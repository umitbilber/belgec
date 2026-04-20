<style>
    .cari-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .cari-page-header-left {
        flex: 1 1 340px;
        min-width: 0;
    }

    .cari-page-header-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .cari-stat-badge {
        max-width: 100%;
    }

    .cari-list-panel {
        min-width: 0;
    }

    .cari-table td,
    .cari-table th {
        word-break: break-word;
    }

    .cari-name-cell,
    .cari-contact-cell {
        min-width: 0;
    }

    .cari-balance-badge {
        max-width: 100%;
        text-align: center;
    }

    .cari-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .cari-actions .btn {
        flex: 0 0 auto;
    }

    .cari-modal-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .cari-modal-actions .btn,
    .cari-modal-actions button {
        flex: 0 0 auto;
    }

    @media (max-width: 980px) {
        .cari-page-header {
            align-items: stretch;
        }

        .cari-page-header-right {
            width: 100%;
            justify-content: space-between;
        }

        .cari-stat-badge {
            width: auto;
        }
    }

    @media (max-width: 640px) {
        .cari-page-header {
            gap: 12px;
        }

        .cari-page-header-right {
            flex-direction: column;
            align-items: stretch;
        }

        .cari-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .cari-actions .btn {
            width: 100%;
        }

        .cari-modal-actions {
            flex-direction: column;
        }

        .cari-modal-actions .btn,
        .cari-modal-actions button {
            width: 100%;
        }

        .cari-list-panel {
            padding: 14px !important;
        }
    }
	.cari-autocomplete-wrap {
    position: relative;
}

.cari-autocomplete-results {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    z-index: 40;
    display: none;
    background: #fff;
    border: 1px solid #d7deea;
    border-radius: 12px;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    max-height: 260px;
    overflow-y: auto;
}

.cari-autocomplete-item {
    width: 100%;
    border: 0;
    background: transparent;
    text-align: left;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    cursor: pointer;
}

.cari-autocomplete-item:hover {
    background: #f4f7fb;
}

.cari-autocomplete-item.disabled {
    cursor: default;
    color: #6b7280;
}

.cari-autocomplete-item.disabled:hover {
    background: transparent;
}

.cari-autocomplete-name {
    font-weight: 600;
    color: #1f2937;
}

.cari-autocomplete-meta {
    font-size: 12px;
    color: #6b7280;
}
    .aging-filter-card {
        padding: 24px !important;
        margin-bottom: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid rgba(191, 219, 254, 0.65);
    }

    .aging-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }

    .aging-filter-actions {
        display: flex;
        gap: 10px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .aging-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .aging-summary-card {
        margin-bottom: 0 !important;
        padding: 18px 18px 16px 18px !important;
        position: relative;
        overflow: hidden;
        border-width: 1px;
    }

    .aging-summary-card::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        border-radius: 16px 0 0 16px;
        background: #cbd5e1;
    }

    .aging-summary-total {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-color: #e2e8f0;
    }

    .aging-summary-total::before {
        background: linear-gradient(180deg, #334155 0%, #0f172a 100%);
    }

    .aging-summary-label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        margin-bottom: 10px;
    }

    .aging-summary-value {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.2;
        color: #0f172a;
        word-break: break-word;
    }

    .bucket-vadesi-gelmemis {
        background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%);
        border-color: #bbf7d0;
    }

    .bucket-vadesi-gelmemis::before,
    .aging-bucket-badge.bucket-vadesi-gelmemis {
        background: #dcfce7;
        color: #166534;
    }

    .bucket-0-30 {
        background: linear-gradient(180deg, #ffffff 0%, #eff6ff 100%);
        border-color: #bfdbfe;
    }

    .bucket-0-30::before,
    .aging-bucket-badge.bucket-0-30 {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .bucket-31-60 {
        background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%);
        border-color: #fed7aa;
    }

    .bucket-31-60::before,
    .aging-bucket-badge.bucket-31-60 {
        background: #ffedd5;
        color: #c2410c;
    }

    .bucket-61-90 {
        background: linear-gradient(180deg, #ffffff 0%, #fff1f2 100%);
        border-color: #fecdd3;
    }

    .bucket-61-90::before,
    .aging-bucket-badge.bucket-61-90 {
        background: #ffe4e6;
        color: #be123c;
    }

    .bucket-90-plus {
        background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
    }

    .bucket-90-plus::before,
    .aging-bucket-badge.bucket-90-plus {
        background: #fee2e2;
        color: #b91c1c;
    }

    .bucket-default::before,
    .aging-bucket-badge.bucket-default {
        background: #e5e7eb;
        color: #374151;
    }

    .aging-table-card {
        padding: 24px !important;
    }

    .aging-section-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }

    .aging-section-head h3 {
        margin: 0 0 6px 0;
    }

    .aging-section-desc {
        margin-bottom: 0;
    }

    .aging-table-wrap {
        border: 1px solid #e6edf5;
        border-radius: 16px;
        background: #fff;
    }

    .aging-table {
        min-width: 1050px;
    }

    .aging-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }

    .aging-table tbody tr {
        transition: background .18s ease, transform .18s ease;
    }

    .aging-table tbody tr:hover {
        background: #f8fbff;
    }

    .aging-type-badge,
    .aging-bucket-badge,
    .aging-delay-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .aging-type-badge.type-satis {
        background: #ede9fe;
        color: #6d28d9;
    }

    .aging-type-badge.type-alis {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .aging-delay-badge.delay-danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .aging-delay-badge.delay-ok {
        background: #f1f5f9;
        color: #64748b;
    }

    .aging-open-amount {
        color: #0f172a;
        font-size: 15px;
    }

    .aging-row.bucket-90-plus td:first-child,
    .aging-row.bucket-61-90 td:first-child {
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .aging-filter-card,
        .aging-table-card {
            padding: 16px !important;
        }

        .aging-summary-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .aging-summary-value {
            font-size: 22px;
        }

        .aging-filter-actions {
            flex-direction: column;
        }

        .aging-filter-actions .btn {
            width: 100%;
        }

        .aging-table {
            min-width: 0;
        }

        .aging-table thead {
            display: none;
        }

        .aging-table,
        .aging-table tbody,
        .aging-table tr,
        .aging-table td {
            display: block;
            width: 100%;
        }

        .aging-table tbody tr {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
        }

        .aging-table tbody tr:last-child {
            border-bottom: 0;
        }

        .aging-table td {
            border-bottom: 0;
            padding: 8px 0;
            font-size: 13px;
        }

        .aging-table td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
        }
    }
</style>