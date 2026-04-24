<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\PushServiceInterface;
use App\Interfaces\SettingsServiceInterface;

class PushController extends BaseController
{
    private PushServiceInterface $pushService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        PushServiceInterface $pushService
    ) {
        parent::__construct($settingsService);
        $this->pushService = $pushService;
    }

    /**
     * Frontend subscribe() isteginden once bu endpoint'i cagirir, VAPID public key alir.
     * Ilk cagrida anahtar cifti uretilip ayarlar.json'a kaydedilir (lazy).
     */
    public function vapidKey(Request $request, Response $response): void
    {
        $this->guard($response);

        try {
            $publicKey = $this->pushService->vapidPublicKey();
        } catch (\Throwable $e) {
            $response->json([
                'ok'    => false,
                'mesaj' => 'VAPID anahtarı üretilemedi: ' . $e->getMessage(),
            ], 500);
            return;
        }

        $response->json([
            'ok'         => true,
            'public_key' => $publicKey,
        ]);
    }

    /**
     * Browser PushManager'dan aldigi subscription'i backend'e kaydeder.
     * Form alanlari: endpoint, p256dh, auth, _csrf_token
     */
    public function aboneOl(Request $request, Response $response): void
    {
        $this->guard($response);
        $this->guardCsrf($request, $response);

        $kullaniciId = $this->aktifKullaniciId();
        if ($kullaniciId === null) {
            $response->json(['ok' => false, 'mesaj' => 'Oturum bulunamadı.'], 401);
            return;
        }

        $endpoint = trim((string) $request->input('endpoint', ''));
        $p256dh   = trim((string) $request->input('p256dh', ''));
        $auth     = trim((string) $request->input('auth', ''));

        if ($endpoint === '' || $p256dh === '' || $auth === '') {
            $response->json(['ok' => false, 'mesaj' => 'Eksik abonelik bilgisi.'], 422);
            return;
        }

        $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $userAgent = $userAgent !== '' ? substr($userAgent, 0, 255) : null;

        try {
            $this->pushService->aboneOl($kullaniciId, $endpoint, $p256dh, $auth, $userAgent);
        } catch (\Throwable $e) {
            $response->json([
                'ok'    => false,
                'mesaj' => 'Abonelik kaydedilemedi: ' . $e->getMessage(),
            ], 500);
            return;
        }

        $response->json(['ok' => true]);
    }

    /**
     * Browser izin geri alindiginda veya kullanici abonelikten cikmak istediginde.
     * Form alanlari: endpoint, _csrf_token
     */
    public function aboneCik(Request $request, Response $response): void
    {
        $this->guard($response);
        $this->guardCsrf($request, $response);

        $endpoint = trim((string) $request->input('endpoint', ''));

        if ($endpoint === '') {
            $response->json(['ok' => false, 'mesaj' => 'Endpoint gerekli.'], 422);
            return;
        }

        try {
            $this->pushService->aboneCik($endpoint);
        } catch (\Throwable $e) {
            $response->json([
                'ok'    => false,
                'mesaj' => 'Abonelik silinemedi: ' . $e->getMessage(),
            ], 500);
            return;
        }

        $response->json(['ok' => true]);
    }
}
