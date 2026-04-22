<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Services\GuncellemeService;

class BildirimController extends BaseController
{
    private GuncellemeService $guncellemeService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        GuncellemeService $guncellemeService
    ) {
        parent::__construct($settingsService);
        $this->guncellemeService = $guncellemeService;
    }

    /**
     * Tum bildirimleri tek response'da birlestirir.
     * Topbar zil butonu bunu cagirir, hem fatura hem guncelleme bilgisini alir.
     */
    public function ozet(Request $request, Response $response): void
    {
        $this->guard($response);

        $ayarlar = $this->settingsService->all();

        // Fatura bildirimleri - sadece EDM aktifse
        $faturaOzet = ['yeni_gelen' => 0, 'yeni_giden' => 0, 'toplam_yeni' => 0];

        if (!empty($ayarlar['edm_aktif'])) {
            $faturaOzet = $this->edmFaturaOzetiCek();
        }

        // Guncelleme bildirimi (her zaman kontrol edilir)
        $guncellemeBilgi = null;
        try {
            $guncelleme = $this->guncellemeService->guncellemeVarMi(false);
            if (!empty($guncelleme['guncelleme_var'])) {
                $guncellemeBilgi = [
                    'son_surum'   => (string) ($guncelleme['son_surum'] ?? ''),
                    'mevcut_surum'=> (string) ($guncelleme['mevcut_surum'] ?? ''),
                    'release_url' => (string) ($guncelleme['release_url'] ?? ''),
                    'indirme_url' => (string) ($guncelleme['indirme_url'] ?? ''),
                    'notlar'      => (string) ($guncelleme['notlar'] ?? ''),
                ];
            }
        } catch (\Throwable $e) {
            // guncelleme kontrolu sessizce atlansin
        }

        $toplam = $faturaOzet['toplam_yeni'] + ($guncellemeBilgi !== null ? 1 : 0);

        $response->json([
            'ok'                 => true,
            'toplam'             => $toplam,
            'fatura'             => $faturaOzet,
            'guncelleme'         => $guncellemeBilgi,
        ]);
    }

    private function edmFaturaOzetiCek(): array
    {
        // EDM kontrol endpoint'inin yaptigi isi burada tekrarlamak yerine,
        // cache dosyasini okumakla yetinelim. Cache yoksa 0 donelim - topbar
        // periyodik olarak EDM kontrolu de calistirir, o zaman guncellenir.
        $cacheDosya = BASE_PATH . '/edm_kontrol_cache.json';
        if (file_exists($cacheDosya) && (time() - filemtime($cacheDosya)) < 300) {
            $cached = json_decode((string) file_get_contents($cacheDosya), true);
            if (is_array($cached) && !empty($cached['ok'])) {
                return [
                    'yeni_gelen'  => (int) ($cached['yeni_gelen']  ?? 0),
                    'yeni_giden'  => (int) ($cached['yeni_giden']  ?? 0),
                    'toplam_yeni' => (int) ($cached['toplam_yeni'] ?? 0),
                ];
            }
        }

        return ['yeni_gelen' => 0, 'yeni_giden' => 0, 'toplam_yeni' => 0];
    }
}
