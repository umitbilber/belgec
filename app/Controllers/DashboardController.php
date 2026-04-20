<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Interfaces\DashboardServiceInterface;

class DashboardController extends BaseController
{
    private DashboardServiceInterface $dashboardService;

    public function __construct(
    SettingsServiceInterface $settingsService,
    DashboardServiceInterface $dashboardService
) {
    parent::__construct($settingsService);
    $this->dashboardService = $dashboardService;
}

    public function index(Request $request, Response $response): void
    {
        $this->guardIzin($response, 'dashboard.goruntule');

        $ayarlar = $this->settingsService->all();
        $ozet = $this->dashboardService->getDailySummary();

        $response->view('dashboard.index', [
            'pageTitle' => 'Yönetici Ekranı',
            'ayarlar' => $ayarlar,
            'ozet' => $ozet,
        ], 'layouts.app');
    }
}