<?php require BASE_PATH . '/app/Views/partials/styles/cari-page.php'; ?>
<?php
$title = 'Cari Hareketler';
$description = 'Tahsilat, tediye ve düzeltme kayıtlarını filtreleyin, hareket geçmişini tek ekranda görün.';
$badgeText = 'Kayıt: ' . count($rapor['satirlar'] ?? []);
$headerClass = 'cari-page-header';
$headerLeftClass = 'cari-page-header-left';
$headerRightClass = 'cari-page-header-right';
$badgeClass = 'istatistik-rozet cari-stat-badge';
$actionHtml = '';

require BASE_PATH . '/app/Views/partials/page-header.php';

$filters = $rapor['filters'] ?? [];
$ozet = $rapor['ozet'] ?? [];

$seciliCariId = (int) ($filters['cari_id'] ?? 0);
$seciliCari = null;

foreach (($cariler ?? []) as $cariItem) {
    if ((int) ($cariItem['id'] ?? 0) === $seciliCariId) {
        $seciliCari = $cariItem;
        break;
    }
}

$hareketlerJson = array_values(array_map(static function ($row) {
    return [
        'id' => (int) ($row['id'] ?? 0),
        'cari_id' => (int) ($row['cari_id'] ?? 0),
        'islem_tipi' => (string) ($row['islem_tipi'] ?? ''),
        'tarih' => !empty($row['tarih']) ? substr((string) $row['tarih'], 0, 10) : '',
        'tutar' => (string) ($row['tutar'] ?? ''),
        'aciklama' => (string) ($row['aciklama'] ?? ''),
    ];
}, $rapor['satirlar'] ?? []));
?>
<?php
$hareketlerJson = array_values(array_map(static function ($row) {
    return [
        'id' => (int) ($row['id'] ?? 0),
        'cari_id' => (int) ($row['cari_id'] ?? 0),
        'islem_tipi' => (string) ($row['islem_tipi'] ?? ''),
        'tarih' => !empty($row['tarih']) ? substr((string) $row['tarih'], 0, 10) : '',
        'tutar' => (string) ($row['tutar'] ?? ''),
        'aciklama' => (string) ($row['aciklama'] ?? ''),
    ];
}, $rapor['satirlar'] ?? []));
?>

<?php
ob_start();
?>
<form id="editCariHareketForm" method="post" action="<?= e(url('cari-hareketler/guncelle')) ?>" class="app-form-stack">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="hareket_id" value="">

    <label>Cari</label>
<input type="hidden" name="cari_id" value="">
<input type="text" value="" disabled>

    <label>İşlem Tipi</label>
    <select name="islem_tipi" required>
        <option value="tahsilat">Tahsilat</option>
        <option value="tediye">Tediye</option>
        <option value="duzeltme">Düzeltme</option>
    </select>

    <label>Tarih</label>
    <input type="date" name="tarih" required>

    <label>Tutar</label>
    <input type="number" name="tutar" step="0.01" min="0.01" required>

    <label>Açıklama</label>
    <input type="text" name="aciklama" required>

    <div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-turuncu">Güncelle</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId = 'editCariHareketModal';
$modalTitle = 'Cari Hareket Düzenle';
$modalDescription = 'Cari hareket kaydını güncelleyin.';
$modalSize = 'md';

require BASE_PATH . '/app/Views/partials/modal.php';
?>

<div class="kutu" style="padding:26px; margin-bottom:18px;">
    <form method="get" action="<?= e(url('cari-hareketler')) ?>">
        <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
            <div>
    <label>Cari</label>
    <div class="cari-autocomplete-wrap">
        <input
            type="text"
            id="cariHareketCariInput"
            value="<?= e($seciliCari['ad_soyad'] ?? '') ?>"
            placeholder="Cari adı yazarak arayın"
            autocomplete="off"
        >
        <input type="hidden" name="cari_id" id="cariHareketCariId" value="<?= $seciliCariId ?>">
        <div id="cariHareketCariResults" class="cari-autocomplete-results"></div>
    </div>
</div>

            <div>
                <label>İşlem Tipi</label>
                <select name="islem_tipi">
                    <option value="">Tümü</option>
                    <option value="tahsilat" <?= ($filters['islem_tipi'] ?? '') === 'tahsilat' ? 'selected' : '' ?>>Tahsilat</option>
                    <option value="tediye" <?= ($filters['islem_tipi'] ?? '') === 'tediye' ? 'selected' : '' ?>>Tediye</option>
                    <option value="duzeltme" <?= ($filters['islem_tipi'] ?? '') === 'duzeltme' ? 'selected' : '' ?>>Düzeltme</option>
                </select>
            </div>

            <div>
                <label>Tarih Başlangıç</label>
                <input type="date" name="tarih_baslangic" value="<?= e($filters['tarih_baslangic'] ?? '') ?>">
            </div>

            <div>
                <label>Tarih Bitiş</label>
                <input type="date" name="tarih_bitis" value="<?= e($filters['tarih_bitis'] ?? '') ?>">
            </div>
        </div>

        <div style="display:flex; gap:8px; margin-top:14px; flex-wrap:wrap;">
            <button type="submit" class="btn btn-yesil">Filtrele</button>
            <a href="<?= e(url('cari-hareketler')) ?>" class="btn btn-gri">Temizle</a>
        </div>
    </form>
