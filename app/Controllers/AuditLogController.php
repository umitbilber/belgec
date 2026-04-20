<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Services\AuditLogService;

class AuditLogController extends BaseController
{
    private AuditLogService $service;

    public function __construct(SettingsServiceInterface $settingsService)
    {
        parent::__construct($settingsService);
        $this->service = new AuditLogService();
    }

    public function index(Request $request, Response $response): void
    {
        $this->guard($response);

        if (!$this->isYonetici()) {
            $response->abort(403, 'Bu sayfaya sadece yönetici erişebilir.');
        }

        $filters = [
            'kullanici_adi'   => trim((string) $request->query('kullanici_adi', '')),
            'modul'           => trim((string) $request->query('modul', '')),
            'islem'           => trim((string) $request->query('islem', '')),
            'tarih_baslangic' => trim((string) $request->query('tarih_baslangic', '')),
            'tarih_bitis'     => trim((string) $request->query('tarih_bitis', '')),
        ];

        $response->view('audit_log.index', [
            'pageTitle'    => 'Audit Log',
            'ayarlar'      => $this->settingsService->all(),
            'kayitlar'     => $this->service->getFiltered($filters),
            'kullanicilar' => $this->service->getKullanicilar(),
            'moduller'     => $this->service->getModuller(),
            'filtreler'    => $filters,
        ], 'layouts.app');
    }
}