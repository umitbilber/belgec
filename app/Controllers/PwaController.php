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
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Service-Worker-Allowed: /');

        $iconUrl     = url('icons/icon-192.png');
        $defaultUrl  = url('dashboard');

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

// Backend'den gelen push payload'ini notification'a donusturur.
// Payload formati: { baslik, metin, url }
self.addEventListener('push', (event) => {
    let veri = { baslik: 'Belgeç', metin: '', url: '' };

    if (event.data) {
        try {
            veri = Object.assign(veri, event.data.json());
        } catch (e) {
            veri.metin = event.data.text();
        }
    }

    const options = {
        body: veri.metin || '',
        icon: '{$iconUrl}',
        badge: '{$iconUrl}',
        tag: 'belgec-bildirim',
        renotify: true,
        data: { url: veri.url || '{$defaultUrl}' },
        vibrate: [200, 100, 200],
        requireInteraction: false
    };

    event.waitUntil(
        self.registration.showNotification(veri.baslik || 'Belgeç', options)
    );
});

// Bildirime tiklaninca: acik tab varsa onu focus et, yoksa yeni tab ac.
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const hedefUrl = (event.notification.data && event.notification.data.url) || '{$defaultUrl}';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url && client.url.indexOf(hedefUrl) !== -1 && 'focus' in client) {
                    return client.focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(hedefUrl);
            }
            return null;
        })
    );
});
JS;
        exit;
    }
}
}
}
