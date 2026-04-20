<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use Throwable;
use App\Interfaces\TeklifPrintServiceInterface;

class TeklifPrintController extends BaseController
{
    private TeklifPrintServiceInterface $service;

    public function __construct(
    SettingsServiceInterface $settingsService,
    TeklifPrintServiceInterface $service
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

            $response->view('teklif.print', [
                'ayarlar' => $data['ayarlar'],
                'teklif' => $data['teklif'],
                'kalemler' => $data['kalemler'],
                'toplamlar' => $data['toplamlar'] ?? [],
            ]);
        } catch (Throwable $e) {
            $response->abort(404, 'Teklif çıktısı hazırlanamadı: ' . $e->getMessage());
        }
    }
}