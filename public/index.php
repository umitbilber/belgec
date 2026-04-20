<?php

declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

define('BASE_PATH', dirname(__DIR__));

$appConfig = require BASE_PATH . '/config/app.php';
$debug = (bool) ($appConfig['debug'] ?? false);

ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : 0);

if (isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true) {
    $_ayarlarDosya = BASE_PATH . '/ayarlar.json';
    if (file_exists($_ayarlarDosya)) {
        $_ayarlar  = json_decode((string) file_get_contents($_ayarlarDosya), true) ?? [];
        $_siklık   = (string) ($_ayarlar['yedek_sikligi'] ?? 'haftalik');
        $_maxAdet  = (int) ($_ayarlar['yedek_max_adet'] ?? 5);
        $_sonZaman = (string) ($_ayarlar['son_otomatik_yedek'] ?? '');

        if ($_siklık !== 'kapali') {
            require_once BASE_PATH . '/app/Services/YedekService.php';
            $_yedekSrv = new \App\Services\YedekService();

            if ($_yedekSrv->zamanKontrol($_siklık, $_sonZaman)) {
                $_yedekSrv->otomatikYedekAl($_maxAdet);
                $_ayarlar['son_otomatik_yedek'] = date('Y-m-d H:i:s');
                file_put_contents($_ayarlarDosya, json_encode($_ayarlar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
            }
        }
    }
}

require BASE_PATH . '/app/bootstrap.php';

$app = new \App\Core\App();
$app->run();