<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\StokHareketServiceInterface;
use App\Interfaces\StokServiceInterface;

class StokHareketController extends BaseController
{
    private StokHareketServiceInterface $service;
    private StokServiceInterface $stokService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        StokHareketServiceInterface $service,
        StokServiceInterface $stokService
    ) {
        parent::__construct($settingsService);
        $this->service    = $service;
        $this->stokService = $stokService;
    }

    public function index(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'stok_hareketleri.goruntule');

        $rapor  = $this->service->getReport($request->query());
        $stoklar = $this->stokService->getAllBasic();

        $response->view('stok_hareket.index', [
            'pageTitle' => 'Stok Hareketleri',
            'ayarlar'   => $this->settingsService->all(),
            'rapor'     => $rapor,
            'stoklar'   => $stoklar,
        ], 'layouts.app');
    }
}