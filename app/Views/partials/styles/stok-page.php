<style>
    .stok-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .stok-page-header-left {
        flex: 1 1 340px;
        min-width: 0;
    }

    .stok-page-header-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .stok-stat-badge {
        max-width: 100%;
    }

    .stok-list-panel {
        min-width: 0;
    }

    .stok-table td,
    .stok-table th {
        word-break: break-word;
    }

    .stok-code-badge,
    .stok-qty-badge {
        max-width: 100%;
        text-align: center;
    }

    .stok-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .stok-actions .btn {
        flex: 0 0 auto;
    }

    .stok-modal-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .stok-modal-actions .btn,
    .stok-modal-actions button {
        flex: 0 0 auto;
    }

    .stok-hareket-bilgi {
        word-break: break-word;
    }

    @media (max-width: 980px) {
        .stok-page-header {
            align-items: stretch;
        }

        .stok-page-header-right {
            width: 100%;
            justify-content: space-between;
        }

        .stok-stat-badge {
            width: auto;
            justify-content: center;
        }
    }

    @media (max-width: 640px) {
        .stok-page-header {
            gap: 12px;
        }

        .stok-page-header-right {
            flex-direction: column;
            align-items: stretch;
        }

        .stok-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .stok-actions .btn {
            width: 100%;
        }

        .stok-modal-actions {
            flex-direction: column;
        }

        .stok-modal-actions .btn,
        .stok-modal-actions button {
            width: 100%;
        }

        .stok-list-panel {
            padding: 14px !important;
        }
    }
</style>