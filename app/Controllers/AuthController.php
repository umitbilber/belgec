<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use Throwable;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\KullaniciServiceInterface;
use App\Core\Migrator;

class AuthController extends BaseController
{

    private KullaniciServiceInterface $kullaniciService;

public function __construct(
    SettingsServiceInterface $settingsService,
    KullaniciServiceInterface $kullaniciService
) {
    parent::__construct($settingsService);
    $this->kullaniciService = $kullaniciService;
}

    public function index(Request $request, Response $response): void
    {
        $this->redirectToSetupIfNeeded($response);

        if (($request->query('cikis') ?? '') === '1') {
            $this->logout($request, $response);
        }

        if ($this->isLoggedIn()) {
            $response->redirect(url('anasayfa'));
        }

        $response->view('auth.login', [
            'ayarlar' => $this->settingsService->all(),
            'hata_mesaji' => '',
        ]);
    }

    public function login(Request $request, Response $response): void
{
    $this->redirectToSetupIfNeeded($response);

    $this->guardCsrf($request, $response);

    $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cacheKey  = sys_get_temp_dir() . '/belgec_login_' . md5($ip) . '.json';
    $simdi     = time();
    $maxDeneme = 5;
    $kilitSure = 15 * 60; // 15 dakika

    // Mevcut kayıt oku
    $kayit = ['denemeler' => 0, 'son_deneme' => 0, 'kilitli_kadar' => 0];
    if (file_exists($cacheKey)) {
        $okunan = json_decode((string) file_get_contents($cacheKey), true);
        if (is_array($okunan)) $kayit = $okunan;
    }

    // Kilit kontrolü
    if ($kayit['kilitli_kadar'] > $simdi) {
        $kalanDakika = (int) ceil(($kayit['kilitli_kadar'] - $simdi) / 60);
        $response->view('auth.login', [
            'ayarlar'     => $this->settingsService->all(),
            'hata_mesaji' => 'Çok fazla hatalı giriş. ' . $kalanDakika . ' dakika bekleyin.',
        ]);
        return;
    }

    $settings = $this->settingsService->all();
    $password = trim((string) $request->input('giris_sifre', ''));

    // Önce kullanıcı tablosunda ara
$kullaniciAdi = trim((string) $request->input('kullanici_adi', ''));
$kullanici = $kullaniciAdi !== ''
    ? $this->kullaniciService->girisDogrula($kullaniciAdi, $password)
    : null;

if ($kullanici) {
    @unlink($cacheKey);
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['giris_yapildi']      = true;
    $_SESSION['kullanici_id']       = (int) $kullanici['id'];
    $_SESSION['kullanici_adi']      = $kullanici['kullanici_adi'];
    $_SESSION['kullanici_ad']       = $kullanici['ad'];
    $_SESSION['kullanici_rol']      = $kullanici['rol'];
    $_SESSION['kullanici_izinler']  = $this->kullaniciService->getIzinler((int) $kullanici['id']);
    $response->redirect(url('anasayfa'));
    return;
}

// Sonra yönetici şifresiyle dene (geriye dönük uyumluluk)
if ($this->settingsService->verifyAdminPassword($password)) {
    @unlink($cacheKey);
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['giris_yapildi']     = true;
    $_SESSION['kullanici_id']      = 0;
    $_SESSION['kullanici_adi']     = 'yonetici';
    $_SESSION['kullanici_ad']      = 'Yönetici';
    $_SESSION['kullanici_rol']     = 'yonetici';
    $_SESSION['kullanici_izinler'] = [];
    $response->redirect(url('anasayfa'));
    return;
}

    // Hatalı giriş — sayacı artır
    $kayit['denemeler']++;
    $kayit['son_deneme'] = $simdi;
    if ($kayit['denemeler'] >= $maxDeneme) {
        $kayit['kilitli_kadar'] = $simdi + $kilitSure;
    }
    file_put_contents($cacheKey, json_encode($kayit), LOCK_EX);

    $kalanHak = max(0, $maxDeneme - $kayit['denemeler']);
    $hata = $kalanHak > 0
        ? 'Hatalı şifre. ' . $kalanHak . ' deneme hakkınız kaldı.'
        : 'Çok fazla hatalı giriş. 15 dakika bekleyin.';

    $response->view('auth.login', [
        'ayarlar'     => $settings,
        'hata_mesaji' => $hata,
    ]);
}

    public function showSetup(Request $request, Response $response): void
    {
        if ($this->settingsService->isInstalled()) {
            $response->redirect(url(''));
        }

        $response->view('auth.setup');
    }

    public function setup(Request $request, Response $response): void
    {
        $this->guardCsrf($request, $response);
        
        if ($this->settingsService->isInstalled()) {
            $response->redirect(url(''));
        }

        try {
            $input = $request->input();
            $sifre = trim((string) ($input['sifre'] ?? ''));
            $sifreTekrar = trim((string) ($input['sifre_tekrar'] ?? ''));

            if ($sifre !== $sifreTekrar) {
                $response->abort(400, 'Şifreler uyuşmuyor. Lütfen tekrar deneyin.');
                return;
            }

            // Migration'ları otomatik çalıştır (müşteri kurulumunda kolaylık)
            try {
                $migrator = new \App\Core\Migrator();
                $migrator->run();
            } catch (\Throwable $e) {
                $response->abort(500, 'Veritabanı kurulumu başarısız: ' . $e->getMessage());
                return;
            }
            
            // İlk yönetici kullanıcısını kullanicilar tablosuna ekle
            try {
                $this->kullaniciService->create([
                    'ad'            => trim((string) ($input['ad'] ?? 'Yönetici')),
                    'kullanici_adi' => trim((string) ($input['kullanici_adi'] ?? 'admin')),
                    'sifre'         => $sifre,
                    'rol'           => 'yonetici',
                ]);
            } catch (\Throwable $e) {
                $response->abort(500, 'Yönetici kullanıcı oluşturulamadı: ' . $e->getMessage());
                return;
            }

            $this->settingsService->install($input);
            $response->redirect(url(''));
        } catch (Throwable $e) {
            $response->abort(500, 'Kurulum sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function logout(Request $request, Response $response): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                (bool) ($params['secure'] ?? false),
                (bool) ($params['httponly'] ?? true)
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $response->redirect(url(''));
    }

    private function redirectToSetupIfNeeded(Response $response): void
    {
        if (!$this->settingsService->isInstalled()) {
            $response->redirect(url('setup'));
        }
    }

    private function isLoggedIn(): bool
    {
        return !empty($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true;
    }
}