<?php
ob_start();
?>
<form method="POST" action="<?= e(url('stoklar/guncelle')) ?>" class="app-form-stack" id="editStokForm">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="stok_id" value="">

    <label>Stok Kodu</label>
    <input type="text" name="stok_kodu" value="" required>

    <label>Ürün Adı</label>
    <input type="text" name="urun_adi" value="" required>

    <div class="form-grid-3">
        <div>
            <label>Mevcut Miktar</label>
            <input type="number" step="0.0001" name="stok_miktari" value="">
        </div>

        <div>
            <label>Birim Tipi</label>
            <select name="birim">
                <option value="Adet">Adet</option>
                <option value="Kg">Kilogram (Kg)</option>
                <option value="Metre">Metre</option>
                <option value="Koli">Koli</option>
                <option value="Paket">Paket</option>
                <option value="Litre">Litre</option>
            </select>
        </div>
    </div>

    <div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Güncelle</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId = 'editStokModal';
$modalTitle = 'Stok Kartı Düzenle';
$modalDescription = 'Stok kartı bilgilerini ve mevcut miktarı güncelleyin.';
$modalSize = 'md';

require __DIR__ . '/../../partials/modal.php';