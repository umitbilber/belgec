<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;
use App\Services\GuncellemeService;

class GuncellemeController extends BaseController
{
    private GuncellemeService $guncellemeService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        GuncellemeService $guncellemeService
    ) {
        parent::__construct($settingsService);
        $this->guncellemeService = $guncellemeService;
    }

    public function kontrol(Request $request, Response $response): void
    {
        $this->guard($response);

        $force = (string) $request->query('force', '0') === '1';
        $sonuc = $this->guncellemeService->guncellemeVarMi($force);

        $response->json($sonuc);
    }
}