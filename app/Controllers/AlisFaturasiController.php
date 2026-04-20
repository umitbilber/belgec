<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use Throwable;
use App\Interfaces\AlisFaturasiServiceInterface;
use App\Interfaces\CariServiceInterface;
use App\Interfaces\StokServiceInterface;

class AlisFaturasiController extends BaseController
{
    private AlisFaturasiServiceInterface $service;
private CariServiceInterface $cariService;
private StokServiceInterface $stokService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    AlisFaturasiServiceInterface $service,
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
        $this->guardIzin($response, 'alis_fatura.goruntule');

        $hata = (string) $request->query('hata', '');
        $edmOnbilgi = null;
$edmCari    = trim((string) $request->query('edm_cari', ''));
if ($edmCari !== '') {
    $edmKalemlerRaw = trim((string) $request->query('edm_kalemler', ''));
$edmKalemler    = [];
if ($edmKalemlerRaw !== '') {
    $decoded = base64_decode($edmKalemlerRaw, true);
    if ($decoded !== false) {
        $parsed = json_decode($decoded, true);
        if (is_array($parsed)) {
            $edmKalemler = $parsed;
        }
    }
}

$edmOnbilgi = [
    'cari_adi'    => $edmCari,
    'fatura_no'   => trim((string) $request->query('edm_fatura_no', '')),
    'tarih'       => trim((string) $request->query('edm_tarih', date('Y-m-d'))),
    'kdvli_tutar' => trim((string) $request->query('edm_kdvli', '')),
    'kdvsiz_tutar'=> trim((string) $request->query('edm_kdvsiz', '')),
    'kalemler'    => $edmKalemler,
];
}
        

                $response->view('alis_fatura.index', [
            'pageTitle' => 'Alış Faturaları',
            'ayarlar' => $this->settingsService->all(),
            'faturalar' => $this->service->getFiltered([
    'cari_id'         => trim((string) $request->query('cari_id', '')),
    'tarih_baslangic' => trim((string) $request->query('tarih_baslangic', '')),
    'tarih_bitis'     => trim((string) $request->query('tarih_bitis', '')),
    'tutar_min'       => trim((string) $request->query('tutar_min', '')),
    'tutar_max'       => trim((string) $request->query('tutar_max', '')),
    'fatura_no'       => trim((string) $request->query('fatura_no', '')),
]),
'filtreler' => [
    'cari_id'         => trim((string) $request->query('cari_id', '')),
    'tarih_baslangic' => trim((string) $request->query('tarih_baslangic', '')),
    'tarih_bitis'     => trim((string) $request->query('tarih_bitis', '')),
    'tutar_min'       => trim((string) $request->query('tutar_min', '')),
    'tutar_max'       => trim((string) $request->query('tutar_max', '')),
    'fatura_no'       => trim((string) $request->query('fatura_no', '')),
],
            'cariler' => $this->cariService->getAll(),
            'stoklar' => $this->stokService->getAll(),
            'hata_mesaji' => $hata,
            'include_modal_js' => true,
            'include_drag_sort_js' => true,
            'edm_onbilgi' => $edmOnbilgi,
        ], 'layouts.app');
    }

    public function store(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'alis_fatura.ekle');
        
        $this->guardCsrf($request, $response);

        try {
            $this->service->create($request->input());
            $this->auditLog('ekle', 'alis_fatura');
            $response->redirect(url('alis-faturalari'));
        } catch (Throwable $e) {
            $response->redirect(url('alis-faturalari?hata=' . urlencode($e->getMessage())));
        }
    }

    public function update(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'alis_fatura.duzenle');
        
        $this->guardCsrf($request, $response);

        $id = (int) $request->input('fatura_id', 0);

        try {
            $this->service->update($id, $request->input());
            $this->auditLog('duzenle', 'alis_fatura', $id);
            $response->redirect(url('alis-faturalari'));
        } catch (Throwable $e) {
            $response->redirect(url('alis-faturalari?hata=' . urlencode($e->getMessage())));
        }
    }

    public function delete(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'alis_fatura.sil');

        $id = (int) $request->input('id', 0);

        try {
            $this->service->delete($id);
            $this->auditLog('sil', 'alis_fatura', $id);
            $response->redirect(url('alis-faturalari'));
        } catch (Throwable $e) {
            $response->redirect(url('alis-faturalari?hata=' . urlencode($e->getMessage())));
        }
    }
}