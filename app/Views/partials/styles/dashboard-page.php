<style>
    .dashboard-page-wrap {
        padding: 26px;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        flex-wrap: wrap;
    }

    .dashboard-header-left {
        flex: 1 1 340px;
        min-width: 0;
    }

    .dashboard-page-title {
        margin-bottom: 8px;
    }

    .dashboard-page-desc,
    .dashboard-section-desc {
        margin-top: 0;
    }

    .dashboard-date-box {
        padding: 12px 14px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(37,99,235,.08), rgba(37,99,235,.14));
        border: 1px solid rgba(37,99,235,.14);
        min-width: 220px;
        max-width: 100%;
    }

    .dashboard-date-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .dashboard-date-value {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin-top: 4px;
    }

    .dashboard-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 18px;
        margin-top: 24px;
    }

    .dashboard-stat-card {
        padding: 22px;
        border-radius: 16px;
    }

    .dashboard-stat-card.tahsilat {
        background: linear-gradient(180deg, #ffffff 0%, #f8fffb 100%);
        border: 1px solid #dcfce7;
        box-shadow: 0 10px 24px rgba(22,163,74,.06);
    }

    .dashboard-stat-card.tediye {
        background: linear-gradient(180deg, #ffffff 0%, #fff8f5 100%);
        border: 1px solid #fed7aa;
        box-shadow: 0 10px 24px rgba(234,88,12,.06);
    }

    .dashboard-stat-card.alis {
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        border: 1px solid #dbeafe;
        box-shadow: 0 10px 24px rgba(37,99,235,.06);
    }

    .dashboard-stat-card.satis {
        background: linear-gradient(180deg, #ffffff 0%, #fbf8ff 100%);
        border: 1px solid #e9d5ff;
        box-shadow: 0 10px 24px rgba(147,51,234,.06);
    }

    .dashboard-stat-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .dashboard-stat-label.tahsilat {
        color: #15803d;
    }

    .dashboard-stat-label.tediye {
        color: #c2410c;
    }

    .dashboard-stat-label.alis {
        color: #1d4ed8;
    }

    .dashboard-stat-label.satis {
        color: #7e22ce;
    }

    .dashboard-stat-value {
        font-size: 30px;
        font-weight: 800;
        margin-top: 10px;
        word-break: break-word;
    }

    .dashboard-stat-value.tahsilat {
        color: #166534;
    }

    .dashboard-stat-value.tediye {
        color: #c2410c;
    }

    .dashboard-stat-value.alis {
        color: #1e40af;
    }

    .dashboard-stat-value.satis {
        color: #6b21a8;
    }

    .dashboard-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 18px;
        margin-top: 22px;
    }

    .dashboard-module-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 12px 26px rgba(15,23,42,.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 220px;
        background: #fff;
    }

    .dashboard-module-card.soft {
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        box-shadow: 0 12px 26px rgba(15,23,42,.04);
    }

    .dashboard-module-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 16px;
    }

    .dashboard-module-title {
        margin: 0 0 10px 0;
        color: #0f172a;
        font-size: 18px;
        line-height: 1.3;
        word-break: break-word;
    }

    .dashboard-module-desc {
        font-size: 13px;
        color: #64748b;
        margin-top: 0;
        line-height: 1.6;
    }

    .dashboard-action {
        margin-top: 18px;
    }

    .dashboard-action .btn {
        width: 100%;
    }

    .dashboard-module-btn {
        color: #fff;
    }

    .dashboard-empty-box {
        margin-top: 18px;
    }

    @media (max-width: 768px) {
        .dashboard-date-box {
            width: 100%;
            min-width: 0;
        }

        .dashboard-stats-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .dashboard-card-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .dashboard-module-card {
            min-height: unset;
            padding: 18px;
        }

        .dashboard-stat-card {
            padding: 18px;
        }
    }

    @media (max-width: 640px) {
        .dashboard-header {
            gap: 14px;
        }

        .dashboard-module-title {
            font-size: 17px;
        }

        .dashboard-module-desc {
            font-size: 13px;
        }

        .dashboard-stat-value {
            font-size: 24px;
            margin-top: 8px;
        }

        .dashboard-date-value {
            font-size: 18px;
        }

        .dashboard-page-wrap {
            padding: 14px;
        }
    }
</style>