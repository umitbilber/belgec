<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Services\GuncellemeService;
use App\Services\GuncellemeUygulamaService;

class GuncellemeController extends BaseController
{
    private GuncellemeService $guncellemeService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        GuncellemeService $guncellemeService
    ) {
        parent::__construct($settingsService);
        $this->guncellemeService = $guncellemeService;
    }

    public function kontrol(Request $request, Response $response): void
    {
        $this->guard($response);

        $force = (string) $request->query('force', '0') === '1';
        $sonuc = $this->guncellemeService->guncellemeVarMi($force);

        $response->json($sonuc);
    }

    public function sistemTestet(Request $request, Response $response): void
    {
        $this->guard($response);

        $tests = [
            'php_version'        => PHP_VERSION,
            'php_binary'         => $this->phpBinaryBul(),
            'curl'               => function_exists('curl_init'),
            'zip_archive'        => class_exists('ZipArchive'),
            'shell_exec'         => $this->isFuncEnabled('shell_exec'),
            'exec'               => $this->isFuncEnabled('exec'),
            'proc_open'          => $this->isFuncEnabled('proc_open'),
            'allow_url_fopen'    => (bool) ini_get('allow_url_fopen'),
            'max_execution_time' => (int) ini_get('max_execution_time'),
            'memory_limit'       => ini_get('memory_limit'),
            'upload_max_filesize'=> ini_get('upload_max_filesize'),
            'writable_base'      => is_writable(BASE_PATH),
            'writable_app'       => is_writable(BASE_PATH . '/app'),
            'writable_storage'   => is_writable(BASE_PATH . '/storage'),
            'tmp_dir'            => sys_get_temp_dir(),
            'tmp_writable'       => is_writable(sys_get_temp_dir()),
            'base_path'          => BASE_PATH,
            'disk_free_mb'       => disk_free_space(BASE_PATH) !== false
                                    ? round(disk_free_space(BASE_PATH) / 1024 / 1024, 2)
                                    : null,
        ];

        $response->json($tests);
    }

    public function uygulaBaslat(Request $request, Response $response): void
    {
        $this->guard($response);
if (($_SESSION['kullanici_rol'] ?? '') !== 'yonetici') {
    $response->json(['ok' => false, 'mesaj' => 'Sadece yonetici guncelleyebilir'], 403);
    return;
}
        $this->guardCsrf($request, $response);

        // Guncelleme bilgilerini al
        $bilgi = $this->guncellemeService->guncellemeVarMi(true);

        if (empty($bilgi['guncelleme_var'])) {
            $response->json(['ok' => false, 'mesaj' => 'Guncelleme yok veya bilgi alinamadi.'], 400);
            return;
        }

        $zipUrl = (string) ($bilgi['indirme_url'] ?? '');
        if ($zipUrl === '') {
            $response->json(['ok' => false, 'mesaj' => 'Indirme URL bulunamadi.'], 400);
            return;
        }

        $hedefSurum = (string) ($bilgi['son_surum'] ?? '');
        $jobId = 'upd_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(4)), 0, 8);

        // Arka plan PHP scriptini baslat
        $phpBinary = $this->phpBinaryBul();
        if ($phpBinary === null) {
            $response->json(['ok' => false, 'mesaj' => 'PHP binary bulunamadi.'], 500);
            return;
        }

        $script = BASE_PATH . '/guncelleme-uygula.php';
        if (!file_exists($script)) {
            $response->json(['ok' => false, 'mesaj' => 'Guncelleme scripti bulunamadi.'], 500);
            return;
        }

        $logDosya = BASE_PATH . '/storage/update_jobs/' . $jobId . '.stderr.log';
        @mkdir(dirname($logDosya), 0755, true);

        // Komut: php guncelleme-uygula.php <job_id> <url> <surum> > stdout.log 2> stderr.log &
$stdoutLog = BASE_PATH . '/storage/update_jobs/' . $jobId . '.stdout.log';
$cmdLog    = BASE_PATH . '/storage/update_jobs/' . $jobId . '.cmd.log';

$cmd = sprintf(
    '%s %s %s %s %s > %s 2> %s &',
    escapeshellarg($phpBinary),
    escapeshellarg($script),
    escapeshellarg($jobId),
    escapeshellarg($zipUrl),
    escapeshellarg($hedefSurum),
    escapeshellarg($stdoutLog),
    escapeshellarg($logDosya)
);

// Komutu ve shell_exec çıktısını logla (debug)
@file_put_contents($cmdLog, "CMD: $cmd\n\nshell_exec çıktısı:\n");
$shellOut = @shell_exec($cmd);
@file_put_contents($cmdLog, (string) $shellOut . "\n", FILE_APPEND);

// Alternatif başlatma yöntemi dene (proc_open)
if (!function_exists('proc_open') || !$this->isFuncEnabled('proc_open')) {
    @file_put_contents($cmdLog, "proc_open yok\n", FILE_APPEND);
}

        // Ilk durum dosyasi olusana kadar kisa bir bekle (max 3 saniye)
        $service = new GuncellemeUygulamaService();
        for ($i = 0; $i < 30; $i++) {
            if ($service->durumOku($jobId) !== null) break;
            usleep(100000); // 100ms
        }

        $response->json([
            'ok'      => true,
            'job_id'  => $jobId,
            'mesaj'   => 'Guncelleme baslatildi',
        ]);
    }

    public function uygulaDurum(Request $request, Response $response): void
    {
        $this->guard($response);
if (($_SESSION['kullanici_rol'] ?? '') !== 'yonetici') {
    $response->json(['ok' => false, 'mesaj' => 'Sadece yonetici'], 403);
    return;
}

        $jobId = trim((string) $request->query('job_id', ''));
        if (!preg_match('/^[A-Za-z0-9_\-]+$/', $jobId)) {
            $response->json(['ok' => false, 'mesaj' => 'Gecersiz job_id'], 400);
            return;
        }

        $service = new GuncellemeUygulamaService();
        $durum   = $service->durumOku($jobId);

        if ($durum === null) {
            $response->json(['ok' => false, 'mesaj' => 'Job bulunamadi'], 404);
            return;
        }

        $response->json(['ok' => true, 'durum' => $durum]);
    }

    private function isFuncEnabled(string $func): bool
    {
        if (!function_exists($func)) return false;
        $disabled = explode(',', (string) ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);
        return !in_array($func, $disabled, true);
    }

    private function phpBinaryBul(): ?string
    {
        // 1. PHP_BINARY sabit var mi
        if (defined('PHP_BINARY') && PHP_BINARY !== '' && is_executable(PHP_BINARY)) {
            return PHP_BINARY;
        }

        // 2. Sık konumlar
        $adaylar = [
            '/usr/bin/php',
            '/usr/local/bin/php',
            '/opt/cpanel/ea-php83/root/usr/bin/php',
            '/opt/cpanel/ea-php82/root/usr/bin/php',
            '/opt/cpanel/ea-php81/root/usr/bin/php',
        ];
        foreach ($adaylar as $yol) {
            if (is_executable($yol)) return $yol;
        }

        return null;
    }
}
