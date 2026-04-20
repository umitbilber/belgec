<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\CariServiceInterface;
use Throwable;
use App\Interfaces\SettingsServiceInterface;

class CariController extends BaseController
{
    private CariServiceInterface $cariService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    CariServiceInterface $cariService
) {
    parent::__construct($settingsService);
    $this->cariService = $cariService;
}

    public function index(Request $request, Response $response): void
{
    $this->guardIzin($response, 'cariler.goruntule');

    $hata = (string) $request->query('hata', '');
    $bilgi = (string) $request->query('bilgi', '');
	

    $response->view('cari.index', [
        'pageTitle' => 'Cari Yönetimi',
        'ayarlar' => $this->settingsService->all(),
        'cariler' => $this->cariService->getAll(),
        'hata_mesaji' => $hata,
        'bilgi_mesaji' => $bilgi,
        'include_modal_js' => true,
    ], 'layouts.app');
}

    public function store(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'cariler.ekle');
        
        $this->guardCsrf($request, $response);

        try {
            $this->cariService->create($request->input());
            $this->auditLog('ekle', 'cariler', $id);
            $response->redirect(url('cariler'));
        } catch (Throwable $e) {
            $response->redirect(url('cariler?hata=' . urlencode($e->getMessage())));
        }
    }

    public function update(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'cariler.duzenle');
        
        $this->guardCsrf($request, $response);

        $id = (int) $request->input('cari_id', 0);

        try {
            $this->cariService->update($id, $request->input());
            $this->auditLog('duzenle', 'cariler', $id);
            $response->redirect(url('cariler'));
        } catch (Throwable $e) {
            $response->redirect(url('cariler?hata=' . urlencode($e->getMessage())));
        }
    }

    public function delete(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'cariler.sil');
        
        $this->guardCsrf($request, $response);

        $id = (int) $request->input('id', 0);

        try {
            $this->cariService->delete($id);
            $this->auditLog('sil', 'cariler', $id);
            $response->redirect(url('cariler'));
        } catch (Throwable $e) {
            $response->redirect(url('cariler?hata=' . urlencode($e->getMessage())));
        }
    }

    public function movement(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'cariler.duzenle');
        
        $this->guardCsrf($request, $response);

        try {
            $this->cariService->recordMovement($request->input());
            $response->redirect(url('cariler'));
        } catch (Throwable $e) {
            $response->redirect(url('cariler?hata=' . urlencode($e->getMessage())));
        }
    }
}