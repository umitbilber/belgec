<?php
$prefix = $fatura_style_prefix ?? 'fatura';
$pageHeaderClass = $prefix . '-page-header';
$pageHeaderLeftClass = $prefix . '-page-header-left';
$pageHeaderRightClass = $prefix . '-page-header-right';
$statBadgeClass = $prefix . '-stat-badge';
$listPanelClass = $prefix . '-list-panel';
$tableClass = $prefix . '-table';
$totalBadgeClass = $prefix . '-total-badge';
$actionsClass = $prefix . '-actions';
$formActionsClass = $prefix . '-form-actions';
$modalActionsClass = $prefix . '-modal-actions';
$kalemlerWrapClass = $prefix . '-kalemler-wrap';
$duzenleKalemlerWrapClass = $prefix . '-duzenle-kalemler-wrap';
$itemClass = $prefix . '-kalem-item';
$rowClass = $prefix . '-kalem-row';
$contentClass = $prefix . '-kalem-icerik';
$handleClass = $prefix . '-drag-handle';
$deleteClass = $prefix . '-kalem-sil';
$modalClass = 'app-modal-' . $prefix;
?>
<style>
    .<?= e($pageHeaderClass) ?> {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .<?= e($pageHeaderLeftClass) ?> {
        flex: 1 1 340px;
        min-width: 0;
    }

    .<?= e($pageHeaderRightClass) ?> {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .<?= e($statBadgeClass) ?> {
        max-width: 100%;
    }

    .<?= e($listPanelClass) ?> {
        min-width: 0;
    }

    .<?= e($tableClass) ?> td,
    .<?= e($tableClass) ?> th {
        word-break: break-word;
    }

    .<?= e($totalBadgeClass) ?> {
        max-width: 100%;
        text-align: center;
    }

    .<?= e($actionsClass) ?> {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .<?= e($actionsClass) ?> .btn {
        flex: 0 0 auto;
    }

    .<?= e($formActionsClass) ?>,
    .<?= e($modalActionsClass) ?> {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .<?= e($formActionsClass) ?> .btn,
    .<?= e($formActionsClass) ?> button,
    .<?= e($modalActionsClass) ?> .btn,
    .<?= e($modalActionsClass) ?> button {
        flex: 0 0 auto;
    }

    .<?= e($kalemlerWrapClass) ?>,
    .<?= e($duzenleKalemlerWrapClass) ?> {
        min-width: 0;
    }

    .<?= e($itemClass) ?> {
        padding: 14px;
    }

    .<?= e($rowClass) ?> {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .<?= e($contentClass) ?> {
        flex: 1 1 auto;
        min-width: 0;
    }

    .<?= e($handleClass) ?> {
        flex: 0 0 auto;
        margin-top: 2px;
    }

    .<?= e($deleteClass) ?> {
        flex: 0 0 auto;
        min-width: 84px;
    }

    .<?= e($modalClass) ?> .app-modal-card {
        max-width: 1180px;
    }

    @media (max-width: 980px) {
        .<?= e($pageHeaderClass) ?> {
            align-items: stretch;
        }

        .<?= e($pageHeaderRightClass) ?> {
            width: 100%;
            justify-content: space-between;
        }

        .<?= e($statBadgeClass) ?> {
            width: auto;
            justify-content: center;
        }

        .<?= e($rowClass) ?> {
            flex-wrap: wrap;
        }

        .<?= e($deleteClass) ?> {
            min-width: 0;
        }
    }

    @media (max-width: 640px) {
        .<?= e($pageHeaderClass) ?> {
            gap: 12px;
        }

        .<?= e($pageHeaderRightClass) ?>,
        .<?= e($actionsClass) ?>,
        .<?= e($formActionsClass) ?>,
        .<?= e($modalActionsClass) ?> {
            flex-direction: column;
            align-items: stretch;
        }

        .<?= e($actionsClass) ?> .btn,
        .<?= e($formActionsClass) ?> .btn,
        .<?= e($formActionsClass) ?> button,
        .<?= e($modalActionsClass) ?> .btn,
        .<?= e($modalActionsClass) ?> button {
            width: 100%;
        }

        .<?= e($listPanelClass) ?> {
            padding: 14px !important;
        }

        .<?= e($rowClass) ?> {
            gap: 10px;
        }

        .<?= e($handleClass) ?> {
            width: 100%;
            min-width: 0;
        }

        .<?= e($deleteClass) ?> {
            width: 100%;
        }
    }
</style>