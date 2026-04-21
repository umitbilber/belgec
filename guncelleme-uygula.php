<?php

declare(strict_types=1);

/**
 * Arka plan guncelleme calistiricisi.
 * Kullanim: php guncelleme-uygula.php <job_id> <zip_url> <hedef_surum>
 *
 * Bu script web istegi tarafindan shell_exec ile tetiklenir ve
 * GuncellemeUygulamaService'i calistirir. Web istegi anlik donus yapar,
 * bu script arka planda dosyalari gunceller.
 */

// Sadece CLI'dan calistirilabilir - guvenlik
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Bu script sadece komut satirindan calistirilabilir.');
}

// Parametreler
$jobId      = $argv[1] ?? '';
$zipUrl     = $argv[2] ?? '';
$hedefSurum = $argv[3] ?? '';

if ($jobId === '' || $zipUrl === '' || $hedefSurum === '') {
    fwrite(STDERR, "Eksik parametre. Kullanim: php guncelleme-uygula.php <job_id> <zip_url> <hedef_surum>\n");
    exit(1);
}

// Job ID guvenlik - sadece alfanumerik, tire, alt cizgi
if (!preg_match('/^[A-Za-z0-9_\-]+$/', $jobId)) {
    fwrite(STDERR, "Gecersiz job ID\n");
    exit(1);
}

// URL guvenlik - sadece github release URL'leri
if (!preg_match('#^https://(github\.com|api\.github\.com|objects\.githubusercontent\.com)/#', $zipUrl)) {
    fwrite(STDERR, "Gecersiz zip URL - sadece GitHub URL'leri kabul edilir\n");
    exit(1);
}

// Zaman siniri kaldir (uzun surebilir)
@set_time_limit(0);
@ini_set('memory_limit', '256M');

define('BASE_PATH', __DIR__);

require BASE_PATH . '/app/bootstrap.php';

$service = new \App\Services\GuncellemeUygulamaService();
$service->calistir($jobId, $zipUrl, $hedefSurum);

exit(0);
