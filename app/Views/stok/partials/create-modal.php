<?php
ob_start();
?>
<form method="POST" action="<?= e(url('stoklar/ekle')) ?>" class="app-form-stack">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <label>Stok Kodu</label>
    <input type="text" name="stok_kodu" required data-modal-autofocus>

    <label>Ürün Adı</label>
    <input type="text" name="urun_adi" required>

    <label>Birim Tipi</label>
    <select name="birim">
        <option value="Adet">Adet</option>
        <option value="Kg">Kilogram (Kg)</option>
        <option value="Metre">Metre</option>
        <option value="Koli">Koli</option>
        <option value="Paket">Paket</option>
        <option value="Litre">Litre</option>
    </select>

    <label>Başlangıç Miktarı</label>
    <input type="number" step="0.0001" name="stok_miktari" value="0">

    <div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Stok Kartını Oluştur</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId = 'createStokModal';
$modalTitle = 'Yeni Stok Ekle';
$modalDescription = 'Yeni ürün veya stok kartı tanımlayın.';
$modalSize = 'md';

require __DIR__ . '/../../partials/modal.php';