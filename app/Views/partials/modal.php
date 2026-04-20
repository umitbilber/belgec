<?php
$modalId = $modalId ?? 'appModal';
$modalTitle = $modalTitle ?? 'Pencere';
$modalDescription = $modalDescription ?? '';
$modalSize = $modalSize ?? 'md'; // sm | md | lg | xl | full
$modalContent = $modalContent ?? '';
$modalClass = $modalClass ?? '';
?>

<div
    id="<?= e($modalId) ?>"
    class="app-modal <?= e($modalClass) ?>"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="<?= e($modalId) ?>Title"
>
    <div class="app-modal-backdrop"></div>

    <div class="app-modal-dialog app-modal-size-<?= e($modalSize) ?>">
        <div class="app-modal-card">
            <div class="app-modal-header">
                <div class="app-modal-header-text">
                    <h3 id="<?= e($modalId) ?>Title"><?= e($modalTitle) ?></h3>

                    <?php if ($modalDescription !== ''): ?>
                        <p><?= e($modalDescription) ?></p>
                    <?php endif; ?>
                </div>

                <button type="button" class="app-modal-close" data-modal-close aria-label="Pencereyi kapat">
                    ×
                </button>
            </div>

            <div class="app-modal-body">
                <?= $modalContent ?>
            </div>
        </div>
    </div>
</div>