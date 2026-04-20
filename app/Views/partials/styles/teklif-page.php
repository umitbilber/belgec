<style>
    .teklif-notlar-yuksek {
        min-height: 140px;
    }
    .teklif-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .teklif-page-header-left {
        flex: 1 1 340px;
        min-width: 0;
    }

    .teklif-page-header-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .teklif-stat-badge {
        max-width: 100%;
    }

    .teklif-list-panel {
        min-width: 0;
    }

    .teklif-table td,
    .teklif-table th {
        word-break: break-word;
    }

    .teklif-tutar {
        word-break: break-word;
        white-space: normal;
    }

    .teklif-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .teklif-actions .btn {
        flex: 0 0 auto;
    }

    .teklif-form-actions,
    .teklif-modal-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .teklif-form-actions .btn,
    .teklif-form-actions button,
    .teklif-modal-actions .btn,
    .teklif-modal-actions button {
        flex: 0 0 auto;
    }

    .teklif-kalemler-wrap,
    .teklif-duzenle-kalemler-wrap {
        min-width: 0;
    }

    .teklif-notlar {
        width: 100%;
    }

    .teklif-kalem-item {
        padding: 14px;
    }

    .teklif-kalem-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .teklif-kalem-icerik {
        flex: 1 1 auto;
        min-width: 0;
    }

    .teklif-drag-handle {
        flex: 0 0 auto;
        margin-top: 2px;
    }

    .teklif-kalem-sil {
        flex: 0 0 auto;
        min-width: 84px;
    }

    @media (max-width: 980px) {
        .teklif-page-header {
            align-items: stretch;
        }

        .teklif-page-header-right {
            width: 100%;
            justify-content: space-between;
        }

        .teklif-stat-badge {
            width: auto;
            justify-content: center;
        }

        .teklif-kalem-row {
            flex-wrap: wrap;
        }

        .teklif-kalem-sil {
            min-width: 0;
        }
    }

    @media (max-width: 640px) {
        .teklif-page-header {
            gap: 12px;
        }

        .teklif-page-header-right,
        .teklif-actions,
        .teklif-form-actions,
        .teklif-modal-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .teklif-actions .btn,
        .teklif-form-actions .btn,
        .teklif-form-actions button,
        .teklif-modal-actions .btn,
        .teklif-modal-actions button {
            width: 100%;
        }

        .teklif-list-panel {
            padding: 14px !important;
        }

        .teklif-tutar {
            font-size: 13px;
        }

        .teklif-kalem-row {
            gap: 10px;
        }

        .teklif-drag-handle {
            width: 100%;
            min-width: 0;
        }

        .teklif-kalem-sil {
            width: 100%;
        }
    }
</style>