<?php require BASE_PATH . '/app/Views/partials/styles/cari-page.php'; ?>
<?php
$title = 'Cari Yaşlandırma';
$description = 'Açık faturaları vade tarihine göre görüntüleyin, geciken alacak ve borçları takip edin.';
$badgeText = 'Açık Fatura: ' . count($rapor['satirlar'] ?? []);
$headerClass = 'cari-page-header';
$headerLeftClass = 'cari-page-header-left';
$headerRightClass = 'cari-page-header-right';
$badgeClass = 'istatistik-rozet cari-stat-badge';
$actionHtml = '';

require BASE_PATH . '/app/Views/partials/page-header.php';

$seciliCariId = (int) ($rapor['cari_id'] ?? 0);
$seciliCari = null;

foreach (($cariler ?? []) as $cariItem) {
    if ((int) ($cariItem['id'] ?? 0) === $seciliCariId) {
        $seciliCari = $cariItem;
        break;
    }
}

$ozet = $rapor['ozet'] ?? [];

$bucketClassResolver = static function (string $bucket): string {
    if ($bucket === 'Vadesi Gelmemiş') {
        return 'bucket-vadesi-gelmemis';
    }

    if ($bucket === '0-30 Gün') {
        return 'bucket-0-30';
    }

    if ($bucket === '31-60 Gün') {
        return 'bucket-31-60';
    }

    if ($bucket === '61-90 Gün') {
        return 'bucket-61-90';
    }

    if ($bucket === '90+ Gün') {
        return 'bucket-90-plus';
    }

    return 'bucket-default';
};
?>

<div class="kutu aging-filter-card">
    <form method="get" action="<?= e(url('cari-yaslandirma')) ?>">
        <div class="aging-filter-grid">
            <div>
                <label>Cari</label>
                <div class="cari-autocomplete-wrap">
                    <input
                        type="text"
                        id="cariYaslandirmaCariInput"
                        value="<?= e($seciliCari['ad_soyad'] ?? '') ?>"
                        placeholder="Cari adı yazarak arayın"
                        autocomplete="off"
                    >
                    <input type="hidden" name="cari_id" id="cariYaslandirmaCariId" value="<?= $seciliCariId ?>">
                    <div id="cariYaslandirmaCariResults" class="cari-autocomplete-results"></div>
                </div>
            </div>

            <div>
                <label>Tür</label>
                <select name="tip">
                    <option value="">Tümü</option>
                    <option value="satis" <?= ($rapor['tip'] ?? '') === 'satis' ? 'selected' : '' ?>>Satış</option>
                    <option value="alis" <?= ($rapor['tip'] ?? '') === 'alis' ? 'selected' : '' ?>>Alış</option>
                </select>
            </div>
        </div>

        <div class="aging-filter-actions">
            <button type="submit" class="btn btn-yesil">Filtrele</button>
            <a href="<?= e(url('cari-yaslandirma')) ?>" class="btn btn-gri">Temizle</a>
        </div>
    </form>
</div>

<div class="aging-summary-grid">
    <div class="kutu aging-summary-card aging-summary-total">
        <div class="aging-summary-label">Toplam Açık</div>
        <div class="aging-summary-value">
            <?= number_format((float) ($ozet['toplam_acik'] ?? 0), 2, ',', '.') ?> ₺
        </div>
    </div>

    <div class="kutu aging-summary-card bucket-vadesi-gelmemis">
        <div class="aging-summary-label">Vadesi Gelmemiş</div>
        <div class="aging-summary-value">
            <?= number_format((float) ($ozet['vadesi_gelmemis'] ?? 0), 2, ',', '.') ?> ₺
        </div>
    </div>

    <div class="kutu aging-summary-card bucket-0-30">
        <div class="aging-summary-label">0-30 Gün</div>
        <div class="aging-summary-value">
            <?= number_format((float) ($ozet['gun_0_30'] ?? 0), 2, ',', '.') ?> ₺
        </div>
    </div>

    <div class="kutu aging-summary-card bucket-31-60">
        <div class="aging-summary-label">31-60 Gün</div>
        <div class="aging-summary-value">
            <?= number_format((float) ($ozet['gun_31_60'] ?? 0), 2, ',', '.') ?> ₺
        </div>
    </div>

    <div class="kutu aging-summary-card bucket-61-90">
        <div class="aging-summary-label">61-90 Gün</div>
        <div class="aging-summary-value">
            <?= number_format((float) ($ozet['gun_61_90'] ?? 0), 2, ',', '.') ?> ₺
        </div>
    </div>

    <div class="kutu aging-summary-card bucket-90-plus">
        <div class="aging-summary-label">90+ Gün</div>
        <div class="aging-summary-value">
            <?= number_format((float) ($ozet['gun_90_uzeri'] ?? 0), 2, ',', '.') ?> ₺
        </div>
    </div>
</div>

