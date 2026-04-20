<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\RaporServiceInterface;

class RaporController extends BaseController
{
    private RaporServiceInterface $service;

    public function __construct(
        SettingsServiceInterface $settingsService,
        RaporServiceInterface $service
    ) {
        parent::__construct($settingsService);
        $this->service = $service;
    }

    public function index(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'raporlar.goruntule');

        $bugun     = date('Y-m-d');
        $baslangic = trim((string) $request->query('baslangic', date('Y-m-d', strtotime('-11 months'))));
        $bitis     = trim((string) $request->query('bitis', $bugun));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $baslangic)) $baslangic = date('Y-m-d', strtotime('-11 months'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bitis))     $bitis     = $bugun;

        $response->view('raporlar.index', [
            'pageTitle'          => 'Raporlar',
            'ayarlar'            => $this->settingsService->all(),
            'baslangic'          => $baslangic,
            'bitis'              => $bitis,
            'aylik_ozet'         => $this->service->aylikFaturaOzeti($baslangic, $bitis),
            'en_cok_satilan'     => $this->service->enCokSatilanUrunler($baslangic, $bitis, 10),
            'cari_ozet'          => $this->service->cariAlisSatisOzeti($baslangic, $bitis),
            'en_yuksek_bakiye'   => $this->service->enYuksekBakiyeliCariler(10),
        ], 'layouts.app');
    }
}