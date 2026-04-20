<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $kod ?> — Belgeç</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: radial-gradient(circle at top left, rgba(37,99,235,.06), transparent 30%),
                        radial-gradient(circle at bottom right, rgba(15,23,42,.04), transparent 25%), #f3f6fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .kart {
            background: #fff;
            border: 1px solid #e6edf5;
            border-radius: 24px;
            box-shadow: 0 24px 64px rgba(15,23,42,.10);
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .kod {
            font-size: 80px;
            font-weight: 900;
            color: #e2e8f0;
            line-height: 1;
            margin-bottom: 16px;
            letter-spacing: -4px;
        }
        .baslik {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 10px;
        }
        .mesaj {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .butonlar {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: transform .15s, box-shadow .15s;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(15,23,42,.10); }
        .btn-ana { background: #2563eb; color: #fff; }
        .btn-gri { background: #f1f5f9; color: #334155; }
        @media(max-width:480px) {
            .kart { padding: 36px 24px; }
            .kod { font-size: 60px; }
            .butonlar { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="kart">
        <div class="kod"><?= (int) $kod ?></div>
        <div class="baslik"><?= e($baslik) ?></div>
        <div class="mesaj"><?= e($mesaj) ?></div>
        <div class="butonlar">
            <a href="<?= e(url('anasayfa')) ?>" class="btn btn-ana">Ana Sayfaya Dön</a>
            <button onclick="history.back()" class="btn btn-gri">Geri Git</button>
        </div>
    </div>
</body>
</html>