<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use Throwable;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\DashboardServiceInterface;
use App\Interfaces\EdmServiceInterface;
use App\Services\YedekService;

class AyarController extends BaseController
{
    private DashboardServiceInterface $dashboardService;
private EdmServiceInterface $edmService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    DashboardServiceInterface $dashboardService,
    EdmServiceInterface $edmService
) {
    parent::__construct($settingsService);
    $this->dashboardService = $dashboardService;
    $this->edmService = $edmService;
}

    public function index(Request $request, Response $response): void
    {
        $this->guard($response);

        $bilgi = (string) $request->query('bilgi', '');
        $hata = (string) $request->query('hata', '');

        $ayarlar = $this->settingsService->all();
        $favoriler = $ayarlar['favoriler'] ?? [];

        $response->view('ayar.index', [
            'pageTitle' => 'Ayarlar',
            'ayarlar' => $ayarlar,
            'favoriler' => $favoriler,
            'tum_moduller' => $this->dashboardService->getModuleLabels(),
            'hata_mesaji' => $hata,
            'bilgi_mesaji' => $bilgi,
        ], 'layouts.app');
    }

    public function update(Request $request, Response $response): void
    {
        $this->guard($response);
        
        $this->guardCsrf($request, $response);

        try {
            $this->auditLog('duzenle', 'ayarlar');
            $this->settingsService->updateGeneralSettings($request->input());
            $response->redirect(url('ayarlar?bilgi=' . urlencode('Ayarlar başarıyla güncellendi.')));
        } catch (Throwable $e) {
            $response->redirect(url('ayarlar?hata=' . urlencode($e->getMessage())));
        }
    }

    public function addFavorite(Request $request, Response $response): void
{
    $this->guard($response);
    $this->guardCsrf($request, $response);

    $modul = trim((string) $request->input('modul', ''));

        if ($modul !== '') {
            $this->settingsService->addFavorite($modul);
        }

        $response->redirect(url('ayarlar?bilgi=' . urlencode('Favori modül eklendi.')));
    }

   public function removeFavorite(Request $request, Response $response): void
{
    $this->guard($response);
    $this->guardCsrf($request, $response);

    $modul = trim((string) $request->input('modul', ''));

        if ($modul !== '') {
            $this->settingsService->removeFavorite($modul);
        }

        $response->redirect(url('ayarlar?bilgi=' . urlencode('Favori modül kaldırıldı.')));
    }
    public function testEdm(Request $request, Response $response): void
{
    $this->guard($response);

    try {
        $result = $this->edmService->testConnection();
        $counterText = $this->summarizeCounter($result['counter'] ?? []);

        $message = 'EDM bağlantısı başarılı.';
        if ($counterText !== '') {
            $message .= ' ' . $counterText;
        }

        $response->redirect(url('ayarlar?bilgi=' . urlencode($message)));
    } catch (Throwable $e) {
        $response->redirect(url('ayarlar?hata=' . urlencode('EDM test hatası: ' . $e->getMessage())));
    }
}

public function previewEdm(Request $request, Response $response): void
{
    $this->guard($response);

    try {
        $gelen = $this->edmService->previewRecentInvoices('IN', 7);
        $giden = $this->edmService->previewRecentInvoices('OUT', 7);

        $response->json([
            'ok' => true,
            'gelen_adet' => count($gelen),
            'giden_adet' => count($giden),
            'gelen' => $gelen,
            'giden' => $giden,
        ]);
    } catch (Throwable $e) {
        $response->json([
            'ok' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

private function summarizeCounter(array $counter): string
{
    $flat = json_encode($counter, JSON_UNESCAPED_UNICODE);

    if (!is_string($flat) || trim($flat) === '') {
        return '';
    }

    return 'Kontör yanıtı alındı.';
}
public function kurBilgisi(Request $request, Response $response): void
{
    $this->guard($response);

    $cacheKey  = sys_get_temp_dir() . '/belgec_kur_cache.json';
    $cacheSure = 1800; // 30 dakika

    if (file_exists($cacheKey) && (time() - filemtime($cacheKey)) < $cacheSure) {
        $cached = json_decode((string) file_get_contents($cacheKey), true);
        if (is_array($cached)) {
            $response->json($cached);
            return;
        }
    }

    try {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'Mozilla/5.0 (compatible; Belgec/1.0)',
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $xml = @file_get_contents('https://www.tcmb.gov.tr/kurlar/today.xml', false, $ctx);

        if ($xml === false || $xml === '') {
            throw new \RuntimeException('TCMB verisi alınamadı.');
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);

        $usd = $xpath->evaluate('string(//Currency[@CurrencyCode="USD"]/ForexSelling)');
        $eur = $xpath->evaluate('string(//Currency[@CurrencyCode="EUR"]/ForexSelling)');

        $sonuc = [
            'ok'    => true,
            'tarih' => date('d.m.Y'),
            'usd'   => $usd !== '' ? number_format((float) $usd, 2, ',', '.') : null,
            'eur'   => $eur !== '' ? number_format((float) $eur, 2, ',', '.') : null,
        ];

        file_put_contents($cacheKey, json_encode($sonuc), LOCK_EX);
        $response->json($sonuc);
    } catch (\Throwable $e) {
        $response->json(['ok' => false, 'message' => $e->getMessage()]);
    }
}
public function yedekAl(Request $request, Response $response): void
{
    $this->guard($response);
    $this->guardCsrf($request, $response);

    try {
        $yedekService = new YedekService();
        $yol = $yedekService->manuelYedekAl();
        $dosyaAdi = basename($yol);
        $response->redirect(url('ayarlar/yedek-indir?dosya=' . urlencode($dosyaAdi)));
    } catch (\Throwable $e) {
        $response->redirect(url('ayarlar?hata=' . urlencode('Yedek alınamadı: ' . $e->getMessage())));
    }
}

public function yedekIndir(Request $request, Response $response): void
{
    $this->guard($response);

    $dosyaAdi = trim((string) $request->query('dosya', ''));

    try {
        $yedekService = new YedekService();
        $yol = $yedekService->yedekIndir($dosyaAdi);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($yol) . '"');
        header('Content-Length: ' . filesize($yol));
        header('Cache-Control: no-cache');
        readfile($yol);
        exit;
    } catch (\Throwable $e) {
        $response->redirect(url('ayarlar?hata=' . urlencode($e->getMessage())));
    }
}

public function yedekListesi(Request $request, Response $response): void
{
    $this->guard($response);

    $yedekService = new YedekService();
    $response->json(['ok' => true, 'yedekler' => $yedekService->yedekleriListele()]);
}
public function yedekBildirim(Request $request, Response $response): void
{
    $this->guard($response);

    $flag = BASE_PATH . '/storage/backups/son_otomatik_bildirim.json';

    if (!file_exists($flag)) {
        $response->json(['ok' => true, 'var' => false]);
        return;
    }

    $data = json_decode((string) file_get_contents($flag), true) ?? [];
    $response->json([
        'ok'     => true,
        'var'    => !($data['okundu'] ?? true),
        'dosya'  => $data['dosya']  ?? '',
        'zaman'  => $data['zaman']  ?? '',
    ]);
}

public function yedekBildirimOku(Request $request, Response $response): void
{
    $this->guard($response);

    $flag = BASE_PATH . '/storage/backups/son_otomatik_bildirim.json';

    if (file_exists($flag)) {
        $data = json_decode((string) file_get_contents($flag), true) ?? [];
        $data['okundu'] = true;
        file_put_contents($flag, json_encode($data), LOCK_EX);
    }

    $response->json(['ok' => true]);
}
}