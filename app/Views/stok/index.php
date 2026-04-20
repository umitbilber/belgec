<?php require BASE_PATH . '/app/Views/partials/scripts/stok-page.php'; ?>
<?php require BASE_PATH . '/app/Views/partials/styles/stok-page.php'; ?>
<?php
$title = 'Stok Yönetimi';
$description = 'Stok kartlarını yönetin, açılış miktarlarını tanımlayın ve hareket geçmişini düzenli şekilde takip edin.';
$badgeText = 'Toplam Stok Kartı: ' . count($stoklar);
$headerClass = 'stok-page-header';
$headerLeftClass = 'stok-page-header-left';
$headerRightClass = 'stok-page-header-right';
$badgeClass = 'istatistik-rozet stok-stat-badge';
$actionHtml = '<button type="button" class="btn btn-ana" data-modal-open="createStokModal">+ Yeni Stok Ekle</button>';

require BASE_PATH . '/app/Views/partials/page-header.php';
?>

    <?php
$sectionClass = 'stok-list-panel';
$sectionTitle = 'Stok Listesi';
$sectionDescription = 'Kayıtlı ürünler arasında arama yapabilir ve stok hareketlerine hızlıca erişebilirsin.';

require BASE_PATH . '/app/Views/partials/section-card.php';
?>

        <div class="arama">
            <input type="text" id="stokAramaInput" onkeyup="stokFiltrele()" placeholder="Stok kodu veya ürün adı ile ara...">
        </div>

        <div class="table-wrap">
            <table id="stokTablosuGosterimi" class="stok-table">
                <thead>
                    <tr>
                        <th>Stok Kodu</th>
                        <th>Ürün Adı</th>
                        <th>Miktar</th>
                        <th>Birim</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stoklar as $stok): ?>
                        <tr>
                            <td>
                                <span class="badge badge-gri stok-code-badge"><?= e($stok['stok_kodu']) ?></span>
                            </td>
                            <td>
                                <div class="veri-vurgu"><?= e($stok['urun_adi']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-mavi stok-qty-badge">
                                    <?= number_format((float) ($stok['stok_miktari'] ?? 0), 4, ',', '.') ?>
                                </span>
                            </td>
                            <td><?= e($stok['birim'] ?? 'Adet') ?></td>
                            <td>
                                <div class="aksiyonlar stok-actions">
    <button
        type="button"
        class="btn btn-gri"
        onclick="stokHareketleriAc(this)"
        data-stok='<?= e(json_encode($stok, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
    >
        Hareketler
    </button>

    <button
        type="button"
        class="btn btn-sari"
        onclick="stokDuzenleAc(this)"
        data-stok='<?= e(json_encode($stok, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
    >
        Düzenle
    </button>

    <form method="POST" action="<?= e(url('stoklar/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu stoğu silmek istediğinize emin misiniz?');">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="id" value="<?= (int)$cari['id'] ?>">
    <button type="submit" class="btn btn-kirmizi">Sil</button>
</form>
</div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($stoklar)): ?>
                        <tr>
                            <td colspan="5">Henüz kayıtlı stok bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
	
	<?php require __DIR__ . '/partials/create-modal.php'; ?>
<?php require __DIR__ . '/partials/edit-modal.php'; ?>
<?php require __DIR__ . '/partials/movements-modal.php'; ?>