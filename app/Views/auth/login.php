<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Belgeç</title>

    <link rel="icon" type="image/png" href="<?= e(url('favicon.png')) ?>">
<link rel="shortcut icon" href="<?= e(url('favicon.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(url('icons/icon-192.png')) ?>">
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
            --yazi-soluk: #64748b;
            --baslik: #0f172a;
            --kenar: #e2e8f0;
            --beyaz: #ffffff;
            --tehlike: #dc2626;
            --golge: 0 30px 80px rgba(15, 23, 42, 0.18);
            --radius: 24px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--yazi);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 28%),
                radial-gradient(circle at bottom right, rgba(15, 23, 42, 0.10), transparent 24%),
                linear-gradient(135deg, #eef4ff 0%, #f8fbff 45%, #eef2ff 100%);
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.1fr .9fr;
        }

        .auth-brand {
            position: relative;
            padding: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background:
                linear-gradient(135deg, <?= e($ayarlar['tema_rengi'] ?? '#2563eb') ?> 0%, #1e3a8a 100%);
            color: #fff;
        }

        .auth-brand::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,.18), transparent 22%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,.10), transparent 18%);
            pointer-events: none;
        }

        .auth-brand-content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 560px;
        }

        .auth-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.18);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 22px;
        }

        .auth-brand-logo {
    width: 120px;
    height: 120px;
    object-fit: contain;
    display: block;
    margin-bottom: 24px;
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 0;
    box-shadow: none;
}

        .auth-brand-title {
            margin: 0 0 14px 0;
            font-size: 42px;
            line-height: 1.12;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .auth-brand-text {
            margin: 0;
            font-size: 16px;
            line-height: 1.8;
            color: rgba(255,255,255,.88);
            max-width: 520px;
        }

        .auth-brand-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 28px;
        }

        .auth-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.14);
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,.94);
        }

        .auth-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px 20px;
        }

        .auth-card {
            width: 100%;
            max-width: 460px;
            background: rgba(255,255,255,.90);
            border: 1px solid rgba(226,232,240,.9);
            border-radius: var(--radius);
            box-shadow: var(--golge);
            backdrop-filter: blur(14px);
            overflow: hidden;
        }

        .auth-card-inner {
            padding: 34px;
        }

        .auth-mobile-logo {
    display: none;
    width: 96px;
    height: 96px;
    object-fit: contain;
    margin: 0 auto 18px auto;
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 0;
    box-shadow: none;
}

        .auth-title {
            margin: 0;
            font-size: 30px;
            line-height: 1.15;
            font-weight: 800;
            color: var(--baslik);
            letter-spacing: -.02em;
        }

        .auth-subtitle {
            margin: 10px 0 0 0;
            color: var(--yazi-soluk);
            font-size: 14px;
            line-height: 1.7;
        }

        .auth-error {
            margin-top: 22px;
            padding: 14px 16px;
            border-radius: 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 14px;
            line-height: 1.6;
        }

        .auth-form {
            margin-top: 24px;
        }

        .auth-field {
            margin-bottom: 16px;
        }

        .auth-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--baslik);
        }

        .auth-input-wrap {
            position: relative;
        }

        .auth-input {
            width: 100%;
            height: 52px;
            border: 1px solid var(--kenar);
            border-radius: 16px;
            padding: 0 16px;
            font: inherit;
            font-size: 15px;
            color: var(--yazi);
            background: #fff;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .auth-input.auth-input-password {
            padding-right: 52px;
        }

        .auth-input:focus {
            outline: none;
            border-color: rgba(37, 99, 235, 0.55);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
            background: #fff;
        }

        .auth-password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 10px;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .15s ease, transform .15s ease;
        }

        .auth-password-toggle:hover {
            background: #eef2f7;
            transform: translateY(-50%) scale(1.03);
        }

        .auth-password-toggle svg {
            width: 18px;
            height: 18px;
            display: block;
        }

        .auth-help {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 6px 0 20px 0;
            flex-wrap: wrap;
        }

        .auth-help-text {
            font-size: 12px;
            color: var(--yazi-soluk);
            line-height: 1.6;
        }

        .auth-submit {
            width: 100%;
            min-height: 52px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--ana-renk) 0%, var(--ana-renk-koyu) 100%);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 18px 36px rgba(37, 99, 235, 0.20);
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }

        .auth-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 42px rgba(37, 99, 235, 0.24);
        }

        .auth-submit:active {
            transform: translateY(0);
        }

        .auth-footer {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #eef2f7;
            text-align: center;
            font-size: 12px;
            color: var(--yazi-soluk);
            line-height: 1.7;
        }

        .auth-mini {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 1040px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }

            .auth-brand {
                display: none;
            }

            .auth-panel {
                min-height: 100vh;
                padding: 24px 16px;
            }

            .auth-mobile-logo {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .auth-card-inner {
                padding: 24px 18px;
            }

            .auth-title {
                font-size: 26px;
            }

            .auth-subtitle {
                font-size: 13px;
            }

            .auth-input,
            .auth-submit {
                min-height: 50px;
                height: 50px;
                font-size: 14px;
            }

            .auth-help {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <section class="auth-brand">
            <div class="auth-brand-content">
                <div class="auth-badge">Belgeç yönetim paneli</div>

                <img src="<?= e(url('favicon.png')) ?>" alt="Belgeç Logo" class="auth-brand-logo">

                <h1 class="auth-brand-title">Belgeç</h1>

                <p class="auth-brand-text">
    Cari, stok, teklif ve fatura süreçlerini tek merkezden yönetmenizi sağlayan
    modern ve düzenli bir çalışma alanı.
</p>

                <div class="auth-brand-pills">
                    <div class="auth-pill">Cari takibi</div>
                    <div class="auth-pill">Stok yönetimi</div>
                    <div class="auth-pill">Teklif ve fatura</div>
                    <div class="auth-pill">Kurumsal panel</div>
                </div>
            </div>
        </section>

        <section class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-inner">
                    <img src="<?= e(url('favicon.png')) ?>" alt="Belgeç Logo" class="auth-mobile-logo">

                    <h2 class="auth-title">Yönetici girişi</h2>
                    <p class="auth-subtitle">
                        Panele erişmek için şifrenizi girin.
                    </p>

                    <?php if (!empty($hata_mesaji)): ?>
                        <div class="auth-error"><?= e($hata_mesaji) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= e(url('login')) ?>" class="auth-form" novalidate>
                        <?php include BASE_PATH . '/app/Views/partials/csrf.php'; ?>
                        <div class="auth-form-group">
    <label class="auth-label">Kullanıcı Adı</label>
    <div class="auth-input-wrap">
        <input type="text" name="kullanici_adi" class="auth-input" placeholder="Kullanıcı adınız" autocomplete="username">
    </div>
    <p class="auth-input-hint">Yönetici girişi için boş bırakın.</p>
</div>
                        <div class="auth-field">
                            <label for="giris_sifre" class="auth-label">Şifre</label>
                            <div class="auth-input-wrap">
                                <input
                                    id="giris_sifre"
                                    type="password"
                                    name="giris_sifre"
                                    class="auth-input auth-input-password"
                                    placeholder="Şifrenizi girin"
                                    autocomplete="current-password"
                                    required
                                >
                                <button type="button" class="auth-password-toggle" id="togglePassword" aria-label="Şifreyi göster veya gizle">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2 12C3.8 8.5 7.4 6 12 6C16.6 6 20.2 8.5 22 12C20.2 15.5 16.6 18 12 18C7.4 18 3.8 15.5 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="auth-help">
                            <div class="auth-help-text">
                                Güvenli oturum doğrulaması ile yönetim paneline erişiyorsunuz.
                            </div>
                        </div>

                        <button type="submit" class="auth-submit">Giriş yap</button>
                    </form>

                    <div class="auth-footer">
                        Belgeç paneline güvenli erişim
                        <div class="auth-mini">Belgeç</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        (function () {
            var passwordInput = document.getElementById('giris_sifre');
            var toggleButton = document.getElementById('togglePassword');

            if (!passwordInput || !toggleButton) {
                return;
            }

            toggleButton.addEventListener('click', function () {
                var isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            });
        })();
    </script>
</body>
</html>