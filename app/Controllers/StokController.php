<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\StokServiceInterface;
use Throwable;

class StokController extends BaseController
{
    private StokServiceInterface $stokService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    StokServiceInterface $stokService
) {
    parent::__construct($settingsService);
    $this->stokService = $stokService;
}

    public function index(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'stoklar.goruntule');

        $hata = (string) $request->query('hata', '');

                $response->view('stok.index', [
            'pageTitle' => 'Stok Yönetimi',
            'ayarlar' => $this->settingsService->all(),
            'stoklar' => $this->stokService->getAll(),
            'hata_mesaji' => $hata,
            'include_modal_js' => true,
        ], 'layouts.app');
    }

    public function store(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'stoklar.ekle');
        
        $this->guardCsrf($request, $response);

        try {
            $this->stokService->create($request->input());
            this->auditLog('ekle', 'stoklar');
            $response->redirect(url('stoklar'));
        } catch (Throwable $e) {
            $response->redirect(url('stoklar?hata=' . urlencode($e->getMessage())));
        }
    }

    public function update(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'stoklar.duzenle');
        
        $this->guardCsrf($request, $response);

        $id = (int) $request->input('stok_id', 0);

        try {
            $this->stokService->update($id, $request->input());
            this->auditLog('duzenle', 'stoklar');
            $response->redirect(url('stoklar'));
        } catch (Throwable $e) {
            $response->redirect(url('stoklar?hata=' . urlencode($e->getMessage())));
        }
    }
    
    public function fiyatGecmisi(Request $request, Response $response): void
{
    $this->guardIzin($response, 'stoklar.goruntule');

    $stokKodu = trim((string) $request->query('stok_kodu', ''));
    $urunAdi  = trim((string) $request->query('urun_adi', ''));

    if ($stokKodu === '' && $urunAdi === '') {
        $response->json(['ok' => false, 'satirlar' => []]);
        return;
    }

    $satirlar = $this->stokService->getFiyatGecmisi($stokKodu, $urunAdi);

    $response->json(['ok' => true, 'satirlar' => $satirlar]);
}

    public function delete(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'stoklar.sil');

        $id = (int) $request->input('id', 0);

        try {
            $this->stokService->delete($id);
            this->auditLog('sil', 'stoklar');
            $response->redirect(url('stoklar'));
        } catch (Throwable $e) {
            $response->redirect(url('stoklar?hata=' . urlencode($e->getMessage())));
        }
    }
}