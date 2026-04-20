<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\CariHareketServiceInterface;
use App\Interfaces\CariServiceInterface;

class CariHareketController extends BaseController
{
    private CariHareketServiceInterface $service;
private CariServiceInterface $cariService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    CariHareketServiceInterface $service,
    CariServiceInterface $cariService
) {
    parent::__construct($settingsService);
    $this->service = $service;
    $this->cariService = $cariService;
}

    public function index(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'cari_hareketler.goruntule');

        $rapor = $this->service->getReport($request->query());
        $cariler = $this->cariService->getAll();

        $response->view('cari_hareket.index', [
            'pageTitle' => 'Cari Hareketler',
            'ayarlar' => $this->settingsService->all(),
            'rapor' => $rapor,
            'cariler' => $cariler,
            'duzenlenecekHareket' => null,
            'include_modal_js' => true,
        ], 'layouts.app');
    }


    public function update(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'cari_hareketler.duzenle');
        
        $this->guardCsrf($request, $response);

        $id = (int) $request->input('hareket_id', 0);

        try {
            $this->service->update($id, $request->all());
            $_SESSION['flash_success'] = 'Cari hareket kaydı güncellendi.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        $this->auditLog('duzenle', 'cari_hareketler', $id);
        $response->redirect(url('cari-hareketler'));
    }

    public function delete(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'cari_hareketler.sil');
        
        $this->guardCsrf($request, $response);

        $id = (int) $request->input('hareket_id', 0);

        try {
            $this->service->delete($id);
            $_SESSION['flash_success'] = 'Cari hareket kaydı silindi.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        $this->auditLog('sil', 'cari_hareketler', $id);
        $response->redirect(url('cari-hareketler'));
    }
}