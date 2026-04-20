<?php require BASE_PATH . '/app/Views/partials/scripts/teklif-form.php'; ?>
<?php require BASE_PATH . '/app/Views/partials/styles/teklif-page.php'; ?>

<?php
$title = 'Teklifler';
$description = 'Müşterilere özel fiyat teklifleri oluştur, satır bazlı para birimi kullan ve yazdırılabilir teklif çıktısı al.';
$badgeText = 'Toplam Teklif: ' . count($teklifler);
$headerClass = 'teklif-page-header';
$headerLeftClass = 'teklif-page-header-left';
$headerRightClass = 'teklif-page-header-right';
$badgeClass = 'liste-ozet teklif-stat-badge';
$actionHtml = '<button type="button" class="btn btn-ana" data-modal-open="createTeklifModal">+ Yeni Teklif</button>';

require BASE_PATH . '/app/Views/partials/page-header.php';
?>

<?php
$sectionClass = 'teklif-list-panel';
$sectionTitle = 'Teklif Listesi';
$sectionDescription = 'Hazırlanan teklifleri yazdırabilir, düzenleyebilir veya silebilirsin.';

require BASE_PATH . '/app/Views/partials/section-card.php';
?>

    <div class="table-wrap">
        <table class="teklif-table">
            <thead>
                <tr>
                    <th>Teklif No</th>
                    <th>Tarih</th>
                    <th>Müşteri</th>
                    <th>Tutar</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teklifler as $tek): ?>
                    <tr>
                        <td>
                            <div class="veri-vurgu"><?= e($tek['teklif_no']) ?></div>
                            <div class="satir-ikincil">Teklif Kaydı</div>
                        </td>
                        <td><?= e(date('d.m.Y', strtotime((string) $tek['tarih']))) ?></td>
                        <td>
                            <div class="veri-vurgu"><?= e($tek['cari_adi'] ?? '-') ?></div>
                        </td>
                        <td>
                            <div class="para-vurgu teklif-tutar"><?= e($tek['toplam_ozeti'] ?? '0,00 ₺') ?></div>
                        </td>
                        <td>
                            <div class="aksiyonlar teklif-actions">
                                <a class="btn btn-gri" href="<?= e(url('teklifler/yazdir?id=' . $tek['id'])) ?>" target="_blank">Yazdır</a>
                                <button
    type="button"
    class="btn btn-sari"
    onclick="teklifDuzenleAc(this)"
    data-teklif='<?= e(json_encode($tek, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>'
>
    Düzenle
</button>
                                <form method="POST" action="<?= e(url('teklifler/sil')) ?>" style="display:inline;" onsubmit="return confirm('Bu teklifi silmek istediğinize emin misiniz?');">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
    <button type="submit" class="btn btn-kirmizi">Sil</button>
</form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($teklifler)): ?>
                    <tr>
                        <td colspan="5">Kayıtlı teklif bulunmuyor.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/datalists/cari-listesi.php'; ?>
<?php unset($include_stok_kodu_listesi); ?>
<?php require BASE_PATH . '/app/Views/partials/datalists/stok-listeleri.php'; ?>

<?php require __DIR__ . '/partials/create-modal.php'; ?>
<?php require __DIR__ . '/partials/edit-modal.php'; ?>
