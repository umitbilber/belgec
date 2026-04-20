<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\EdmGorulduServiceInterface;
use App\Interfaces\EdmServiceInterface;

class EdmController extends BaseController
{
    private EdmServiceInterface $edmService;
private EdmGorulduServiceInterface $gorulduService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    EdmServiceInterface $edmService,
    EdmGorulduServiceInterface $gorulduService
) {
    parent::__construct($settingsService);
    $this->edmService = $edmService;
    $this->gorulduService = $gorulduService;
}

    public function index(Request $request, Response $response): void
{
    $this->guardIzin($response, 'edm.goruntule');

    $bugun     = date('Y-m-d');
    $baslangic = trim((string) $request->query('baslangic', date('Y-m-d', strtotime('-30 days'))));
    $bitis     = trim((string) $request->query('bitis', $bugun));

    // Basit format doğrulama
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $baslangic)) $baslangic = date('Y-m-d', strtotime('-30 days'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bitis))     $bitis     = $bugun;

    try {
        $gelen = $this->edmService->getInvoicesByRange('IN',  $baslangic, $bitis);
        $giden = $this->edmService->getInvoicesByRange('OUT', $baslangic, $bitis);
        $ayarlar = $this->settingsService->all();
        $gorulduler = $this->gorulduService->gorulduler();

        $response->view('edm/index', [
            'pageTitle'        => 'EDM Faturalar',
            'ayarlar' => $ayarlar,
            'gelen'            => $gelen,
            'giden'            => $giden,
            'gorulduler'       => $gorulduler,
            'baslangic'        => $baslangic,
            'bitis'            => $bitis,
            'include_modal_js' => false,
        ], 'layouts.app');
    } catch (\Throwable $e) {
        $response->view('edm/index', [
            'pageTitle'        => 'EDM Faturalar',
            'ayarlar' => $ayarlar,
            'gelen'            => [],
            'giden'            => [],
            'gorulduler'       => [],
            'baslangic'        => $baslangic,
            'bitis'            => $bitis,
            'hata'             => $e->getMessage(),
            'include_modal_js' => false,
        ], 'layouts.app');
    }
}

    public function gorulduIsaretle(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'edm.goruntule');

        $uuid = trim((string) $request->input('uuid', ''));

        if ($uuid === '') {
            $response->json(['ok' => false, 'message' => 'UUID boş.'], 400);
            return;
        }

        $this->gorulduService->gorulduIsaretle($uuid);

// Kontrol cache'ini temizle — sonraki çağrı güncel veri döndürsün
$cacheDosya = BASE_PATH . '/edm_kontrol_cache.json';
if (file_exists($cacheDosya)) {
    @unlink($cacheDosya);
}

$response->json(['ok' => true]);
    }
    public function kontrol(Request $request, Response $response): void
{
    $this->guardIzin($response, 'edm.goruntule');

    $cacheDosya  = BASE_PATH . '/edm_kontrol_cache.json';
    $cacheGecerli = file_exists($cacheDosya) && (time() - filemtime($cacheDosya)) < 300;

    if ($cacheGecerli) {
        $cached = json_decode((string) file_get_contents($cacheDosya), true);
        if (is_array($cached)) {
            $response->json($cached);
            return;
        }
    }

    try {
        $bugun     = date('Y-m-d');
        $baslangic = date('Y-m-d', strtotime('-7 days'));

        $gelen      = $this->edmService->getInvoicesByRange('IN',  $baslangic, $bugun);
        $giden      = $this->edmService->getInvoicesByRange('OUT', $baslangic, $bugun);
        $gorulduler = $this->gorulduService->gorulduler();

        $yeniGelen = count(array_filter($gelen, fn($f) => !in_array($f['uuid'], $gorulduler, true)));
        $yeniGiden = count(array_filter($giden, fn($f) => !in_array($f['uuid'], $gorulduler, true)));

        $sonuc = [
            'ok'          => true,
            'yeni_gelen'  => $yeniGelen,
            'yeni_giden'  => $yeniGiden,
            'toplam_yeni' => $yeniGelen + $yeniGiden,
        ];

        file_put_contents($cacheDosya, json_encode($sonuc), LOCK_EX);
        $response->json($sonuc);
    } catch (\Throwable $e) {
        $response->json(['ok' => false, 'toplam_yeni' => 0]);
    }
}

public function faturaKalemleri(Request $request, Response $response): void
{
    $this->guardIzin($response, 'edm.goruntule');

    $uuid = trim((string) $request->query('uuid', ''));
    $yon  = strtoupper(trim((string) $request->query('yon', 'IN')));

    if ($uuid === '' || !in_array($yon, ['IN', 'OUT'], true)) {
        $response->json(['ok' => false, 'message' => 'Geçersiz parametre.'], 400);
        return;
    }

    try {
        $kalemler = $this->edmService->getFaturaKalemleri($uuid, $yon);
        $response->json(['ok' => true, 'kalemler' => $kalemler]);
    } catch (\Throwable $e) {
        $response->json(['ok' => false, 'message' => $e->getMessage()], 500);
    }
}
public function kontor(Request $request, Response $response): void
{
    $this->guardIzin($response, 'edm.goruntule');

    try {
        $data = $this->edmService->getKontor();
        $response->json(['ok' => true, 'data' => $data]);
    } catch (\Throwable $e) {
        $response->json(['ok' => false, 'message' => $e->getMessage()]);
    }
}
public function goruntule(Request $request, Response $response): void
{
    $this->guardIzin($response, 'edm.goruntule');

    $uuid = trim((string) $request->query('uuid', ''));
    $yon  = strtoupper(trim((string) $request->query('yon', 'IN')));

    if ($uuid === '') {
        http_response_code(400);
        echo 'UUID eksik.';
        exit;
    }

    try {
        $html = $this->edmService->getFaturaIcerik($uuid, $yon);

        header('Content-Type: text/html; charset=UTF-8');
        header('X-Frame-Options: SAMEORIGIN');
        echo $html;
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        echo '<p style="font-family:sans-serif;color:#b91c1c;padding:20px;">Fatura yüklenemedi: ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}
}