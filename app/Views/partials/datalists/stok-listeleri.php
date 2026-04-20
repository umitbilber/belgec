<?php if (!empty($include_stok_kodu_listesi)): ?>
    <datalist id="stokKodlariListesi">
        <?php foreach (($stoklar ?? []) as $stok): ?>
            <option value="<?= e($stok['stok_kodu'] ?? '') ?>"></option>
        <?php endforeach; ?>
    </datalist>
<?php endif; ?>

<datalist id="stokUrunleriListesi">
    <?php foreach (($stoklar ?? []) as $stok): ?>
        <option value="<?= e($stok['urun_adi'] ?? '') ?>"></option>
    <?php endforeach; ?>
</datalist>