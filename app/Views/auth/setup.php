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
        .radyo-grup {
            display: flex;
            gap: 10px;
            margin: 8px 0 15px 0;
        }
        .radyo-kart {
            flex: 1;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px;
            cursor: pointer;
            text-align: center;
            transition: all .15s;
        }
        .radyo-kart input { display: none; }
        .radyo-kart:hover { border-color: #2ecc71; }
        .radyo-kart.secili {
            border-color: #2ecc71;
            background: #d4f6dd;
        }
        .radyo-kart strong {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .radyo-kart small {
            color: #666;
            font-size: 11px;
        }
        .mysql-alanlari {
            display: none;
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #2ecc71;
        }
        .mysql-alanlari.acik { display: block; }
        .test-buton {
            padding: 8px 12px;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            margin-bottom: 10px;
            width: auto;
        }
        .test-buton:hover { background: #2980b9; }
        .test-sonuc {
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 10px;
            display: none;
        }
        .test-sonuc.basarili {
            background: #d4f6dd;
            color: #1e7e34;
            display: block;
        }
        .test-sonuc.hata {
            background: #fce4e4;
            color: #c82333;
            display: block;
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

            <label>Tema Rengi:</label>
            <input type="color" name="tema_rengi" value="#3498db">

            <hr>
            <p style="color:#666;font-size:13px;margin:15px 0 10px 0;">
                <strong>Veritabanı Tipi:</strong><br>
                Küçük firmalar için SQLite önerilir (kurulum gerektirmez). Çok kullanıcılı veya büyük veri için MySQL tercih edilebilir.
            </p>

            <div class="radyo-grup">
                <label class="radyo-kart secili" id="radyoSqlite">
                    <input type="radio" name="db_driver" value="sqlite" checked onchange="dbSec(this)">
                    <strong>🗂️ SQLite</strong>
                    <small>Dosya tabanlı, kurulum gerekmez (Önerilen)</small>
                </label>
                <label class="radyo-kart" id="radyoMysql">
                    <input type="radio" name="db_driver" value="mysql" onchange="dbSec(this)">
                    <strong>🗄️ MySQL</strong>
                    <small>Sunucu veritabanı, önceden kurulum gerekir</small>
                </label>
            </div>

            <div class="mysql-alanlari" id="mysqlAlanlari">
                <label>MySQL Sunucu (Host):</label>
                <input type="text" name="db_mysql_host" value="127.0.0.1" placeholder="127.0.0.1 veya localhost">

                <label>Port:</label>
                <input type="text" name="db_mysql_port" value="3306">

                <label>Veritabanı Adı:</label>
                <input type="text" name="db_mysql_database" value="belgec" placeholder="Önceden oluşturulmuş olmalı">

                <label>Kullanıcı Adı:</label>
                <input type="text" name="db_mysql_username" placeholder="MySQL kullanıcı adı">

                <label>Şifre:</label>
                <input type="password" name="db_mysql_password" placeholder="MySQL şifresi">

                <button type="button" class="test-buton" onclick="dbTestEt()">Bağlantıyı Test Et</button>
                <div class="test-sonuc" id="dbTestSonuc"></div>

                <p style="color:#666;font-size:12px;margin-top:10px;">
                    ⚠️ MySQL seçtiyseniz, kuruluma başlamadan önce boş bir veritabanı oluşturmanız gerekir. cPanel'de "MySQL Veritabanları" bölümünden yapılır.
                </p>
            </div>

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

    <script>
    function dbSec(input) {
        document.getElementById('radyoSqlite').classList.remove('secili');
        document.getElementById('radyoMysql').classList.remove('secili');
        if (input.value === 'sqlite') {
            document.getElementById('radyoSqlite').classList.add('secili');
            document.getElementById('mysqlAlanlari').classList.remove('acik');
        } else {
            document.getElementById('radyoMysql').classList.add('secili');
            document.getElementById('mysqlAlanlari').classList.add('acik');
        }
    }

    function dbTestEt() {
        var sonucEl = document.getElementById('dbTestSonuc');
        sonucEl.className = 'test-sonuc';
        sonucEl.textContent = 'Test ediliyor...';
        sonucEl.style.display = 'block';

        var fd = new FormData();
        fd.append('host',     document.querySelector('[name=db_mysql_host]').value);
        fd.append('port',     document.querySelector('[name=db_mysql_port]').value);
        fd.append('database', document.querySelector('[name=db_mysql_database]').value);
        fd.append('username', document.querySelector('[name=db_mysql_username]').value);
        fd.append('password', document.querySelector('[name=db_mysql_password]').value);
        fd.append('_csrf_token', document.querySelector('[name=_csrf_token]').value);

        fetch('<?= e(url('setup/db-test')) ?>', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) {
                    sonucEl.className = 'test-sonuc basarili';
                    sonucEl.textContent = '✓ ' + data.mesaj;
                } else {
                    sonucEl.className = 'test-sonuc hata';
                    sonucEl.textContent = '✗ ' + data.mesaj;
                }
            })
            .catch(function() {
                sonucEl.className = 'test-sonuc hata';
                sonucEl.textContent = '✗ Test isteği başarısız oldu';
            });
    }
    </script>
</body>
</html>
