<?php
$sectionClass = $sectionClass ?? '';
$sectionTitle = $sectionTitle ?? '';
$sectionDescription = $sectionDescription ?? '';
?>

<div class="kutu liste-panel <?= e($sectionClass) ?>">
    <div class="kutu-baslik">
        <div>
            <h3><?= e($sectionTitle) ?></h3>
            <?php if ($sectionDescription !== ''): ?>
                <p class="kutu-aciklama"><?= e($sectionDescription) ?></p>
            <?php endif; ?>
        </div>
    </div>