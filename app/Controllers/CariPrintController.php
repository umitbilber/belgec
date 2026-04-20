<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use Throwable;
use App\Interfaces\CariPrintServiceInterface;

class CariPrintController extends BaseController
{
    private CariPrintServiceInterface $service;

    public function __construct(
    SettingsServiceInterface $settingsService,
    CariPrintServiceInterface $service
) {
    parent::__construct($settingsService);
    $this->service = $service;
}

    public function show(Request $request, Response $response): void
    {
        $this->guard($response);

        $id = (int) $request->query('id', 0);

        try {
            $data = $this->service->getPrintData($id);
            $bakiyeDurumu = $this->service->getBakiyeDurumu((float) ($data['cari']['bakiye'] ?? 0));

            $response->view('cari.print', [
                'ayarlar' => $data['ayarlar'],
                'cari' => $data['cari'],
                'hareketler' => $data['hareketler'],
                'bakiye_durumu' => $bakiyeDurumu,
                'print_service' => $this->service,
            ]);
        } catch (Throwable $e) {
            $response->abort(404, 'Cari ekstresi hazırlanamadı: ' . $e->getMessage());
        }
    }
}