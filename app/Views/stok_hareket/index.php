<?php
$title = 'Stok Hareketleri';
$description = 'Stok giriş-çıkış hareketlerini tarih aralığı ve stok bazında filtreleyin, bakiye takibi yapın.';
$badgeText = 'Kayıt: ' . count($rapor['satirlar'] ?? []);
$headerClass = 'stok-hareket-page-header';
$headerLeftClass = 'stok-hareket-page-header-left';
$headerRightClass = 'stok-hareket-page-header-right';
$badgeClass = 'istatistik-rozet';
$actionHtml = '';
require BASE_PATH . '/app/Views/partials/page-header.php';

$filters = $rapor['filters'] ?? [];
$ozet    = $rapor['ozet'] ?? [];
$satirlar = $rapor['satirlar'] ?? [];

$seciliStokId = (int) ($filters['stok_id'] ?? 0);
$tekStok = $seciliStokId > 0;

function shTipRenk(string $tip): string {
    return match($tip) {
        'alis'     => '#16a34a',
        'satis'    => '#dc2626',
        'devir'    => '#2563eb',
        'duzeltme' => '#d97706',
        default    => '#64748b',
    };
}

function shTipEtiket(string $tip): string {
    return match($tip) {
        'alis'     => 'Alış',
        'satis'    => 'Satış',
        'devir'    => 'Devir',
        'duzeltme' => 'Düzeltme',
        default    => $tip,
    };
}