</div>

<div class="kutu" style="padding: 26px; margin-bottom: 18px;">
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
        <div class="bilgi-kutusu">
            <strong>Kayıt Sayısı</strong><br>
            <?= (int) ($ozet['kayit_sayisi'] ?? 0) ?>
        </div>
        <div class="bilgi-kutusu">
            <strong>Toplam Tahsilat</strong><br>
            <?= number_format((float) ($ozet['toplam_tahsilat'] ?? 0), 2, ',', '.') ?> ₺
        </div>
        <div class="bilgi-kutusu">
            <strong>Toplam Tediye</strong><br>
            <?= number_format((float) ($ozet['toplam_tediye'] ?? 0), 2, ',', '.') ?> ₺
        </div>
        <div class="bilgi-kutusu">
            <strong>Toplam Düzeltme</strong><br>
            <?= number_format((float) ($ozet['toplam_duzeltme'] ?? 0), 2, ',', '.') ?> ₺
        </div>
    </div>
</div>


<div class="kutu" style="padding:26px;">
    <h3 style="margin-bottom:10px;">Hareket Listesi</h3>

    <div class="table-wrap">
        <table class="cari-table">
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>Cari</th>
                    <th>İşlem Tipi</th>
                    <th>Açıklama</th>
                    <th>Tutar</th>
<th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rapor['satirlar'] ?? []) as $row): ?>
                    <tr>
                        <td><?= e($row['tarih'] ?? '-') ?></td>
                        <td><?= e($row['cari_adi'] ?? '-') ?></td>
                        <td>
                            <?php
                            $tip = (string) ($row['islem_tipi'] ?? '');
                            echo e(
                                $tip === 'tahsilat' ? 'Tahsilat'
                                : ($tip === 'tediye' ? 'Tediye' : 'Düzeltme')
                            );
                            ?>
                        </td>
                        <td><?= e($row['aciklama'] ?? '-') ?></td>
                        <td><strong><?= number_format((float) ($row['tutar'] ?? 0), 2, ',', '.') ?> ₺</strong></td>
                        <td>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button type="button" class="btn btn-gri" onclick="cariHareketDuzenleAc(<?= (int) ($row['id'] ?? 0) ?>)">Düzenle</button>

        <form method="post" action="<?= e(url('cari-hareketler/sil')) ?>" onsubmit="return confirm('Bu cari hareket kaydı silinsin mi?');" style="display:inline;">
            <input type="hidden" name="hareket_id" value="<?= (int) ($row['id'] ?? 0) ?>">
            <button type="submit" class="btn btn-kirmizi">Sil</button>
        </form>
    </div>
</td>
                    </tr>
                <?php endforeach; ?>
                

                <?php if (empty($rapor['satirlar'])): ?>
                    <tr>
                        <td colspan="6">Kayıt bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <script>
const cariHareketData = <?= json_encode($hareketlerJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

function cariHareketDuzenleAc(id) {
    const hareket = cariHareketData.find(function (item) {
        return Number(item.id) === Number(id);
    });

    if (!hareket) {
        return;
    }

    const form = document.getElementById('editCariHareketForm');
    if (!form) {
        return;
    }

    form.querySelector('[name="hareket_id"]').value = hareket.id ?? '';
    form.querySelector('[name="cari_id"]').value = hareket.cari_id ?? '';
    form.querySelector('[name="islem_tipi"]').value = hareket.islem_tipi ?? '';
    form.querySelector('[name="tarih"]').value = hareket.tarih ?? '';
    form.querySelector('[name="tutar"]').value = hareket.tutar ?? '';
    form.querySelector('[name="aciklama"]').value = hareket.aciklama ?? '';
	
	const cariTextInput = form.querySelector('input[type="text"][disabled]');
if (cariTextInput) {
    const cari = (window.cariHareketCariler || []).find(function (item) {
        return Number(item.id) === Number(hareket.cari_id);
    });
    cariTextInput.value = cari ? (cari.ad_soyad ?? '') : '';
}

    if (window.appModal) {
        window.appModal.open('editCariHareketModal');
    }
}
</script>
<script>
    window.cariHareketCariler = <?= json_encode(array_values(array_map(static function ($cari) {
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
            inputId: 'cariHareketCariInput',
            hiddenId: 'cariHareketCariId',
            resultsId: 'cariHareketCariResults',
            items: window.cariHareketCariler || [],
            emptyValue: '0'
        });
    });
</script>
    </div>
</div>