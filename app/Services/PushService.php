<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\PushRepositoryInterface;
use App\Interfaces\PushServiceInterface;
use App\Interfaces\SettingsServiceInterface;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use Throwable;

class PushService implements PushServiceInterface
{
    private PushRepositoryInterface $pushRepository;
    private SettingsServiceInterface $settingsService;

    public function __construct(
        PushRepositoryInterface $pushRepository,
        SettingsServiceInterface $settingsService
    ) {
        $this->pushRepository = $pushRepository;
        $this->settingsService = $settingsService;
    }

    public function aboneOl(
        int $kullaniciId,
        string $endpoint,
        string $p256dh,
        string $auth,
        ?string $userAgent = null
    ): void {
        if ($endpoint === '' || $p256dh === '' || $auth === '') {
            throw new \InvalidArgumentException('Eksik abonelik bilgisi.');
        }

        $this->pushRepository->upsertAbonelik($kullaniciId, $endpoint, $p256dh, $auth, $userAgent);
    }

    public function aboneCik(string $endpoint): void
    {
        if ($endpoint === '') {
            return;
        }
        $this->pushRepository->silEndpointIle($endpoint);
    }

    public function gonder(int $kullaniciId, string $baslik, string $metin, ?string $url = null): int
    {
        $abonelikler = $this->pushRepository->kullaniciAbonelikleri($kullaniciId);
        if (count($abonelikler) === 0) {
            return 0;
        }

        $auth = $this->vapidAuthArray();
        $webPush = new WebPush(['VAPID' => $auth]);
        $webPush->setReuseVAPIDHeaders(true);

        $payload = json_encode([
            'baslik' => $baslik,
            'metin'  => $metin,
            'url'    => $url ?? '',
        ], JSON_UNESCAPED_UNICODE);

        $subscriptionMap = [];

        foreach ($abonelikler as $satir) {
            try {
                $subscription = Subscription::create([
                    'endpoint'        => (string) $satir['endpoint'],
                    'publicKey'       => (string) $satir['p256dh'],
                    'authToken'       => (string) $satir['auth'],
                    'contentEncoding' => 'aesgcm',
                ]);
                $webPush->queueNotification($subscription, (string) $payload);
                $subscriptionMap[(string) $satir['endpoint']] = (int) $satir['id'];
            } catch (Throwable $e) {
                // Gecersiz abonelik kaydi - direkt sil
                $this->pushRepository->silId((int) $satir['id']);
            }
        }

        $basarili = 0;

        foreach ($webPush->flush() as $report) {
            $endpoint = (string) $report->getRequest()->getUri();

            if ($report->isSuccess()) {
                $basarili++;
                continue;
            }

            // 410 Gone veya 404 Not Found -> abonelik gecersiz, temizle
            if ($report->isSubscriptionExpired()) {
                $hedefId = null;
                foreach ($subscriptionMap as $abEndpoint => $abId) {
                    if (strpos($endpoint, $abEndpoint) === 0 || $abEndpoint === $endpoint) {
                        $hedefId = $abId;
                        break;
                    }
                }
                if ($hedefId !== null) {
                    $this->pushRepository->silId($hedefId);
                }
            }
        }

        return $basarili;
    }

    public function vapidPublicKey(): string
    {
        return $this->vapidAuthArray()['publicKey'];
    }

    public function sonDurumAl(int $kullaniciId, string $tip): ?string
    {
        return $this->pushRepository->sonDurumAl($kullaniciId, $tip);
    }

    public function sonDurumKaydet(int $kullaniciId, string $tip, string $deger): void
    {
        $this->pushRepository->sonDurumKaydet($kullaniciId, $tip, $deger);
    }

    /**
     * VAPID anahtar ciftini dondurur. Ayarlarda yoksa uretir ve kaydeder (lazy).
     *
     * @return array{subject:string, publicKey:string, privateKey:string}
     */
    private function vapidAuthArray(): array
    {
        $ayarlar = $this->settingsService->all();

        $public  = (string) ($ayarlar['vapid_public_key']  ?? '');
        $private = (string) ($ayarlar['vapid_private_key'] ?? '');

        if ($public === '' || $private === '') {
            $keys = VAPID::createVapidKeys();
            $public  = (string) $keys['publicKey'];
            $private = (string) $keys['privateKey'];

            $ayarlar['vapid_public_key']  = $public;
            $ayarlar['vapid_private_key'] = $private;
            $this->settingsService->save($ayarlar);
        }

        $eposta = trim((string) ($ayarlar['eposta'] ?? ''));
        $subject = $eposta !== '' ? ('mailto:' . $eposta) : 'mailto:admin@belgec.local';

        return [
            'subject'    => $subject,
            'publicKey'  => $public,
            'privateKey' => $private,
        ];
    }
}