<div class="kutu aging-table-card">
    <div class="aging-section-head">
        <div>
            <h3>Açık Fatura Listesi</h3>
            <p class="dashboard-section-desc aging-section-desc">
                Bugün: <strong><?= e(date('d.m.Y', strtotime((string) ($rapor['bugun'] ?? date('Y-m-d'))))) ?></strong>
            </p>
        </div>
    </div>

    <div class="table-wrap aging-table-wrap">
        <table class="cari-table aging-table">
            <thead>
                <tr>
                    <th>Cari</th>
                    <th>Tür</th>
                    <th>Fatura No</th>
                    <th>Fatura Tarihi</th>
                    <th>Vade Tarihi</th>
                    <th>Genel Toplam</th>
                    <th>Ödenen</th>
                    <th>Açık Tutar</th>
                    <th>Gecikme</th>
                    <th>Yaşlandırma</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rapor['satirlar'] ?? []) as $row): ?>
                    <?php
                    $bucket = (string) ($row['yaslandirma_kovasi'] ?? '-');
                    $bucketClass = $bucketClassResolver($bucket);
                    $gecikmeGun = (int) ($row['gecikme_gun'] ?? 0);
                    $tarihFormatli     = ($row['tarih'] ?? '') !== '' ? date('d.m.Y', strtotime($row['tarih'])) : '-';
$vadeTarihFormatli = ($row['vade_tarihi'] ?? '') !== '' ? date('d.m.Y', strtotime($row['vade_tarihi'])) : '-';
$butonDurum        = $gecikmeGun > 0 ? 'gecmis' : ($bucket === 'Vadesi Gelmemiş' ? 'yaklasan' : '');
                    $typeLabel = ($row['tip'] ?? '') === 'satis' ? 'Satış' : 'Alış';
                    $typeClass = ($row['tip'] ?? '') === 'satis' ? 'type-satis' : 'type-alis';
                    ?>
                    <tr class="aging-row <?= e($bucketClass) ?>">
                        <td data-label="Cari"><?= e($row['cari_adi'] ?? '-') ?></td>
                        <td data-label="Tür">
                            <span class="aging-type-badge <?= e($typeClass) ?>">
                                <?= e($typeLabel) ?>
                            </span>
                        </td>
                        <td data-label="Fatura No"><?= e($row['fatura_no'] ?? '-') ?></td>
                        <td data-label="Fatura Tarihi"><?= e($row['tarih'] ?? '-') ?></td>
                        <td data-label="Vade Tarihi"><?= e($row['vade_tarihi'] ?? '-') ?></td>
                        <td data-label="Genel Toplam"><?= number_format((float) ($row['genel_toplam'] ?? 0), 2, ',', '.') ?> ₺</td>
                        <td data-label="Ödenen"><?= number_format((float) ($row['odenen_tutar'] ?? 0), 2, ',', '.') ?> ₺</td>
                        <td data-label="Açık Tutar">
                            <strong class="aging-open-amount">
                                <?= number_format((float) ($row['acik_tutar'] ?? 0), 2, ',', '.') ?> ₺
                            </strong>
                        </td>
                        <td data-label="Gecikme">
                            <?php if ($gecikmeGun > 0): ?>
                                <span class="aging-delay-badge delay-danger">
                                    <?= $gecikmeGun ?> gün
                                </span>
                            <?php else: ?>
                                <span class="aging-delay-badge delay-ok">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Yaşlandırma">
                            <span class="aging-bucket-badge <?= e($bucketClass) ?>">
                                <?= e($bucket) ?>
                            </span>
                        </td>
                        <td>
    <?php if (!empty($row['cari_eposta']) && $butonDurum !== ''): ?>
    <button
        type="button"
        class="btn <?= $butonDurum === 'gecmis' ? 'btn-sari' : 'btn-gri' ?>"
        style="font-size:11px;padding:5px 10px;"
        onclick="yaslandirmaHatirlatmaAc(<?= htmlspecialchars(json_encode([
            'cari_id'    => $row['cari_id'],
            'cari_adi'   => $row['cari_adi'],
            'eposta'     => $row['cari_eposta'],
            'fatura_no'  => $row['fatura_no'],
            'tarih'      => $tarihFormatli,
            'vade_tarihi'=> $vadeTarihFormatli,
            'acik_tutar' => number_format((float)($row['acik_tutar'] ?? 0), 2, ',', '.'),
            'tip'        => ($row['tip'] ?? '') === 'satis' ? 'Satış' : 'Alış',
            'durum'      => $butonDurum,
        ], JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
        Hatırlat
    </button>
    <?php elseif ($butonDurum !== ''): ?>
        <span style="font-size:11px;color:#94a3b8;">Mail yok</span>
    <?php endif; ?>
</td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($rapor['satirlar'])): ?>
                    <tr>
                        <td colspan="10">Açık fatura bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$modalContent = ob_start() ?>
<div class="bilgi-kutusu" id="yasHatirlatmaBilgi" style="margin-bottom:12px;"></div>

