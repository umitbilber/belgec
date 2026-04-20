<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use Throwable;
use App\Interfaces\CariServiceInterface;
use App\Interfaces\StokServiceInterface;
use App\Interfaces\TeklifServiceInterface;

class TeklifController extends BaseController
{
    private TeklifServiceInterface $service;
private CariServiceInterface $cariService;
private StokServiceInterface $stokService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    TeklifServiceInterface $service,
    CariServiceInterface $cariService,
    StokServiceInterface $stokService
) {
    parent::__construct($settingsService);
    $this->service = $service;
    $this->cariService = $cariService;
    $this->stokService = $stokService;
}

    public function index(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'teklifler.goruntule');

        $hata = (string) $request->query('hata', '');

                $response->view('teklif.index', [
            'pageTitle' => 'Teklifler',
            'ayarlar' => $this->settingsService->all(),
            'teklifler' => $this->service->getAll(),
            'cariler' => $this->cariService->getAll(),
            'stoklar' => $this->stokService->getAll(),
            'hata_mesaji' => $hata,
            'include_modal_js' => true,
            'include_drag_sort_js' => true,
        ], 'layouts.app');
    }

    public function store(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'teklifler.ekle');
        
        $this->guardCsrf($request, $response);

        try {
            $this->service->create($request->input());
            $this->auditLog('ekle', 'teklifler');
            $response->redirect(url('teklifler'));
        } catch (Throwable $e) {
            $response->redirect(url('teklifler?hata=' . urlencode($e->getMessage())));
        }
    }

    public function update(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'teklifler.duzenle');
        
        $this->guardCsrf($request, $response);

        $id = (int) $request->input('teklif_id', 0);

        try {
            $this->service->update($id, $request->input());
            $this->auditLog('duzenle', 'teklifler');
            $response->redirect(url('teklifler'));
        } catch (Throwable $e) {
            $response->redirect(url('teklifler?hata=' . urlencode($e->getMessage())));
        }
    }

    public function delete(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'teklifler.sil');

        $id = (int) $request->input('id', 0);

        try {
            $this->service->delete($id);
            $this->auditLog('sil', 'teklifler');
            $response->redirect(url('teklifler'));
        } catch (Throwable $e) {
            $response->redirect(url('teklifler?hata=' . urlencode($e->getMessage())));
        }
    }
}