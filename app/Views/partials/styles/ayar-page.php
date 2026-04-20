<style>
    .ayar-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .ayar-page-header-left {
        flex: 1 1 340px;
        min-width: 0;
    }

    .ayar-stat-badge {
        max-width: 100%;
    }

    .ayar-form-panel,
    .ayar-list-panel {
        min-width: 0;
    }

    .ayar-table td,
    .ayar-table th {
        word-break: break-word;
    }

    .ayar-table .badge {
        max-width: 100%;
        text-align: center;
    }

    .ayar-save-btn {
        max-width: 100%;
    }

    .ayar-smtp-note {
        margin-top: 8px;
    }

    .ayar-teklif-sartlari {
        min-height: 160px;
    }

    .ayar-table-action .btn {
        max-width: 100%;
    }

    @media (max-width: 980px) {
        .ayar-page-header {
            align-items: stretch;
        }

        .ayar-stat-badge {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 640px) {
        .ayar-page-header {
            gap: 12px;
        }

        .ayar-form-panel,
        .ayar-list-panel {
            padding: 14px !important;
        }

        .ayar-save-btn,
        .ayar-table-action .btn {
            width: 100%;
        }
    }
</style>