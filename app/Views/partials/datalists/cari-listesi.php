<datalist id="cariListesi">
    <?php foreach (($cariler ?? []) as $cari): ?>
        <option value="<?= e($cari['ad_soyad'] ?? '') ?>"></option>
    <?php endforeach; ?>
</datalist>