function shSayi(float $val): string {
    return $val != 0 ? number_format($val, 4, ',', '.') : '';
}
?>
<style>
.sh-filtre-wrap {
    background:#fff;border:1px solid #e6edf5;border-radius:16px;
    padding:20px;margin-bottom:18px;
}
.sh-filtre-grid {
    display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;
}
.sh-filtre-grid label {
    display:block;font-size:11px;font-weight:700;color:#475569;
    text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;margin-top:0;
}
.sh-ozet-grid {
    display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:14px;margin-bottom:18px;
}
.sh-ozet-kart {
    background:#fff;border:1px solid #e6edf5;border-radius:14px;
    padding:16px 18px;
}
.sh-ozet-label {
    font-size:11px;font-weight:700;color:#64748b;
    text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;
}
.sh-ozet-deger {
    font-size:22px;font-weight:800;color:#0f172a;
}
.sh-ozet-deger.giris  { color:#16a34a; }
.sh-ozet-deger.cikis  { color:#dc2626; }
.sh-ozet-deger.net.pozitif { color:#2563eb; }
.sh-ozet-deger.net.negatif { color:#dc2626; }
.sh-tablo-wrap {
    background:#fff;border:1px solid #e6edf5;border-radius:16px;overflow:hidden;
}
.sh-tablo {
    width:100%;border-collapse:collapse;font-size:13px;
}
.sh-tablo thead th {
    background:#f8fafc;color:#475569;font-size:11px;font-weight:800;
    text-transform:uppercase;letter-spacing:.04em;
    padding:11px 12px;text-align:left;border-bottom:1px solid #e6edf5;
    white-space:nowrap;
}
.sh-tablo tbody tr { border-bottom:1px solid #f1f5f9; }
.sh-tablo tbody tr:last-child { border-bottom:0; }
.sh-tablo tbody tr:hover { background:#f8fbff; }
.sh-tablo td { padding:10px 12px;vertical-align:middle;color:#1e293b; }
.sh-tip-badge {
    display:inline-flex;align-items:center;padding:3px 10px;
    border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;
}
.sh-sayi-giris { font-weight:700;color:#16a34a; }
.sh-sayi-cikis { font-weight:700;color:#dc2626; }
.sh-sayi-bakiye { font-weight:800;color:#0f172a; }
.sh-bos { text-align:center;padding:40px;color:#94a3b8;font-size:13px; }
@media(max-width:640px) {
    .sh-tablo-wrap { overflow-x:auto; }
    .sh-tablo { min-width:700px; }
}
</style>

<!-- FİLTRE -->
<div class="sh-filtre-wrap">
    <form method="get" action="<?= e(url('stok-hareketleri')) ?>">
        <div class="sh-filtre-grid">
            <div>
                <label>Başlangıç Tarihi</label>
                <input type="date" name="tarih_baslangic" value="<?= e($filters['tarih_baslangic'] ?? '') ?>">
            </div>
            <div>
                <label>Bitiş Tarihi</label>
                <input type="date" name="tarih_bitis" value="<?= e($filters['tarih_bitis'] ?? '') ?>">
            </div>
            <div style="position:relative;">
    <label>Stok</label>
                <?php
$seciliStokAd = '';
foreach ($stoklar as $s) {
    if ((int)$s['id'] === $seciliStokId) {
        $seciliStokAd = $s['stok_kodu'] ? $s['stok_kodu'] . ' — ' . $s['urun_adi'] : $s['urun_adi'];
        break;
    }
}
?>
<input type="text" id="stokAraInput" placeholder="Stok ara..." autocomplete="off"
    value="<?= e($seciliStokAd) ?>"
    style="margin-bottom:4px;">
<input type="hidden" name="stok_id" id="stokIdHidden" value="<?= $seciliStokId ?: '' ?>">
<div id="stokOneri" style="position:absolute;z-index:999;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(15,23,42,.12);max-height:220px;overflow-y:auto;display:none;min-width:260px;"></div>
<script>
(function () {
    var data = <?= json_encode(array_values(array_map(fn($s) => [
        'id'   => (int) $s['id'],
        'text' => $s['stok_kodu'] ? $s['stok_kodu'] . ' — ' . $s['urun_adi'] : $s['urun_adi'],
    ], $stoklar)), JSON_UNESCAPED_UNICODE) ?>;

    var inp  = document.getElementById('stokAraInput');
    var hid  = document.getElementById('stokIdHidden');
    var oneri = document.getElementById('stokOneri');

    function goster(liste) {
        if (!liste.length) { oneri.style.display = 'none'; return; }
        oneri.innerHTML = '';
        liste.forEach(function (item) {
            var div = document.createElement('div');
            div.textContent = item.text;
            div.style.cssText = 'padding:9px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid #f1f5f9;';
            div.addEventListener('mousedown', function (e) {
                e.preventDefault();
                inp.value = item.text;
                hid.value = item.id;
                oneri.style.display = 'none';
            });
            div.addEventListener('mouseover', function () { this.style.background = '#f0f9ff'; });
            div.addEventListener('mouseout',  function () { this.style.background = ''; });
            oneri.appendChild(div);
        });
        oneri.style.display = 'block';
    }

    inp.addEventListener('input', function () {
        hid.value = '';
        var q = this.value.trim().toLowerCase();
        if (!q) { goster(data.slice(0, 20)); return; }
        goster(data.filter(function (d) { return d.text.toLowerCase().includes(q); }).slice(0, 20));
    });

    inp.addEventListener('focus', function () {
        var q = this.value.trim().toLowerCase();
        goster(q ? data.filter(function (d) { return d.text.toLowerCase().includes(q); }).slice(0, 20) : data.slice(0, 20));
    });

    inp.addEventListener('blur', function () {
        setTimeout(function () { oneri.style.display = 'none'; }, 150);
        // Yazılan şey seçili bir stokla eşleşmiyorsa temizle
        if (!hid.value) inp.value = '';
    });
})();
</script>
            </div>
            <div>
                <label>İşlem Tipi</label>
                <select name="islem_tipi">
                    <option value="">Tümü</option>
                    <option value="alis"     <?= ($filters['islem_tipi'] ?? '') === 'alis'     ? 'selected' : '' ?>>Alış</option>
                    <option value="satis"    <?= ($filters['islem_tipi'] ?? '') === 'satis'    ? 'selected' : '' ?>>Satış</option>
                    <option value="devir"    <?= ($filters['islem_tipi'] ?? '') === 'devir'    ? 'selected' : '' ?>>Devir</option>
                    <option value="duzeltme" <?= ($filters['islem_tipi'] ?? '') === 'duzeltme' ? 'selected' : '' ?>>Düzeltme</option>
                </select>
            </div>
            <div style="display:flex;align-items:flex-end;gap:8px;">
                <button type="submit" class="btn btn-ana" style="flex:1;">Filtrele</button>
                <a href="<?= e(url('stok-hareketleri')) ?>" class="btn btn-gri">Sıfırla</a>
            </div>
        </div>
    </form>
</div>

<!-- ÖZET KARTLAR -->
<div class="sh-ozet-grid">
    <div class="sh-ozet-kart">
        <div class="sh-ozet-label">Toplam Giriş</div>
        <div class="sh-ozet-deger giris"><?= number_format((float) ($ozet['toplam_giris'] ?? 0), 4, ',', '.') ?></div>
    </div>
    <div class="sh-ozet-kart">
        <div class="sh-ozet-label">Toplam Çıkış</div>
        <div class="sh-ozet-deger cikis"><?= number_format((float) ($ozet['toplam_cikis'] ?? 0), 4, ',', '.') ?></div>
    </div>
    <div class="sh-ozet-kart">
        <?php $net = (float) ($ozet['net_degisim'] ?? 0); ?>
        <div class="sh-ozet-label">Net Değişim</div>
        <div class="sh-ozet-deger net <?= $net >= 0 ? 'pozitif' : 'negatif' ?>">
            <?= ($net >= 0 ? '+' : '') . number_format($net, 4, ',', '.') ?>
        </div>
    </div>
    <div class="sh-ozet-kart">
        <div class="sh-ozet-label">Kayıt Sayısı</div>
        <div class="sh-ozet-deger"><?= (int) ($ozet['kayit_sayisi'] ?? 0) ?></div>
    </div>
</div>

<!-- TABLO -->
<div class="sh-tablo-wrap">
    <table class="sh-tablo">
        <thead>
            <tr>
                <th>Tarih</th>
                <?php if (!$tekStok): ?><th>Stok Kodu</th><th>Ürün Adı</th><?php endif; ?>
                <th>Birim</th>
                <th>İşlem</th>
                <th>Cari / Açıklama</th>
                <th>Fatura No</th>
                <th style="text-align:right;">Giriş</th>
                <th style="text-align:right;">Çıkış</th>
                <?php if ($tekStok): ?><th style="text-align:right;">Bakiye</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($satirlar)): ?>
                <tr><td colspan="<?= $tekStok ? 9 : 9 ?>" class="sh-bos">Kayıt bulunamadı.</td></tr>
            <?php else: ?>
                <?php foreach ($satirlar as $row):
                    $tip    = strtolower((string) ($row['islem_tipi'] ?? ''));
                    $renk   = shTipRenk($tip);
                    $etiket = shTipEtiket($tip);
                    $tarih  = !empty($row['tarih']) ? date('d.m.Y', strtotime(substr((string) $row['tarih'], 0, 10))) : '-';
                    $cari   = $row['cari_adi'] ?: ($row['aciklama'] ?: '-');
                ?>
                <tr>
                    <td><?= e($tarih) ?></td>
                    <?php if (!$tekStok): ?>
                        <td style="font-family:monospace;font-size:12px;color:#64748b;"><?= e($row['stok_kodu'] ?? '') ?></td>
                        <td style="font-weight:600;"><?= e($row['urun_adi'] ?? '') ?></td>
                    <?php endif; ?>
                    <td><?= e($row['birim'] ?? '') ?></td>
                    <td>
                        <span class="sh-tip-badge" style="background:<?= $renk ?>18;color:<?= $renk ?>;">
                            <?= e($etiket) ?>
                        </span>
                    </td>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($cari) ?>"><?= e($cari) ?></td>
                    <td style="font-family:monospace;font-size:12px;color:#64748b;"><?= e($row['fatura_no'] ?? '-') ?></td>
                    <td style="text-align:right;">
                        <?php if ((float)($row['giris'] ?? 0) > 0): ?>
                            <span class="sh-sayi-giris"><?= shSayi((float)$row['giris']) ?></span>
                        <?php else: ?>&nbsp;<?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <?php if ((float)($row['cikis'] ?? 0) > 0): ?>
                            <span class="sh-sayi-cikis"><?= shSayi((float)$row['cikis']) ?></span>
                        <?php else: ?>&nbsp;<?php endif; ?>
                    </td>
                    <?php if ($tekStok): ?>
                        <td style="text-align:right;">
                            <span class="sh-sayi-bakiye"><?= shSayi((float)($row['bakiye'] ?? 0)) ?></span>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>