<?php
$title = 'EDM Faturalar';
$description = 'EDM portalından gelen ve giden e-faturaları görüntüle.';

$yeniGelen = array_filter($gelen, fn($f) => !in_array($f['uuid'], $gorulduler ?? [], true));
$yeniGiden = array_filter($giden, fn($f) => !in_array($f['uuid'], $gorulduler ?? [], true));

$badgeText        = 'Gelen: ' . count($gelen) . ' / Giden: ' . count($giden);
$headerClass      = 'edm-page-header';
$headerLeftClass  = 'edm-page-header-left';
$headerRightClass = 'edm-page-header-right';
$badgeClass       = 'istatistik-rozet edm-stat-badge';
$actionHtml       = '';

require BASE_PATH . '/app/Views/partials/page-header.php';

function edmDurumBilgisi(string $durum): array {
    return match(strtoupper(trim($durum))) {
        'SUCCEED'                   => ['label' => 'Teslim Edildi',        'sinif' => 'badge-yesil'],
        'WAIT_APPLICATION_RESPONSE' => ['label' => 'Yanıt Bekleniyor',     'sinif' => 'badge-sari'],
        'WAIT_SENDER_RESPONSE'      => ['label' => 'Gönderici Bekleniyor', 'sinif' => 'badge-sari'],
        'REJECTED'                  => ['label' => 'Reddedildi',           'sinif' => 'badge-kirmizi'],
        'ERROR'                     => ['label' => 'Hata',                 'sinif' => 'badge-kirmizi'],
        'LOADING'                   => ['label' => 'İşleniyor',            'sinif' => 'badge-mavi'],
        default                     => ['label' => $durum ?: '-',          'sinif' => 'badge-gri'],
    };
}

function edmTarihFormatla(string $tarih): string {
    if ($tarih === '') return '-';
    $ts = strtotime($tarih);
    return $ts ? date('d.m.Y', $ts) : $tarih;
}

