<?php

declare(strict_types=1);

use App\Controllers\AlisFaturasiController;
use App\Controllers\AuthController;
use App\Controllers\AyarController;
use App\Controllers\CariController;
use App\Controllers\DashboardController;
use App\Controllers\MutabakatController;
use App\Controllers\SatisFaturasiController;
use App\Controllers\StokController;
use App\Controllers\TeklifController;
use App\Controllers\PwaController;
use App\Controllers\TeklifPrintController;
use App\Controllers\CariPrintController;
use App\Controllers\CariYaslandirmaController;
use App\Controllers\CariHareketController;
use App\Controllers\CariAktarimController;
use App\Controllers\VeriAktarimController;
use App\Controllers\EdmController;
use App\Controllers\AnaSayfaController;
use App\Controllers\StokHareketController;
use App\Controllers\KullaniciController;
use App\Controllers\RaporController;
use App\Controllers\AuditLogController;

/** @var \App\Core\Router $router */

$router->get('/', [AuthController::class, 'index']);
$router->get('/setup', [AuthController::class, 'showSetup']);
$router->post('/setup', [AuthController::class, 'setup']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/veri-aktarim', [\App\Controllers\VeriAktarimController::class, 'index']);

$router->get('/pwa/manifest.json', [PwaController::class, 'manifest']);
$router->get('/pwa/sw.js', [PwaController::class, 'serviceWorker']);

$router->get('/anasayfa', [AnaSayfaController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/cariler', [CariController::class, 'index']);
$router->post('/cariler/ekle', [CariController::class, 'store']);
$router->post('/cariler/guncelle', [CariController::class, 'update']);
$router->post('/cariler/hareket', [CariController::class, 'movement']);
$router->post('/cariler/sil', [CariController::class, 'delete']);
$router->get('/cariler/yazdir', [CariPrintController::class, 'show']);
$router->get('/cari-yaslandirma', [CariYaslandirmaController::class, 'index']);
$router->post('/cari-yaslandirma/hatirlatma-gonder', [CariYaslandirmaController::class, 'sendHatirlatma']);
$router->get('/cari-hareketler', [CariHareketController::class, 'index']);
$router->post('/cari-hareketler/guncelle', [CariHareketController::class, 'update']);
$router->post('/cari-hareketler/sil', [CariHareketController::class, 'delete']);
$router->post('/cariler/wolvox/onizleme', [CariAktarimController::class, 'preview']);
$router->post('/cariler/wolvox/aktar', [CariAktarimController::class, 'execute']);
$router->post('/cariler/wolvox/toplu-aktar', [CariAktarimController::class, 'executeAll']);
$router->get('/cariler/wolvox/kuyruk-sil', [CariAktarimController::class, 'removeQueueItem']);

$router->get('/stoklar', [StokController::class, 'index']);
$router->post('/stoklar/ekle', [StokController::class, 'store']);
$router->post('/stoklar/guncelle', [StokController::class, 'update']);
$router->post('/stoklar/sil', [StokController::class, 'delete']);
$router->get('/stok-hareketleri', [StokHareketController::class, 'index']);
$router->get('/stoklar/fiyat-gecmisi', [StokController::class, 'fiyatGecmisi']);

$router->get('/alis-faturalari', [AlisFaturasiController::class, 'index']);
$router->post('/alis-faturalari/ekle', [AlisFaturasiController::class, 'store']);
$router->post('/alis-faturalari/guncelle', [AlisFaturasiController::class, 'update']);
$router->post('/alis-faturalari/sil', [AlisFaturasiController::class, 'delete']);

$router->get('/satis-faturalari', [SatisFaturasiController::class, 'index']);
$router->post('/satis-faturalari/ekle', [SatisFaturasiController::class, 'store']);
$router->post('/satis-faturalari/guncelle', [SatisFaturasiController::class, 'update']);
$router->post('/satis-faturalari/sil', [SatisFaturasiController::class, 'delete']);

$router->get('/teklifler', [TeklifController::class, 'index']);
$router->post('/teklifler/ekle', [TeklifController::class, 'store']);
$router->post('/teklifler/guncelle', [TeklifController::class, 'update']);
$router->post('/teklifler/sil', [TeklifController::class, 'delete']);
$router->get('/teklifler/yazdir', [TeklifPrintController::class, 'show']);

$router->get('/ayarlar', [AyarController::class, 'index']);
$router->post('/ayarlar/guncelle', [AyarController::class, 'update']);
$router->post('/ayarlar/favori-ekle', [AyarController::class, 'addFavorite']);
$router->post('/ayarlar/favori-kaldir', [AyarController::class, 'removeFavorite']);
$router->get('/ayarlar/edm-test', [AyarController::class, 'testEdm']);
$router->get('/ayarlar/edm-onizleme', [AyarController::class, 'previewEdm']);
$router->get('/ayarlar/kur-bilgisi', [AyarController::class, 'kurBilgisi']);
$router->post('/ayarlar/yedek-al', [AyarController::class, 'yedekAl']);
$router->get('/ayarlar/yedek-indir', [AyarController::class, 'yedekIndir']);
$router->get('/ayarlar/yedek-listesi', [AyarController::class, 'yedekListesi']);
$router->get('/ayarlar/yedek-bildirim', [AyarController::class, 'yedekBildirim']);
$router->post('/ayarlar/yedek-bildirim-oku', [AyarController::class, 'yedekBildirimOku']);

$router->get('/mutabakat', [MutabakatController::class, 'form']);
$router->post('/mutabakat/gonder', [MutabakatController::class, 'send']);
$router->get('/mutabakat/cevap', [MutabakatController::class, 'reply']);
$router->post('/mutabakat/cevap', [MutabakatController::class, 'reply']);
$router->post('/mutabakat/onizle', [MutabakatController::class, 'onizle']);

$router->get('/edm-faturalar', [EdmController::class, 'index']);
$router->post('/edm-faturalar/goruldu', [EdmController::class, 'gorulduIsaretle']);
$router->get('/edm-faturalar/kontrol', [EdmController::class, 'kontrol']);
$router->get('/edm-faturalar/goruntule', [EdmController::class, 'goruntule']);
$router->get('/edm-faturalar/kalemler', [EdmController::class, 'faturaKalemleri']);
$router->get('/edm-faturalar/kontor', [EdmController::class, 'kontor']);

$router->get('/kullanicilar', [KullaniciController::class, 'index']);
$router->post('/kullanicilar/ekle', [KullaniciController::class, 'store']);
$router->post('/kullanicilar/guncelle', [KullaniciController::class, 'update']);
$router->post('/kullanicilar/sil', [KullaniciController::class, 'delete']);
$router->get('/kullanicilar/izinler', [KullaniciController::class, 'getIzinler']);

$router->get('/raporlar', [RaporController::class, 'index']);

$router->get('/audit-log', [AuditLogController::class, 'index']);

$router->get('/guncelleme/kontrol', [\App\Controllers\GuncellemeController::class, 'kontrol']);