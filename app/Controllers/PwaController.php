<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\SettingsServiceInterface;

class PwaController
{
    private SettingsServiceInterface $settingsService;

    public function __construct(SettingsServiceInterface $settingsService)
{
    $this->settingsService = $settingsService;
}

    public function manifest(Request $request, Response $response): void
    {
        $ayarlar = $this->settingsService->all();

        $response->json([
            'name' => 'Belgeç',
            'short_name' => 'Belgeç',
            'description' => 'Belgeç',
            'start_url' => url('dashboard'),
            'scope' => url(''),
            'display' => 'standalone',
            'background_color' => '#0f172a',
            'theme_color' => !empty($ayarlar['tema_rengi']) ? $ayarlar['tema_rengi'] : '#2563eb',
            'icons' => [
                [
                    'src' => url('icons/icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => url('icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
            ],
        ]);
    }

    public function serviceWorker(Request $request, Response $response): void
    {
        http_response_code(200);
        header('Content-Type: application/javascript; charset=UTF-8');

        echo <<<JS
self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        fetch(event.request).catch(() => new Response('İnternet bağlantınız yok.'))
    );
});
JS;
        exit;
    }
}