function edmTutarFormatla(array $tutar): string {
    $deger = trim((string) ($tutar['value'] ?? ''));
    if ($deger === '' || $deger === '0') return '-';
    $sayi   = (float) $deger;
    $birim  = strtoupper(trim((string) ($tutar['currency'] ?? '')));
    $sembol = match($birim) {
        'TRY', 'TL' => '₺',
        'USD'       => '$',
        'EUR'       => '€',
        default     => $birim,
    };
    return number_format($sayi, 2, ',', '.') . ' ' . $sembol;
}
?>
<style>
    .badge-sari {
        background:#fffbeb;color:#92400e;border:1px solid #fde68a;
        display:inline-flex;align-items:center;justify-content:center;
        padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;
    }
    .edm-yeni-nokta {
        width:8px;height:8px;background:#2563eb;border-radius:50%;
        display:inline-block;flex-shrink:0;margin-right:6px;
    }
    .edm-yeni-banner {
        background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;
        border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;
        margin-bottom:16px;display:flex;align-items:center;gap:8px;
    }
    .edm-sekme-wrap {
        display:flex;gap:8px;margin-bottom:16px;
        border-bottom:2px solid #e6edf5;flex-wrap:wrap;
    }
    .edm-sekme {
        padding:9px 16px;font-size:13px;font-weight:600;color:#64748b;
        cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;
        user-select:none;transition:color .15s,border-color .15s;
        display:flex;align-items:center;gap:6px;
    }
    .edm-sekme.aktif { color:#2563eb;border-bottom-color:#2563eb; }
    .edm-sekme-rozet { border-radius:20px;padding:1px 8px;font-size:11px;font-weight:700; }
    .edm-panel { display:none; }
    .edm-panel.aktif { display:block; }

    .edm-filtre-form {
        display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:18px;
    }
    .edm-filtre-form .edm-filtre-alan label {
        display:block;font-size:11px;font-weight:700;color:#475569;
        text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;
    }
    .edm-filtre-form .edm-filtre-alan input[type="date"] {
        border:1px solid #e2e8f0;border-radius:8px;padding:7px 12px;
        font-size:13px;color:#1e293b;background:#fff;cursor:pointer;
        display:block;box-sizing:border-box;
    }

    .edm-tablo-wrap {
        border:1px solid #e6edf5;border-radius:16px;background:#fff;overflow:hidden;
    }
    .edm-tablo {
        width:100%;border-collapse:collapse;font-size:13.5px;table-layout:fixed;
    }
    .edm-tablo col.c-firma { width:28%; }
    .edm-tablo col.c-no    { width:13%; }
    .edm-tablo col.c-tarih { width:10%; }
    .edm-tablo col.c-tutar { width:12%; }
    .edm-tablo col.c-durum { width:15%; }
    .edm-tablo col.c-btn   { width:22%; }
    .edm-tablo thead th {
        background:#f8fafc;color:#475569;font-size:11px;font-weight:800;
        text-transform:uppercase;letter-spacing:.04em;
        padding:11px 14px;text-align:left;border-bottom:1px solid #e6edf5;
    }
    .edm-tablo tbody tr {
        border-bottom:1px solid #f1f5f9;transition:background .15s ease;cursor:pointer;
    }
    .edm-tablo tbody tr:last-child { border-bottom:0; }
    .edm-tablo tbody tr:hover { background:#f8fbff; }
    .edm-tablo td {
        padding:11px 14px;vertical-align:middle;color:#1e293b;
        overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
    }
    tr.edm-yeni td { font-weight:700; }
    .edm-firma-adi { display:flex;align-items:center;font-weight:600; }
    .edm-fatura-no { font-family:monospace;font-size:12px;color:#94a3b8; }
    .edm-tutar-deger { font-weight:700;color:#2563eb;white-space:nowrap; }
    .edm-tarih-deger { color:#64748b;font-size:12.5px;white-space:nowrap; }
    .edm-bos { text-align:center;padding:32px 0;color:#94a3b8;font-size:13px; }

    .edm-kart-liste { display:none; }
    .edm-kart { padding:14px 16px;border-bottom:1px solid #f1f5f9;cursor:pointer; }
    .edm-kart:last-child { border-bottom:0; }
    .edm-kart-ust {
        display:flex;align-items:flex-start;justify-content:space-between;
        gap:8px;margin-bottom:8px;
    }
    .edm-kart-firma {
        font-size:14px;font-weight:600;color:#0f172a;
        word-break:break-word;flex:1;
        display:flex;align-items:center;gap:4px;
    }
    .edm-kart.edm-yeni .edm-kart-firma { font-weight:800; }
    .edm-kart-meta {
        display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:10px;
    }
    .edm-kart-butonlar {
        display:flex;gap:8px;
    }
    .edm-kart-btn {
        display:block;flex:1;text-align:center;
        box-sizing:border-box;font-size:13px !important;padding:9px 12px !important;
    }
    .edm-tablo-kap { width:100%; }

    @media (max-width: 640px) {
        .edm-tablo-kap { display:none; }
        .edm-kart-liste {
            display:block;border:1px solid #e6edf5;
            border-radius:16px;background:#fff;overflow:hidden;
        }
        .edm-filtre-form { flex-direction:column; }
        .edm-filtre-form .edm-filtre-alan { width:100%; }
        .edm-filtre-form .edm-filtre-alan input[type="date"] { width:100%; }
        .edm-filtre-form .btn { width:100%;box-sizing:border-box;text-align:center; }
        .edm-sekme { padding:8px 10px;font-size:12px; }
        .edm-sekme-rozet { padding:1px 6px;font-size:10px; }
    }
    @keyframes edmSpin { to { transform: rotate(360deg); } }
    .edm-kontor-bar {
    display:flex;align-items:center;gap:10px;
    background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;
    padding:10px 16px;margin-bottom:16px;font-size:13px;color:#15803d;font-weight:600;
}
.edm-kontor-bar.yukleniyor { background:#f8fafc;border-color:#e2e8f0;color:#94a3b8; }
.edm-kontor-bar.hata { background:#fff5f5;border-color:#fecaca;color:#b91c1c; }
.edm-kontor-deger { font-size:18px;font-weight:800; }
</style>

<?php
$sectionClass       = 'edm-list-panel';
$sectionTitle       = 'Fatura Listesi';
$sectionDescription = 'Son 7 günün gelen ve giden e-faturalarını aşağıdan takip edebilirsin.';
require BASE_PATH . '/app/Views/partials/section-card.php';
?>
<div class="edm-kontor-bar yukleniyor" id="edmKontorBar">
    Kontör bilgisi yükleniyor…
</div>
    <form method="GET" action="<?= e(url('edm-faturalar')) ?>" class="edm-filtre-form">
        <div class="edm-filtre-alan">
            <label>Başlangıç</label>
            <input type="date" name="baslangic" value="<?= e($baslangic) ?>">
        </div>
        <div class="edm-filtre-alan">
            <label>Bitiş</label>
            <input type="date" name="bitis" value="<?= e($bitis) ?>">
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-ana" style="padding:8px 20px;font-size:13px;">Filtrele</button>
            <a href="<?= e(url('edm-faturalar')) ?>" class="btn btn-gri" style="padding:8px 16px;font-size:13px;">Sıfırla</a>
        </div>
    </form>

    <?php if (!empty($hata)): ?>
        <div class="bilgi-kutusu" style="background:#fff5f5;border-color:#fecaca;color:#b91c1c;margin-bottom:16px;">
            <?= e($hata) ?>
        </div>
    <?php endif; ?>

    <?php $toplamYeni = count($yeniGelen) + count($yeniGiden); ?>
    <?php if ($toplamYeni > 0): ?>
        <div class="edm-yeni-banner" id="edmYeniBanner">
            <span class="edm-yeni-nokta" style="margin-right:0;"></span>
            <span id="edmYeniBannerMetin"><?= $toplamYeni ?> yeni fatura var &mdash; okunmamış olanlar kalın gösterilmektedir.</span>
        </div>
    <?php endif; ?>

    <div class="edm-sekme-wrap">
        <div class="edm-sekme aktif" onclick="edmSekmeAc('gelen', this)">
            Gelen Faturalar
            <span class="edm-sekme-rozet" style="background:#dbeafe;color:#1d4ed8;"><?= count($gelen) ?></span>
            <?php if (count($yeniGelen) > 0): ?><span class="edm-sekme-rozet edm-yeni-rozet-gelen" style="background:#fef3c7;color:#92400e;"><?= count($yeniGelen) ?> yeni</span><?php endif; ?>
        </div>
        <div class="edm-sekme" onclick="edmSekmeAc('giden', this)">
            Giden Faturalar
            <span class="edm-sekme-rozet" style="background:#dcfce7;color:#15803d;"><?= count($giden) ?></span>
            <?php if (count($yeniGiden) > 0): ?><span class="edm-sekme-rozet edm-yeni-rozet-giden" style="background:#fef3c7;color:#92400e;"><?= count($yeniGiden) ?> yeni</span><?php endif; ?>
        </div>
    </div>

    <?php
    foreach ([
        'gelen' => [
            'liste'      => $gelen,
            'yon'        => 'IN',
            'firma_key'  => ['supplier', 'sender'],
            'baslik'     => 'Gönderici Firma',
            'aktar_url'  => 'alis-faturalari',
            'aktar_etiket' => '+ Alış Faturası',
            'aktar_sinif'  => 'btn-yesil',
        ],
        'giden' => [
            'liste'      => $giden,
            'yon'        => 'OUT',
            'firma_key'  => ['customer', 'receiver'],
            'baslik'     => 'Alıcı Firma',
            'aktar_url'  => 'satis-faturalari',
            'aktar_etiket' => '+ Satış Faturası',
            'aktar_sinif'  => 'btn-ana',
        ],
    ] as $panelId => $cfg):
        $aktif = $panelId === 'gelen' ? 'aktif' : '';
    ?>
    <div id="edm-panel-<?= $panelId ?>" class="edm-panel <?= $aktif ?>">

        <?php /* DESKTOP TABLO */ ?>
        <div class="edm-tablo-kap">
            <div class="edm-tablo-wrap">
                <table class="edm-tablo">
                    <colgroup>
                        <col class="c-firma"><col class="c-no"><col class="c-tarih">
                        <col class="c-tutar"><col class="c-durum"><col class="c-btn">
                    </colgroup>
                    <thead>
                        <tr>
                            <th><?= $cfg['baslik'] ?></th>
                            <th>Fatura No</th><th>Tarih</th><th>Tutar</th><th>Durum</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cfg['liste'])): ?>
                            <tr><td colspan="6" class="edm-bos">Son 7 günde fatura bulunamadı.</td></tr>
                        <?php else: ?>
                            <?php foreach ($cfg['liste'] as $f):
                                $yeni     = !in_array($f['uuid'], $gorulduler ?? [], true);
                                $durum    = edmDurumBilgisi($f['status_description'] ?: $f['status']);
                               $faturaNo = $f['fatura_no'] ?: ($f['trxid'] ?: strtoupper(substr($f['uuid'], 0, 8)) . '…');
                               $kdvliTutar  = $f['payable_amount']['value'] ?? '';
$kdvsizTutar = $f['tax_exclusive_amount']['value'] ?? '';
                                $firma    = $f[$cfg['firma_key'][0]] ?: $f[$cfg['firma_key'][1]] ?: '-';
                                $gorUrl   = url('edm-faturalar/goruntule?uuid=' . urlencode($f['uuid']) . '&yon=' . $cfg['yon']);
                                $aktarUrl = url($cfg['aktar_url']
    . '?edm_cari='        . urlencode($firma)
    . '&edm_fatura_no='   . urlencode($f['fatura_no'] ?: $f['trxid'])
    . '&edm_tarih='       . urlencode(substr($f['issue_date'], 0, 10))
    . '&edm_kdvli='       . urlencode($kdvliTutar)
    . '&edm_kdvsiz='      . urlencode($kdvsizTutar)
);
                            ?>
                            <tr class="<?= $yeni ? 'edm-yeni' : '' ?>" data-uuid="<?= e($f['uuid']) ?>" data-panel="<?= $panelId ?>" onclick="edmGorulduIsaretle(this)">
                                <td><div class="edm-firma-adi"><?php if ($yeni): ?><span class="edm-yeni-nokta"></span><?php endif; ?><?= e($firma) ?></div></td>
                                <td><span class="edm-fatura-no"><?= e($faturaNo) ?></span></td>
                                <td><span class="edm-tarih-deger"><?= e(edmTarihFormatla($f['issue_date'])) ?></span></td>
                                <td><span class="edm-tutar-deger"><?= e(edmTutarFormatla($f['payable_amount'])) ?></span></td>
                                <td><span class="badge <?= e($durum['sinif']) ?>" style="font-size:11px;"><?= e($durum['label']) ?></span></td>
                                <td style="white-space:nowrap;">
                                    <button type="button" class="btn btn-gri" style="font-size:11px;padding:5px 8px;" onclick="event.stopPropagation();edmFaturaAc('<?= e(addslashes($gorUrl)) ?>')">Görüntüle</button>
                                    <button type="button"
    class="btn <?= e($cfg['aktar_sinif']) ?>"
    style="font-size:11px;padding:5px 8px;"
    onclick="event.stopPropagation();edmAktarFatura(this,'<?= e(addslashes($f['uuid'])) ?>','<?= $cfg['yon'] ?>','<?= e(addslashes($aktarUrl)) ?>')"
><?= $cfg['aktar_etiket'] ?></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php /* MOBİL KARTLAR */ ?>
        <div class="edm-kart-liste">
            <?php if (empty($cfg['liste'])): ?>
                <div class="edm-bos" style="padding:24px;">Son 7 günde fatura bulunamadı.</div>
            <?php else: ?>
                <?php foreach ($cfg['liste'] as $f):
                    $yeni     = !in_array($f['uuid'], $gorulduler ?? [], true);
                    $durum    = edmDurumBilgisi($f['status_description'] ?: $f['status']);
                    $firma    = $f[$cfg['firma_key'][0]] ?: $f[$cfg['firma_key'][1]] ?: '-';
                    $gorUrl   = url('edm-faturalar/goruntule?uuid=' . urlencode($f['uuid']) . '&yon=' . $cfg['yon']);
                    $aktarUrl = url($cfg['aktar_url']
    . '?edm_cari='      . urlencode($firma)
    . '&edm_fatura_no=' . urlencode($f['fatura_no'] ?: $f['trxid'])
    . '&edm_tarih='     . urlencode(substr($f['issue_date'], 0, 10))
    . '&edm_kdvli='     . urlencode($f['payable_amount']['value'] ?? '')
    . '&edm_kdvsiz='    . urlencode($f['tax_exclusive_amount']['value'] ?? '')
);
                ?>
                <div class="edm-kart <?= $yeni ? 'edm-yeni' : '' ?>" data-uuid="<?= e($f['uuid']) ?>" data-panel="<?= $panelId ?>" onclick="edmGorulduIsaretle(this)">
                    <div class="edm-kart-ust">
                        <span class="edm-kart-firma">
                            <?php if ($yeni): ?><span class="edm-yeni-nokta"></span><?php endif; ?>
                            <?= e($firma) ?>
                        </span>
                        <span class="edm-tutar-deger"><?= e(edmTutarFormatla($f['payable_amount'])) ?></span>
                    </div>
                    <div class="edm-kart-meta">
                        <span class="edm-tarih-deger"><?= e(edmTarihFormatla($f['issue_date'])) ?></span>
                        <span class="badge <?= e($durum['sinif']) ?>" style="font-size:11px;"><?= e($durum['label']) ?></span>
                    </div>
                    <div class="edm-kart-butonlar">
                        <button type="button" class="btn btn-gri edm-kart-btn" onclick="event.stopPropagation();edmFaturaAc('<?= e(addslashes($gorUrl)) ?>')">Görüntüle</button>
                        <button type="button"
    class="btn <?= e($cfg['aktar_sinif']) ?> edm-kart-btn"
    onclick="event.stopPropagation();edmAktarFatura(this,'<?= e(addslashes($f['uuid'])) ?>','<?= $cfg['yon'] ?>','<?= e(addslashes($aktarUrl)) ?>')"
><?= $cfg['aktar_etiket'] ?></button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>

</div>
<div id="edmFaturaModal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(15,23,42,.65);padding:16px;box-sizing:border-box;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:1000px;height:100%;max-height:calc(100vh - 32px);display:flex;flex-direction:column;overflow:hidden;box-shadow:0 24px 64px rgba(15,23,42,.24);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #e6edf5;flex-shrink:0;">
            <span style="font-size:15px;font-weight:700;color:#0f172a;">Fatura Önizleme</span>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="btn btn-gri" style="font-size:13px;padding:7px 14px;" onclick="edmFaturaYazdir()">
                    Yazdır
                </button>
                <button type="button" class="btn btn-ana" style="font-size:13px;padding:7px 14px;" onclick="edmFaturaPdfIndir()">
                    PDF İndir
                </button>
                <button type="button" class="btn btn-gri" style="font-size:13px;padding:7px 14px;" onclick="edmFaturaKapat()">
                    Kapat
                </button>
            </div>
        </div>
        <div style="flex:1;position:relative;overflow:hidden;">
            <div id="edmFaturaYukleniyor" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:#f8fafc;font-size:14px;color:#64748b;gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="animation:edmSpin 1s linear infinite;flex-shrink:0;"><circle cx="12" cy="12" r="10" stroke="#cbd5e1" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/></svg>
                Fatura yükleniyor…
            </div>
            <iframe id="edmFaturaFrame" style="width:100%;height:100%;border:none;display:none;" onload="edmFaturaYuklendi()"></iframe>
        </div>
    </div>
</div>

<script>
function edmSekmeAc(panel, el) {
    document.querySelectorAll('.edm-sekme').forEach(s => s.classList.remove('aktif'));
    document.querySelectorAll('.edm-panel').forEach(p => p.classList.remove('aktif'));
    el.classList.add('aktif');
    document.getElementById('edm-panel-' + panel).classList.add('aktif');
}

var _edmAktifUrl = '';

function edmFaturaAc(url) {
    _edmAktifUrl = url;
    var modal  = document.getElementById('edmFaturaModal');
    var frame  = document.getElementById('edmFaturaFrame');
    var loader = document.getElementById('edmFaturaYukleniyor');

    frame.style.display  = 'none';
    loader.style.display = 'flex';
    modal.style.display  = 'flex';
    document.body.style.overflow = 'hidden';

    frame.src = url;
}

function edmFaturaYuklendi() {
    document.getElementById('edmFaturaFrame').style.display = 'block';
    document.getElementById('edmFaturaYukleniyor').style.display = 'none';
}

function edmFaturaYazdir() {
    var frame = document.getElementById('edmFaturaFrame');
    try { frame.contentWindow.print(); } catch(e) { window.open(_edmAktifUrl); }
}

function edmFaturaPdfIndir() {
    // Yeni sekmede aç → Ctrl+P → PDF olarak kaydet
    var w = window.open(_edmAktifUrl, '_blank');
    if (w) {
        w.addEventListener('load', function() {
            setTimeout(function() { w.print(); }, 500);
        });
    }
}

function edmFaturaKapat() {
    var modal = document.getElementById('edmFaturaModal');
    var frame = document.getElementById('edmFaturaFrame');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    setTimeout(function() { frame.src = ''; }, 200);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') edmFaturaKapat();
});

function edmGorulduIsaretle(el) {
    if (!el.classList.contains('edm-yeni')) return;
    const uuid  = el.dataset.uuid;
    const panel = el.dataset.panel;
    if (!uuid) return;

    fetch('<?= e(url('edm-faturalar/goruldu')) ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'uuid=' + encodeURIComponent(uuid),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) return;

        document.querySelectorAll('[data-uuid="' + uuid + '"]').forEach(node => {
            node.classList.remove('edm-yeni');
            node.querySelectorAll('.edm-yeni-nokta').forEach(n => n.remove());
        });

        const kalanPanelde = document.querySelectorAll('#edm-panel-' + panel + ' .edm-yeni').length;
        const rozet = document.querySelector('.edm-yeni-rozet-' + panel);
        if (rozet) {
            if (kalanPanelde > 0) rozet.textContent = kalanPanelde + ' yeni';
            else rozet.remove();
        }

        const toplamKalan = document.querySelectorAll('.edm-yeni').length;
        const banner = document.getElementById('edmYeniBanner');
        if (banner) {
            if (toplamKalan > 0) {
                document.getElementById('edmYeniBannerMetin').textContent =
                    toplamKalan + ' yeni fatura var — okunmamış olanlar kalın gösterilmektedir.';
            } else {
                banner.remove();
            }
        }
        document.dispatchEvent(new Event('edmGorulduGuncellendi'));
    })
    .catch(() => {});
}
function edmAktarFatura(btn, uuid, yon, baseUrl) {
    var orijinalMetin = btn.textContent;
    btn.disabled = true;
    btn.textContent = '...';

    fetch('<?= e(url('edm-faturalar/kalemler')) ?>?uuid=' + encodeURIComponent(uuid) + '&yon=' + encodeURIComponent(yon))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var url = baseUrl;
            if (data.ok && data.kalemler && data.kalemler.length > 0) {
                url += '&edm_kalemler=' + encodeURIComponent(btoa(unescape(encodeURIComponent(JSON.stringify(data.kalemler)))));
            }
            window.location.href = url;
        })
        .catch(function() {
            window.location.href = baseUrl;
        });
}
function edmKontorYukle() {
    var bar = document.getElementById('edmKontorBar');
    if (!bar) return;

    fetch('<?= e(url('edm-faturalar/kontor')) ?>')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) throw new Error(res.message || 'Hata');

            var d = res.data || {};
        
            // EDM farklı key'lerle dönebilir, hepsini dene
            var kalan = d.COUNTER_LEFT ?? d.REMAINING_CREDIT ?? d.REMAINING ?? d.COUNTER ?? d.BALANCE ?? null;

            bar.classList.remove('yukleniyor', 'hata');

            if (kalan !== null && kalan !== undefined) {
                bar.innerHTML = 'Kalan kontör: <span class="edm-kontor-deger">' + (Number.isInteger(Number(kalan)) ? kalan : Number(kalan).toLocaleString('tr-TR')) + '</span>';
            } else {
                // ham veriyi göster, hangi key geldiğini görelim
                bar.innerHTML = 'Kontör verisi: <span style="font-size:11px;font-family:monospace;">' + JSON.stringify(d) + '</span>';
            }
        })
        .catch(function(e) {
            bar.classList.remove('yukleniyor');
            bar.classList.add('hata');
            bar.textContent = 'Kontör bilgisi alınamadı: ' + e.message;
        });
        
}
document.addEventListener('DOMContentLoaded', function() {
    edmKontorYukle();

    document.querySelectorAll('[data-uuid]').forEach(function(el) {
        if (el.classList.contains('edm-yeni')) {
            edmGorulduIsaretle(el);
        }
    });
});
</script>