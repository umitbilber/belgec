<?php
$formSectionTitle = $formSectionTitle ?? '';
$formSectionDescription = $formSectionDescription ?? '';
?>

<div class="kutu-baslik">
    <div>
        <h4><?= e($formSectionTitle) ?></h4>

        <?php if ($formSectionDescription !== ''): ?>
            <p class="kutu-aciklama"><?= e($formSectionDescription) ?></p>
        <?php endif; ?>
    </div>
</div>