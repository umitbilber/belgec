<?php
ob_start();
?>
<form method="POST" action="<?= e(url('cariler/guncelle')) ?>" class="app-form-stack" id="editCariForm">
    <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
    <input type="hidden" name="cari_id" value="">

    <label>Cari Unvanı</label>
    <input type="text" name="ad_soyad" value="" required>

    <label>Telefon</label>
    <input type="text" name="telefon" value="">

    <label>E-Posta</label>
    <input type="text" name="eposta" value="">

    <label>Adres</label>
    <textarea name="adres"></textarea>

        <label>Vergi Numarası</label>
    <input type="text" name="vergi_no" value="">
    
    <label>Varsayılan Vade Günü</label>
<input type="number" name="varsayilan_vade_gun" min="0" step="1" value="0">
<small style="display:block; margin-top:6px; color:#666;">
    Bu cariye ait yeni faturalarda kullanılacak varsayılan vade gün sayısı.
</small>

    <label>Elle Düzeltilmiş Bakiye</label>
    <input type="number" step="0.0001" name="duzeltilmis_bakiye" value="">
    <small style="display:block; margin-top:6px; color:#666;">
        Bu alanı doldurursanız cari bakiyesi yazdığınız tutara çekilir ve fark kadar düzeltme hareketi oluşturulur.
    </small>

    <div class="panel-divider"></div>

    <div class="app-modal-footer">
        <button type="button" class="btn btn-gri" data-modal-close>İptal</button>
        <button type="submit" class="btn btn-ana">Güncelle</button>
    </div>
</form>
<?php
$modalContent = ob_get_clean();

$modalId = 'editCariModal';
$modalTitle = 'Cari Düzenle';
$modalDescription = 'Cari kartı bilgilerini güncelleyin.';
$modalSize = 'md';

require __DIR__ . '/../../partials/modal.php';