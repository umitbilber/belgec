<?php

declare(strict_types=1);

namespace App\Interfaces;

interface PushServiceInterface
{
    /**
     * Browser'in verdigi push subscription bilgisini DB'ye kaydeder.
     * Ayni endpoint_hash tekrar gelirse son_goruldu tarihini gunceller (upsert).
     */
    public function aboneOl(
        int $kullaniciId,
        string $endpoint,
        string $p256dh,
        string $auth,
        ?string $userAgent = null
    ): void;

    /**
     * Belirli bir endpoint aboneligini siler (kullanici browser'dan izni geri aldiginda).
     */
    public function aboneCik(string $endpoint): void;

    /**
     * Kullanicinin tum cihazlarina push bildirimi gonderir.
     * Gecersiz abonelikler (410 Gone, 404) otomatik temizlenir.
     *
     * @return int Basarili gonderilen cihaz sayisi
     */
    public function gonder(int $kullaniciId, string $baslik, string $metin, ?string $url = null): int;

    /**
     * VAPID public key'i dondurur. Yoksa yeni anahtar cifti uretip kaydeder.
     * Frontend bu key'i kullanarak subscribe isteginde bulunur.
     */
    public function vapidPublicKey(): string;

    /**
     * Bir kullanici icin belirli bir bildirim tipinin son bildirilen degerini getirir.
     * Duplicate push gonderimini engellemek icin BildirimController kullanir.
     */
    public function sonDurumAl(int $kullaniciId, string $tip): ?string;

    /**
     * Bir kullanici icin belirli bir bildirim tipinin son bildirilen degerini gunceller.
     */
    public function sonDurumKaydet(int $kullaniciId, string $tip, string $deger): void;
}
