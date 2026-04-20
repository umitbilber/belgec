<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?= e($pageTitle ?? 'Belgeç') ?> - Belgeç</title>
	
	<link rel="icon" type="image/png" href="<?= e(url('favicon.png')) ?>">
<link rel="shortcut icon" href="<?= e(url('favicon.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(url('icons/icon-192.png')) ?>">

    <link rel="manifest" href="<?= e(url('pwa/manifest.json')) ?>">
    <meta name="theme-color" content="<?= e($ayarlar['tema_rengi'] ?? '#2563eb') ?>">
	<meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="application-name" content="Belgeç">
<meta name="apple-mobile-web-app-title" content="Belgeç">

    <style>
        :root {
            --ana-renk: <?= e($ayarlar['tema_rengi'] ?? '#2563eb') ?>;
            --ana-renk-koyu: #1d4ed8;
            --arka-plan: #f3f6fb;
            --yazi: #1f2937;
            --yazi-soluk: #6b7280;
            --kenar: #e5e7eb;
            --beyaz: #ffffff;
            --golge: 0 10px 30px rgba(15, 23, 42, 0.06);
            --golge-hover: 0 16px 40px rgba(15, 23, 42, 0.10);
            --radius: 16px;
            --radius-kucuk: 10px;
            --baslik: #0f172a;
            --basarili: #16a34a;
            --uyari: #d97706;
            --tehlike: #dc2626;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.05), transparent 28%),
                radial-gradient(circle at top right, rgba(15, 23, 42, 0.04), transparent 22%),
                var(--arka-plan);
            color: var(--yazi);
            overflow-x: hidden;
        }

        a {
            color: inherit;
        }
		        .app-loader {
            position: fixed;
            inset: 0;
            z-index: 20000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 30%),
                rgba(243, 246, 251, 0.82);
            backdrop-filter: blur(10px);
            opacity: 1;
            visibility: visible;
            transition: opacity 0.22s ease, visibility 0.22s ease;
        }

        .app-loader.is-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .app-loader-card {
            width: min(100%, 280px);
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 24px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.16);
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            text-align: center;
        }

        .app-loader-logo-wrap {
            position: relative;
            width: 82px;
            height: 82px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .app-loader-ring {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            border: 3px solid rgba(37, 99, 235, 0.12);
            border-top-color: var(--ana-renk);
            animation: app-loader-spin 0.95s linear infinite;
        }

        .app-loader-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.16);
            background: #fff;
        }

        .app-loader-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--baslik);
        }

        .app-loader-text {
            margin: 0;
            font-size: 13px;
            line-height: 1.6;
            color: var(--yazi-soluk);
        }

        @keyframes app-loader-spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .app-loader,
            .app-loader-ring {
                transition: none;
                animation: none;
            }
        }

        @media (max-width: 640px) {
            .app-loader-card {
                width: min(100%, 240px);
                padding: 24px 18px;
                border-radius: 20px;
            }

            .app-loader-logo-wrap {
                width: 72px;
                height: 72px;
            }

            .app-loader-logo {
                width: 42px;
                height: 42px;
            }

            .app-loader-title {
                font-size: 16px;
            }

            .app-loader-text {
                font-size: 12px;
            }
        }
		
		        .ust-bar {
            background:
                linear-gradient(135deg, <?= e($ayarlar['tema_rengi'] ?? '#2563eb') ?> 0%, #1e3a8a 100%);
            color: #fff;
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(8px);
        }

        .ust-bar-sol {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .ust-bar-baslik {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: .01em;
            line-height: 1.2;
            color: #fff;
        }

        .ust-bar-alt {
            font-size: 12px;
            opacity: .92;
            color: rgba(255,255,255,.92);
        }

        .ust-bar-sag {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }

        .ust-bar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 44px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,.18);
            background: rgba(255,255,255,.08);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            line-height: 1;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }

        .ust-bar-btn:hover {
            transform: translateY(-1px);
            background: rgba(255,255,255,.14);
            border-color: rgba(255,255,255,.28);
            box-shadow:
                0 10px 24px rgba(15, 23, 42, .14),
                inset 0 1px 0 rgba(255,255,255,.08);
        }

        .ust-bar-btn-ikon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
        }

        .ust-bar-btn-ikon svg {
            width: 18px;
            height: 18px;
            display: block;
        }

        .ust-bar-btn-cikis {
            background: rgba(255,255,255,.06);
        }

        .ust-bar-btn-cikis:hover {
            background: rgba(220, 38, 38, .18);
            border-color: rgba(254, 202, 202, .38);
        }
        
                .ust-bar-menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            padding: 0;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 12px;
            background: rgba(255,255,255,.10);
            color: #fff;
            cursor: pointer;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
        }

        .ust-bar-menu-toggle:hover {
            background: rgba(255,255,255,.16);
            border-color: rgba(255,255,255,.28);
            box-shadow:
                0 10px 24px rgba(15, 23, 42, .14),
                inset 0 1px 0 rgba(255,255,255,.08);
        }

        .ust-bar-menu-toggle-ikon {
            width: 18px;
            height: 14px;
            display: inline-flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .ust-bar-menu-toggle-ikon span {
            display: block;
            width: 100%;
            height: 2px;
            border-radius: 999px;
            background: currentColor;
            transition: transform 0.18s ease, opacity 0.18s ease;
        }

        .ust-bar.menu-open .ust-bar-menu-toggle-ikon span:nth-child(1) {
            transform: translateY(6px) rotate(45deg);
        }

        .ust-bar.menu-open .ust-bar-menu-toggle-ikon span:nth-child(2) {
            opacity: 0;
        }

        .ust-bar.menu-open .ust-bar-menu-toggle-ikon span:nth-child(3) {
            transform: translateY(-6px) rotate(-45deg);
        }

        .sayfa {
            max-width: 1480px;
            margin: 28px auto;
            padding: 0 18px 28px 18px;
        }

        .kutu {
            background: var(--beyaz);
            border: 1px solid rgba(229, 231, 235, 0.85);
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--golge);
            margin-bottom: 22px;
        }

        .kutu h2,
        .kutu h3,
        .kutu h4 {
            color: var(--baslik);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 22px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
        }

        .form-grid-4 {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr 1fr;
            gap: 14px;
        }

        label {
            display: block;
            margin-top: 10px;
            margin-bottom: 7px;
            font-weight: 600;
            font-size: 13px;
            color: var(--baslik);
        }

        input,
        select,
        textarea {
            width: 100%;
            max-width: 100%;
            padding: 11px 12px;
            border: 1px solid var(--kenar);
            border-radius: 12px;
            box-sizing: border-box;
            font: inherit;
            background: #fff;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: rgba(37, 99, 235, 0.65);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
            background: #fff;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button,
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 12px;
            padding: 10px 14px;
            text-decoration: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            line-height: 1.2;
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease, background 0.15s ease;
            max-width: 100%;
            text-align: center;
        }

        button:hover,
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.10);
        }

        .btn-ana { background: var(--ana-renk); color: #fff; }
        .btn-ana:hover { background: var(--ana-renk-koyu); }

        .btn-gri { background: #64748b; color: #fff; }
        .btn-yesil { background: #16a34a; color: #fff; }
        .btn-turuncu { background: #ea580c; color: #fff; }
        .btn-sari { background: #facc15; color: #111827; }
        .btn-kirmizi { background: #dc2626; color: #fff; }

        .hata-kutusu,
        .bilgi-kutusu {
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 16px;
            font-size: 14px;
            border: 1px solid transparent;
        }

        .hata-kutusu {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .bilgi-kutusu {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .arama {
            margin-bottom: 14px;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            min-width: 700px;
        }

        th,
        td {
            padding: 14px 12px;
            border-bottom: 1px solid #eef2f7;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            position: sticky;
            top: 0;
        }

        tbody tr:hover td {
            background: #fafcff;
        }

        .aksiyonlar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .modal-arkaplan {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 999;
        }

        .modal-icerik {
            width: 100%;
            max-width: 1120px;
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            max-height: 92vh;
            overflow: auto;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.22);
        }
		
		body.modal-open {
    overflow: hidden;
}

.app-form-stack {
    display: grid;
    gap: 10px;
}

.app-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
}

.app-modal.is-open {
    display: block;
}

.app-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(4px);
}

.app-modal-dialog {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.app-modal-card {
    width: 100%;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 24px 80px rgba(15, 23, 42, 0.22);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: min(88vh, 860px);
}

.app-modal-size-sm .app-modal-card {
    max-width: 420px;
}

.app-modal-size-md .app-modal-card {
    max-width: 640px;
}

.app-modal-size-lg .app-modal-card {
    max-width: 820px;
}

.app-modal-size-xl .app-modal-card {
    max-width: 1040px;
}

.app-modal-size-full .app-modal-card {
    max-width: 1280px;
}

.app-modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 20px 0 20px;
}

.app-modal-header-text h3 {
    margin: 0;
}

.app-modal-header-text p {
    margin: 6px 0 0 0;
    color: #64748b;
}

.app-modal-close {
    border: 0;
    background: #f1f5f9;
    width: 40px;
    height: 40px;
    border-radius: 999px;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
}

.app-modal-body {
    padding: 20px;
    overflow-y: auto;
    overflow-x: hidden;
    flex: 1 1 auto;
    min-height: 0;
    scrollbar-gutter: stable;
}

.app-modal-body::-webkit-scrollbar {
    width: 10px;
}

.app-modal-body::-webkit-scrollbar-track {
    background: #eef2f7;
    border-radius: 999px;
}

.app-modal-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}

.app-modal-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.app-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 640px) {
    .app-modal-dialog {
        padding: 12px;
        align-items: flex-end;
    }

    .app-modal-card {
        border-radius: 18px 18px 0 0;
        max-height: 92vh;
    }

    .app-modal-footer {
        flex-direction: column-reverse;
    }

    .app-modal-footer .btn,
    .app-modal-footer button {
        width: 100%;
    }
	
	            .ust-bar {
                padding: 14px;
                align-items: center;
                flex-direction: row;
                flex-wrap: wrap;
            }

            .ust-bar-sol {
                flex: 1 1 auto;
                min-width: 0;
            }

            .ust-bar-sag {
                width: 100%;
                display: none;
                grid-template-columns: 1fr;
                gap: 8px;
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid rgba(255,255,255,.14);
            }
            
            .ust-bar.menu-open .ust-bar-sag {
                display: grid;
            }

            .ust-bar-btn {
                width: 100%;
                min-height: 42px;
                padding: 10px 12px;
                font-size: 13px;
            }

            .ust-bar-baslik {
                font-size: 20px;
            }
            
            .ust-bar-menu-toggle {
                display: inline-flex;
                flex: 0 0 auto;
            }
}

        .kalem-grid-fatura {
            display: grid;
            grid-template-columns: 1.2fr 1.4fr .7fr .7fr .7fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .kalem-grid-teklif {
            display: grid;
            grid-template-columns: 1.5fr 1fr .8fr .8fr 1fr .7fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .sayfa-ust {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .sayfa-ust-sol h2,
        .sayfa-ust-sol h3 {
            margin-bottom: 6px;
        }

        .sayfa-aciklama {
            margin: 0;
            color: var(--yazi-soluk);
            font-size: 14px;
            line-height: 1.6;
        }

        .kutu-baslik {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .kutu-baslik h3,
        .kutu-baslik h4 {
            margin: 0;
        }

        .kutu-aciklama {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: var(--yazi-soluk);
        }

        .form-panel {
            background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
        }

        .liste-panel {
            background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
        }

        .istatistik-rozet {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.08);
            color: var(--ana-renk-koyu);
            font-size: 13px;
            font-weight: 700;
            max-width: 100%;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            max-width: 100%;
        }

        .badge-kirmizi {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .badge-yesil {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-mavi {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .badge-gri {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .panel-divider {
            height: 1px;
            background: linear-gradient(90deg, rgba(148,163,184,0.08), rgba(148,163,184,0.35), rgba(148,163,184,0.08));
            margin: 18px 0;
        }

        .veri-vurgu {
            font-weight: 700;
            color: var(--baslik);
        }

        .alt-metin {
            font-size: 12px;
            color: var(--yazi-soluk);
        }

        .form-aciklama-kutu {
            padding: 14px 16px;
            border-radius: 14px;
            background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            border: 1px solid #dbeafe;
            color: #1e40af;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .kalem-blok {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .liste-ozet {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            max-width: 100%;
        }

        .para-vurgu {
            font-weight: 700;
            color: var(--baslik);
            white-space: nowrap;
        }

        .satir-ikincil {
            font-size: 12px;
            color: var(--yazi-soluk);
            margin-top: 4px;
        }

        h1, h2, h3, h4 {
            margin-top: 0;
            margin-bottom: 12px;
            word-break: break-word;
        }

        p {
            color: var(--yazi-soluk);
        }

        @media (max-width: 1200px) {
            .grid-2 {
                grid-template-columns: 320px 1fr;
            }
        }

        @media (max-width: 980px) {
            .sayfa {
                padding: 0 14px 22px 14px;
                margin: 20px auto;
            }

            .grid-2,
            .form-grid-3,
            .form-grid-4 {
                grid-template-columns: 1fr;
            }

            .kutu {
                padding: 18px;
            }

            .kalem-grid-fatura,
            .kalem-grid-teklif {
                grid-template-columns: 1fr;
            }

            .modal-arkaplan {
                align-items: flex-start;
                padding: 12px;
            }

            .modal-icerik {
                max-width: 100%;
                padding: 18px;
                border-radius: 16px;
                margin-top: 6px;
            }

            .istatistik-rozet,
            .liste-ozet {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .sayfa {
                padding: 0 10px 18px 10px;
                margin: 14px auto;
            }

            .kutu {
                padding: 14px;
                margin-bottom: 14px;
                border-radius: 14px;
            }

            label {
                font-size: 12px;
                margin-top: 8px;
                margin-bottom: 6px;
            }

            input,
            select,
            textarea {
                padding: 10px 11px;
                font-size: 14px;
                border-radius: 10px;
            }

            .btn,
            button {
                width: 100%;
                min-height: 42px;
                padding: 11px 12px;
                font-size: 14px;
            }

            .aksiyonlar {
                flex-direction: column;
                align-items: stretch;
            }

            .aksiyonlar .btn {
                width: 100%;
            }

            .sayfa-ust {
                margin-bottom: 14px;
            }

            .sayfa-aciklama,
            .kutu-aciklama,
            .form-aciklama-kutu {
                font-size: 13px;
            }

            .modal-icerik {
                padding: 14px;
                border-radius: 14px;
            }

            th,
            td {
                padding: 12px 10px;
                font-size: 13px;
            }
        }
		.sortable-list {
    display: grid;
    gap: 12px;
}

.sortable-item {
    position: relative;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}

.sortable-item.is-dragging {
    opacity: 0.55;
}

.sortable-item.drag-over-top {
    box-shadow: inset 0 3px 0 var(--ana-renk);
}

.sortable-item.drag-over-bottom {
    box-shadow: inset 0 -3px 0 var(--ana-renk);
}

.drag-handle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    min-width: 42px;
    height: 42px;
    border-radius: 12px;
    border: 1px dashed #cbd5e1;
    background: #f8fafc;
    color: #475569;
    cursor: grab;
    user-select: none;
    font-size: 18px;
    line-height: 1;
}

.drag-handle:active {
    cursor: grabbing;
}

.drag-handle:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}
    </style>

    <link rel="stylesheet" href="<?= e(url('assets/css/mobile.css')) ?>">
    
</head>
<body>
    <div id="appLoader" class="app-loader" aria-hidden="true">
        <div class="app-loader-card">
            <div class="app-loader-logo-wrap">
                <div class="app-loader-ring"></div>
                <img
                    src="<?= e(url('favicon.png')) ?>"
                    alt="Belgeç"
                    class="app-loader-logo"
                >
            </div>
            <p class="app-loader-title">Belgeç</p>
            <p class="app-loader-text">Sayfa hazırlanıyor...</p>
        </div>
    </div>
    <?php require BASE_PATH . '/app/Views/partials/topbar.php'; ?>

       <div class="sayfa">
        <?php require BASE_PATH . '/app/Views/partials/flash.php'; ?>
        <?= $content ?>
    </div>

    <?php require BASE_PATH . '/app/Views/partials/app_footer_scripts.php'; ?>
	<script>
    (function () {
        var loader = document.getElementById('appLoader');

        function showLoader() {
            if (!loader) return;
            loader.classList.remove('is-hidden');
        }

        function hideLoader() {
            if (!loader) return;
            loader.classList.add('is-hidden');
        }

        window.showAppLoader = showLoader;
        window.hideAppLoader = hideLoader;

                document.addEventListener('DOMContentLoaded', function () {
            var topbar = document.getElementById('ustBar');
            var menuToggle = document.getElementById('ustBarMenuToggle');
            var menu = document.getElementById('ustBarMenu');

            function closeTopbarMenu() {
                if (!topbar || !menuToggle) return;
                topbar.classList.remove('menu-open');
                menuToggle.setAttribute('aria-expanded', 'false');
            }

            function toggleTopbarMenu() {
                if (!topbar || !menuToggle) return;

                var isOpen = topbar.classList.toggle('menu-open');
                menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }

            if (menuToggle && topbar && menu) {
                menuToggle.addEventListener('click', function (event) {
                    event.stopPropagation();
                    toggleTopbarMenu();
                });

                document.addEventListener('click', function (event) {
                    if (!topbar.contains(event.target)) {
                        closeTopbarMenu();
                    }
                });

                menu.addEventListener('click', function (event) {
                    var link = event.target.closest('a');
                    if (link) {
                        closeTopbarMenu();
                    }
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth > 640) {
                        closeTopbarMenu();
                    }
                });
            }

            setTimeout(hideLoader, 120);
        });

        window.addEventListener('load', function () {
            hideLoader();
        });

        window.addEventListener('pageshow', function () {
            hideLoader();
        });

        window.addEventListener('beforeunload', function () {
            showLoader();
        });

        document.addEventListener('click', function (event) {
            var link = event.target.closest('a[href]');
            if (!link) return;

            var href = link.getAttribute('href') || '';

            if (
                link.target === '_blank' ||
                link.hasAttribute('download') ||
                href.startsWith('#') ||
                href.startsWith('javascript:') ||
                href.startsWith('mailto:') ||
                href.startsWith('tel:')
            ) {
                return;
            }

            showLoader();
        });

        document.addEventListener('submit', function () {
            showLoader();
        });
    })();
</script>
<?php if (!empty($ayarlar['edm_aktif'])): ?>
<script>
(function () {
    const KONTROL_URL = '<?= e(url('edm-faturalar/kontrol')) ?>';
    const EDM_URL     = '<?= e(url('edm-faturalar')) ?>';
    const INTERVAL_MS = 5 * 60 * 1000;
    const STORAGE_KEY = 'edm_son_bildirim_sayisi';

    let sonVeri = null;

    function badgeGuncelle(sayi) {
        const badge = document.getElementById('edmZilBadge');
        if (!badge) return;
        if (sayi > 0) {
            badge.textContent = sayi > 99 ? '99+' : sayi;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    function dropdownIcerikGuncelle(data) {
        const icerik = document.getElementById('edmDropIcerik');
        if (!icerik) return;

        if (!data || !data.ok) {
            icerik.innerHTML = '<div class="edm-drop-bos">Bağlantı kurulamadı.</div>';
            return;
        }

        const yeniGelen = data.yeni_gelen || 0;
        const yeniGiden = data.yeni_giden || 0;
        const toplam    = yeniGelen + yeniGiden;

        if (toplam === 0) {
            icerik.innerHTML = '<div class="edm-drop-bos">Bildirim yok.</div>';
            return;
        }

        let html = '';
        if (yeniGelen > 0) {
            html += '<a href="' + EDM_URL + '" class="edm-drop-yeni-satir" style="text-decoration:none;cursor:pointer;">'
    + '<span class="edm-drop-nokta"></span>'
    + '<span class="edm-drop-yeni-metin">Yeni gelen fatura</span>'
    + '<span class="edm-drop-yeni-sayi">' + yeniGelen + '</span>'
    + '</a>';
        }
        if (yeniGiden > 0) {
            html += '<a href="' + EDM_URL + '" class="edm-drop-yeni-satir" style="text-decoration:none;cursor:pointer;">'
    + '<span class="edm-drop-nokta"></span>'
    + '<span class="edm-drop-yeni-metin">Yeni giden fatura</span>'
    + '<span class="edm-drop-yeni-sayi">' + yeniGiden + '</span>'
    + '</a>';
        }
        html += '<a href="' + EDM_URL + '" class="edm-drop-git-btn">Faturaları Görüntüle</a>';
        icerik.innerHTML = html;
    }

    function bildirimGoster(sayi) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        const onceki = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
        if (sayi <= onceki) return;
        localStorage.setItem(STORAGE_KEY, sayi);
        new Notification('Belgeç — Yeni Fatura', {
            body: sayi + ' adet okunmamış e-fatura var.',
            icon: '<?= e(url('icons/icon-192.png')) ?>',
        });
    }

    function izinIsteVeBildir(sayi) {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'granted') {
            bildirimGoster(sayi);
        } else if (Notification.permission === 'default') {
            Notification.requestPermission().then(p => { if (p === 'granted') bildirimGoster(sayi); });
        }
    }

    function kontrol() {
        fetch(KONTROL_URL)
            .then(r => r.json())
            .then(data => {
                sonVeri = data;
                const sayi = data.toplam_yeni || 0;
                badgeGuncelle(sayi);
                if (sayi > 0) izinIsteVeBildir(sayi);
                // Dropdown açıksa içeriği de güncelle
                const drop = document.getElementById('edmDropdown');
                if (drop && drop.classList.contains('acik')) dropdownIcerikGuncelle(data);
            })
            .catch(() => {});
    }

    window.edmDropdownToggle = function(e) {
        e.stopPropagation();
        const drop = document.getElementById('edmDropdown');
        if (!drop) return;
        const acikMi = drop.classList.toggle('acik');
        if (acikMi) dropdownIcerikGuncelle(sonVeri);
    };

    document.addEventListener('click', function(e) {
        const wrap = document.querySelector('.edm-zil-wrap');
        if (wrap && !wrap.contains(e.target)) {
            const drop = document.getElementById('edmDropdown');
            if (drop) drop.classList.remove('acik');
        }
    });

    // Sayfa açılışında izin iste (kullanıcı etkileşimi olmadan bazı tarayıcılar bloklar)
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
    kontrol();
    setInterval(kontrol, INTERVAL_MS);

    document.addEventListener('edmGorulduGuncellendi', function () {
        localStorage.setItem(STORAGE_KEY, '0');
        kontrol();
    });
})();
</script>
<?php endif; ?>
<script>
(function () {
    function tryParseFloat(val) {
        var n = parseFloat(String(val).replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function formatTL(val) {
        return val.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
    }

    function hesaplaForm(formId, kdvsizId, kdvId, genelId) {
        var form = document.getElementById(formId);
        if (!form) return;

        var miktarlar   = form.querySelectorAll('input[name="miktar[]"]');
        var fiyatlar    = form.querySelectorAll('input[name="birim_fiyat[]"]');
        var kdvler      = form.querySelectorAll('input[name="kdv_orani[]"]');

        var toplamKdvsiz = 0, toplamKdv = 0;

        for (var i = 0; i < miktarlar.length; i++) {
            var miktar     = tryParseFloat(miktarlar[i]?.value);
            var fiyat      = tryParseFloat(fiyatlar[i]?.value);
            var kdvOrani   = tryParseFloat(kdvler[i]?.value);
            var satirTutar = miktar * fiyat;
            toplamKdvsiz  += satirTutar;
            toplamKdv     += satirTutar * (kdvOrani / 100);
        }

        var el1 = document.getElementById(kdvsizId);
        var el2 = document.getElementById(kdvId);
        var el3 = document.getElementById(genelId);
        if (el1) el1.textContent = formatTL(toplamKdvsiz);
        if (el2) el2.textContent = formatTL(toplamKdv);
        if (el3) el3.textContent = formatTL(toplamKdvsiz + toplamKdv);
    }

    function baglantiyiKur(formId, kdvsizId, kdvId, genelId) {
        var form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('input', function (e) {
            var n = e.target.name;
            if (n === 'miktar[]' || n === 'birim_fiyat[]' || n === 'kdv_orani[]') {
                hesaplaForm(formId, kdvsizId, kdvId, genelId);
            }
        });

        // Dinamik kalem eklendiğinde de güncelle (MutationObserver)
        var observer = new MutationObserver(function () {
            hesaplaForm(formId, kdvsizId, kdvId, genelId);
        });
        var kapsayici = form.querySelector('[data-sort-container]');
        if (kapsayici) observer.observe(kapsayici, { childList: true, subtree: true });

        hesaplaForm(formId, kdvsizId, kdvId, genelId);
    }

    document.addEventListener('DOMContentLoaded', function () {
        baglantiyiKur('createAlisForm',  'alisKdvsizToplam',  'alisKdvTutari',  'alisGenelToplam');
        baglantiyiKur('createSatisForm', 'satisKdvsizToplam', 'satisKdvTutari', 'satisGenelToplam');
        baglantiyiKur('editAlisForm',  'alisEditKdvsizToplam',  'alisEditKdvTutari',  'alisEditGenelToplam');
baglantiyiKur('editSatisForm', 'satisEditKdvsizToplam', 'satisEditKdvTutari', 'satisEditGenelToplam');
    });

    // Modal her açıldığında da yenile
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-modal-open]');
        if (!trigger) return;
        var id = trigger.getAttribute('data-modal-open');
        setTimeout(function () {
            if (id === 'createAlisModal')  hesaplaForm('createAlisForm',  'alisKdvsizToplam',  'alisKdvTutari',  'alisGenelToplam');
            if (id === 'createSatisModal') hesaplaForm('createSatisForm', 'satisKdvsizToplam', 'satisKdvTutari', 'satisGenelToplam');
            if (id === 'editAlisModal')  hesaplaForm('editAlisForm',  'alisEditKdvsizToplam',  'alisEditKdvTutari',  'alisEditGenelToplam');
if (id === 'editSatisModal') hesaplaForm('editSatisForm', 'satisEditKdvsizToplam', 'satisEditKdvTutari', 'satisEditGenelToplam');
        }, 50);
    });
})();
</script>
</body>
</html>