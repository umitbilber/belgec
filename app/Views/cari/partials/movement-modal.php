<?php
ob_start();
?>
<div class="bilgi-kutusu" id="cariHareketBilgiKutusu">
    <strong>Cari Unvanı:</strong> <span data-cari-hareket-unvan>-</span>
</div>

<form method="POST" action="<?= e(url('cariler/hareket')) ?>" class="app-form-stack" id="cariMovementForm">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="cari_id" value="">
    <input type="hidden" name="islem_tipi" value="">

    <label>İşlem Tarihi</label>
    <input type="date" name="tarih" value="<?= date('Y-m-d') ?>" required>

    <label>İşlem Tutarı (TL)</label>
    <input type="number" step="0.0001" name="tutar" required>

    <label>Açıklama / Makbuz No</label>
    <input type="text" name="aciklama" required>

    <div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn" id="cariMovementSubmitButton">Kaydet</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId = 'cariMovementModal';
$modalTitle = 'Cari Hareketi';
$modalDescription = 'Tahsilat veya tediye işlemini kaydedin.';
$modalSize = 'md';

require __DIR__ . '/../../partials/modal.php';