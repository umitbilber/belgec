<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\CariServiceInterface;

class VeriAktarimController extends BaseController
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
    $this->guardIzin($response, 'veri_aktarim.goruntule');

    $hata = (string) $request->query('hata', '');
    $bilgi = (string) $request->query('bilgi', '');
    $wolvoxPreview = $_SESSION['wolvox_preview'] ?? null;
    $wolvoxQueue = $_SESSION['wolvox_import_queue'] ?? [];

    $response->view('veri-aktarim.index', [
        'pageTitle' => 'Veri Aktarım',
        'ayarlar' => $this->settingsService->all(),
        'cariler' => $this->cariService->getAll(),
        'hata_mesaji' => $hata,
        'bilgi_mesaji' => $bilgi,
        'wolvox_preview' => $wolvoxPreview,
        'wolvox_queue' => is_array($wolvoxQueue) ? array_values($wolvoxQueue) : [],
        'include_modal_js' => true,
    ], 'layouts.app');
}
}