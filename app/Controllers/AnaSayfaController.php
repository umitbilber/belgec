<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\DashboardServiceInterface;

class AnaSayfaController extends BaseController
{
    private DashboardServiceInterface $dashboardService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        DashboardServiceInterface $dashboardService
    ) {
        parent::__construct($settingsService);
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request, Response $response): void
    {
        $this->guard($response);

        $tumModuller = $this->dashboardService->getModuleDefinitions();
        $rol         = $_SESSION['kullanici_rol'] ?? 'yonetici';
        $izinler     = $_SESSION['kullanici_izinler'] ?? [];

        // İzin — modül eşleştirmesi
        $modulIzinMap = [
            'yonetici'         => null, // her zaman göster (yönetici modülü)
            'cariler'          => 'cariler.goruntule',
            'cari_yaslandirma' => 'cari_yaslandirma.goruntule',
            'cari_hareketler'  => 'cari_hareketler.goruntule',
            'stoklar'          => 'stoklar.goruntule',
            'stok_hareketleri' => 'stok_hareketleri.goruntule',
            'alis_faturalari'  => 'alis_fatura.goruntule',
            'satis_faturalari' => 'satis_fatura.goruntule',
            'teklifler'        => 'teklifler.goruntule',
            'veri_aktarim'     => 'veri_aktarim.goruntule',
            'edm'              => 'edm.goruntule',
            'ayarlar'          => null, // yönetici kontrolü ayrıca
            'kullanicilar'     => null, // yönetici kontrolü ayrıca
            'mutabakat'        => 'mutabakat.goruntule',
            'dashboard'        => 'dashboard.goruntule',
            'raporlar' => 'raporlar.goruntule',
            'audit_log' => null, // sadece yönetici
        ];

        $gorunurModuller = [];
        foreach ($tumModuller as $key => $modul) {
            if ($rol === 'yonetici') {
                $gorunurModuller[$key] = $modul;
                continue;
            }

            if (in_array($key, ['ayarlar', 'kullanicilar', 'audit_log'], true)) {
    continue;
}
            
            

            $gerekliIzin = $modulIzinMap[$key] ?? null;
            if ($gerekliIzin === null || in_array($gerekliIzin, $izinler, true)) {
                $gorunurModuller[$key] = $modul;
            }
        }

        $response->view('anasayfa.index', [
            'pageTitle' => 'Ana Sayfa',
            'ayarlar'   => $this->settingsService->all(),
            'moduller'  => $gorunurModuller,
        ], 'layouts.app');
    }
}