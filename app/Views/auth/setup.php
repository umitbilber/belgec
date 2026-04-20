<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kurulumu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .kurulum-kutusu {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="color"],
        textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        button:hover {
            background: #27ae60;
        }

        h2 {
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="kurulum-kutusu">
        <h2>Müşteri Kurulum Sihirbazı</h2>

        <form method="POST" action="<?= e(url('setup')) ?>">
            <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
            <label>Şirket Adı:</label>
            <input type="text" name="sirket_adi" required>

            <label>Logo URL:</label>
            <input type="text" name="logo_url" required>

            <label>E-Posta:</label>
            <input type="email" name="eposta" required>

            <label>Telefon:</label>
            <input type="text" name="telefon" required>

            <label>Adres:</label>
            <textarea name="adres" required></textarea>
            
            <label>Vergi Kimlik No:</label>
            <input type="text" name="vergi_no">

            <label>Vergi Dairesi:</label>
            <input type="text" name="vergi_dairesi">

            <label>İnternet Sitesi:</label>
            <input type="text" name="web_sitesi" required>

            <label>Tema Rengi:</label>
            <input type="color" name="tema_rengi" value="#3498db">

            <hr>
            <p style="color:#666;font-size:13px;margin:15px 0 10px 0;">
                <strong>SMTP Ayarları (isteğe bağlı):</strong><br>
                Mutabakat e-postası göndermek istersen doldur, istemiyorsan boş bırak.
            </p>

            <label>SMTP Sunucusu (Host):</label>
            <input type="text" name="smtp_host" placeholder="Örn: mail.firmaniz.com">

            <label>SMTP E-Posta Adresi:</label>
            <input type="text" name="smtp_mail" placeholder="Örn: bilgi@firmaniz.com">

            <label>SMTP Şifresi:</label>
            <input type="password" name="smtp_sifre" placeholder="E-posta hesabınızın şifresi">

            <hr>

            <label>Yöneticinin Adı Soyadı:</label>
            <input type="text" name="ad" required placeholder="Örn: Ahmet Yılmaz">

            <label>Kullanıcı Adı:</label>
            <input type="text" name="kullanici_adi" required placeholder="Örn: ahmet" pattern="[a-zA-Z0-9_]+" title="Sadece harf, rakam ve alt çizgi">

            <label>Yönetici Şifresi:</label>
            <input type="password" name="sifre" required minlength="6">

            <label>Şifre Tekrar:</label>
            <input type="password" name="sifre_tekrar" required minlength="6">

            <button type="submit">Kurulumu Tamamla ve Kaydet</button>
        </form>
    </div>
</body>
</html>