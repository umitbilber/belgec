<?php
ob_start();
?>
<form method="POST" action="<?= e(url('cariler/ekle')) ?>" class="app-form-stack">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <label>Cari Unvanı</label>
    <input type="text" name="ad_soyad" required data-modal-autofocus>

    <label>Telefon</label>
    <input type="text" name="telefon">

    <label>E-Posta</label>
    <input type="text" name="eposta">

    <label>Adres</label>
    <textarea name="adres"></textarea>

    <label>Vergi Numarası</label>
    <input type="text" name="vergi_no">
    
    <label>Varsayılan Vade Günü</label>
<input type="number" name="varsayilan_vade_gun" min="0" step="1" value="0">
<small style="display:block; margin-top:6px; color:#666;">
    Yeni oluşturulacak faturalarda varsayılan vade hesabı için kullanılabilir.
</small>

    <label>Açılış Bakiyesi</label>
    <input type="number" step="0.0001" name="bakiye" value="0">

    <div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Cari Kaydını Oluştur</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId = 'createCariModal';
$modalTitle = 'Yeni Cari Ekle';
$modalDescription = 'Yeni müşteri veya tedarikçi hesabı tanımlayın.';
$modalSize = 'md';

require __DIR__ . '/../../partials/modal.php';