<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\PushServiceInterface;
use App\Interfaces\SettingsServiceInterface;
use App\Services\GuncellemeService;

class BildirimController extends BaseController
{
    private GuncellemeService $guncellemeService;
    private PushServiceInterface $pushService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        GuncellemeService $guncellemeService,
        PushServiceInterface $pushService
    ) {
        parent::__construct($settingsService);
        $this->guncellemeService = $guncellemeService;
        $this->pushService = $pushService;
    }

    /**
     * Tum bildirimleri tek response'da birlestirir.
     * Topbar zil butonu bunu cagirir, hem fatura hem guncelleme bilgisini alir.
     * Yeni bildirim tespit edilirse kullanici cihazlarina push tetiklenir.
     */
    public function ozet(Request $request, Response $response): void
    {
        $this->guard($response);

        $ayarlar = $this->settingsService->all();
        $kullaniciId = $this->aktifKullaniciId();

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

        // Yeni bildirim varsa telefona push gonder (sayfa acik degilken bile calisir)
        if ($kullaniciId !== null) {
            $this->pushTetikle($kullaniciId, $faturaOzet, $guncellemeBilgi);
        }

        $response->json([
            'ok'                 => true,
            'toplam'             => $toplam,
            'fatura'             => $faturaOzet,
            'guncelleme'         => $guncellemeBilgi,
        ]);
    }

    /**
     * Son bildirilen degerle karsilastirip sadece degisim varsa push gonderir.
     * Boylece aynı bildirim her 5 dakikada bir spam olarak gitmez.
     */
    private function pushTetikle(int $kullaniciId, array $faturaOzet, ?array $guncellemeBilgi): void
    {
        try {
            // --- EDM fatura kontrolu ---
            $edmToplam = (int) ($faturaOzet['toplam_yeni'] ?? 0);
            $edmSonBildirilen = (int) ($this->pushService->sonDurumAl($kullaniciId, 'edm_toplam') ?? '0');

            if ($edmToplam > 0 && $edmToplam > $edmSonBildirilen) {
                $metinParcalari = [];
                if (($faturaOzet['yeni_gelen'] ?? 0) > 0) {
                    $metinParcalari[] = $faturaOzet['yeni_gelen'] . ' gelen';
                }
                if (($faturaOzet['yeni_giden'] ?? 0) > 0) {
                    $metinParcalari[] = $faturaOzet['yeni_giden'] . ' giden';
                }
                $metin = implode(', ', $metinParcalari) . ' fatura var';

                $this->pushService->gonder(
                    $kullaniciId,
                    'Yeni e-Fatura',
                    $metin,
                    url('edm-faturalar')
                );
                $this->pushService->sonDurumKaydet($kullaniciId, 'edm_toplam', (string) $edmToplam);
            } elseif ($edmToplam === 0 && $edmSonBildirilen > 0) {
                // Faturalar goruldu/temizlendi - son durumu sifirla ki bir sonraki fatura tekrar push tetiklesin
                $this->pushService->sonDurumKaydet($kullaniciId, 'edm_toplam', '0');
            }

            // --- Guncelleme kontrolu ---
            if ($guncellemeBilgi !== null) {
                $yeniSurum = (string) ($guncellemeBilgi['son_surum'] ?? '');
                if ($yeniSurum !== '') {
                    $sonBildirilenSurum = $this->pushService->sonDurumAl($kullaniciId, 'guncelleme_surum');
                    if ($sonBildirilenSurum !== $yeniSurum) {
                        $this->pushService->gonder(
                            $kullaniciId,
                            'Güncelleme Var',
                            'v' . $yeniSurum . ' yayınlandı',
                            url('dashboard')
                        );
                        $this->pushService->sonDurumKaydet($kullaniciId, 'guncelleme_surum', $yeniSurum);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Push failure ozet endpoint response'unu bozmasin, sessizce gec
            // TODO: storage/logs altina dump edilebilir
        }
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
