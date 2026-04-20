<?php
$headerClass = $headerClass ?? '';
$headerLeftClass = $headerLeftClass ?? '';
$headerRightClass = $headerRightClass ?? '';
$title = $title ?? '';
$description = $description ?? '';
$badgeText = $badgeText ?? '';
$badgeClass = $badgeClass ?? '';
$actionHtml = $actionHtml ?? '';
?>

<div class="sayfa-ust <?= e($headerClass) ?>">
    <div class="sayfa-ust-sol <?= e($headerLeftClass) ?>">
        <h2><?= e($title) ?></h2>
        <?php if ($description !== ''): ?>
            <p class="sayfa-aciklama">
                <?= e($description) ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if ($badgeText !== '' || $actionHtml !== ''): ?>
        <div class="<?= e($headerRightClass) ?>">
            <?php if ($badgeText !== ''): ?>
                <div class="<?= e($badgeClass) ?>">
                    <?= e($badgeText) ?>
                </div>
            <?php endif; ?>

            <?= $actionHtml ?>
        </div>
    <?php endif; ?>
</div>