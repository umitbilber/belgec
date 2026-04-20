<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mutabakat Red Nedeni</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .kutu {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 460px;
            width: 100%;
        }

        textarea {
            width: 100%;
            height: 120px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 15px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            resize: vertical;
        }

        button {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="kutu">
        <h2 style="color:#e74c3c; margin-top:0;">Mutabık Değilim</h2>
        <p>
            <strong><?= e($cari['ad_soyad'] ?? '') ?></strong> için bakiyeyle neden mutabık olmadığınızı lütfen kısaca belirtin.
        </p>

        <form method="POST">
            <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
            <textarea name="aciklama" placeholder="Örn: 15.10.2023 tarihli havalem hesaba yansımamış..." required></textarea>
            <button type="submit">Cevabımı Firmaya İlet</button>
        </form>
    </div>
</body>
</html>