<?php if (!empty($hata_mesaji)): ?>
    <div class="hata-kutusu"><?= e($hata_mesaji) ?></div>
<?php endif; ?>

<?php if (!empty($bilgi_mesaji)): ?>
    <div class="bilgi-kutusu"><?= e($bilgi_mesaji) ?></div>
<?php endif; ?>