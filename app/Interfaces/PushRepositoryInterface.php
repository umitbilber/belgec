<?php

declare(strict_types=1);

namespace App\Interfaces;

interface PushRepositoryInterface
{
    /**
     * Yeni abonelik ekler veya mevcut endpoint_hash uzerinden gunceller.
     */
    public function upsertAbonelik(
        int $kullaniciId,
        string $endpoint,
        string $p256dh,
        string $auth,
        ?string $userAgent
    ): void;

    /**
     * Endpoint'e gore abonelik siler (abone cikma).
     */
    public function silEndpointIle(string $endpoint): void;

    /**
     * Id'ye gore abonelik siler (push gonderimi basarisiz olunca temizlik).
     */
    public function silId(int $id): void;

    /**
     * Bir kullanicinin tum aktif aboneliklerini dondurur.
     *
     * @return array<int, array{id:int, endpoint:string, p256dh:string, auth:string}>
     */
    public function kullaniciAbonelikleri(int $kullaniciId): array;

    public function sonDurumAl(int $kullaniciId, string $tip): ?string;
    public function sonDurumKaydet(int $kullaniciId, string $tip, string $deger): void;
}