<div style="margin-bottom:12px;">
    <label style="display:block;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">
        Alıcı
    </label>
    <input type="text" id="yasHatirlatmaEposta" readonly
        style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:7px 12px;font-size:13px;color:#64748b;background:#f8fafc;box-sizing:border-box;">
</div>

<div>
    <label style="display:block;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">
        Mail Metni
    </label>
    <textarea id="yasHatirlatmaMetin" rows="8"
        style="width:100%;font-family:inherit;font-size:13px;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;resize:vertical;line-height:1.6;box-sizing:border-box;color:#1e293b;"></textarea>
</div>

<div class="app-modal-footer">
    <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
    <button type="button" class="btn btn-ana" id="yasHatirlatmaGonderBtn" onclick="yaslandirmaHatirlatmaGonder()">Gönder</button>
</div>
<?php
$modalIcerik = ob_get_clean();
$modalId = 'yasHatirlatmaModal';
$modalTitle = 'Vade Hatırlatması Gönder';
$modalDescription = 'Vadesi geçmiş fatura için hatırlatma maili gönder.';
$modalSize = 'md';
$modalContent = $modalIcerik;
require BASE_PATH . '/app/Views/partials/modal.php';
?>

<script>
var _yasHatirlatmaVeri = null;

function yaslandirmaHatirlatmaAc(veri) {
    _yasHatirlatmaVeri = veri;

    document.getElementById('yasHatirlatmaEposta').value = veri.eposta;

    var varsayilanMetin;
if (veri.durum === 'gecmis') {
    varsayilanMetin =
        'Sayın ' + veri.cari_adi + ',\n\n' +
        veri.fatura_no + ' no\'lu ' + veri.tarih + ' tarihli ' + veri.tip.toLowerCase() + ' faturanızın ' +
        veri.vade_tarihi + ' olan vadesi geçmiş bulunmaktadır.\n\n' +
        'Açık tutarınız: ' + veri.acik_tutar + ' ₺\n\n' +
        'Ödeme yapmamanız durumunda vade farkı uygulanacaktır.\n\n' +
        'İyi çalışmalar dileriz.';
} else {
    varsayilanMetin =
        'Sayın ' + veri.cari_adi + ',\n\n' +
        veri.fatura_no + ' no\'lu ' + veri.tarih + ' tarihli ' + veri.tip.toLowerCase() + ' faturanızın ' +
        'vadesi ' + veri.vade_tarihi + ' tarihinde dolmaktadır.\n\n' +
        'Açık tutarınız: ' + veri.acik_tutar + ' ₺\n\n' +
        'Ödemenizi zamanında yapmanızı rica eder, iyi çalışmalar dileriz.';
}

    document.getElementById('yasHatirlatmaMetin').value = varsayilanMetin;

    var bilgiEl = document.getElementById('yasHatirlatmaBilgi');
    bilgiEl.innerHTML =
        '<strong>Cari:</strong> ' + veri.cari_adi + ' &nbsp;|&nbsp; ' +
        '<strong>Fatura:</strong> ' + veri.fatura_no + ' &nbsp;|&nbsp; ' +
        '<strong>Vade:</strong> ' + veri.vade_tarihi;

    var modal = document.getElementById('yasHatirlatmaModal');
    if (modal) {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }
}

function yaslandirmaHatirlatmaGonder() {
    if (!_yasHatirlatmaVeri) return;

    var btn   = document.getElementById('yasHatirlatmaGonderBtn');
    var metin = document.getElementById('yasHatirlatmaMetin').value.trim();

    if (metin === '') {
        alert('Mail metni boş olamaz.');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Gönderiliyor…';

    fetch('<?= e(url('cari-yaslandirma/hatirlatma-gonder')) ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            cari_id:   _yasHatirlatmaVeri.cari_id,
            fatura_no: _yasHatirlatmaVeri.fatura_no,
            eposta:    _yasHatirlatmaVeri.eposta,
            metin:     metin,
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Gönder';
        if (data.ok) {
            var modal = document.getElementById('yasHatirlatmaModal');
            if (modal) {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            }
            alert('Mail başarıyla gönderildi.');
        } else {
            alert('Hata: ' + (data.message || 'Bilinmeyen hata.'));
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Gönder';
        alert('Bağlantı hatası.');
    });
}
</script>

<script>
    window.cariYaslandirmaCariler = <?= json_encode(array_values(array_map(static function ($cari) {
        return [
            'id' => (int) ($cari['id'] ?? 0),
            'ad_soyad' => (string) ($cari['ad_soyad'] ?? ''),
            'telefon' => (string) ($cari['telefon'] ?? ''),
            'eposta' => (string) ($cari['eposta'] ?? ''),
        ];
    }, $cariler ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php require BASE_PATH . '/app/Views/partials/scripts/cari-autocomplete.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initCariAutocomplete({
            inputId: 'cariYaslandirmaCariInput',
            hiddenId: 'cariYaslandirmaCariId',
            resultsId: 'cariYaslandirmaCariResults',
            items: window.cariYaslandirmaCariler || [],
            emptyValue: '0'
        });
    });
</script>