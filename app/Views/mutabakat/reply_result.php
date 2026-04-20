<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mutabakat Cevabı</title>
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
            max-width: 520px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="kutu">
        <h2 style="color:#2ecc71; margin-top:0;">Teşekkürler!</h2>
        <p>
            Mutabakat cevabınız başarıyla kaydedildi ve
            <strong><?= e($ayarlar['sirket_adi'] ?? 'Firma') ?></strong>
            firmasına iletildi.
        </p>
        <p style="color:#777; font-size:14px;">Bu pencereyi güvenle kapatabilirsiniz.</p>
    </div>
</body>
</html>