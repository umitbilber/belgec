<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\PushRepositoryInterface;

class PushRepository extends BaseRepository implements PushRepositoryInterface
{
    public function upsertAbonelik(
        int $kullaniciId,
        string $endpoint,
        string $p256dh,
        string $auth,
        ?string $userAgent
    ): void {
        $endpointHash = hash('sha256', $endpoint);

        $stmt = $this->db->prepare('SELECT id FROM push_abonelikleri WHERE endpoint_hash = ?');
        $stmt->execute([$endpointHash]);
        $mevcutId = $stmt->fetchColumn();

        if ($mevcutId !== false) {
            $stmt = $this->db->prepare(
                'UPDATE push_abonelikleri
                 SET kullanici_id = ?, p256dh = ?, auth = ?, user_agent = ?, son_goruldu = CURRENT_TIMESTAMP
                 WHERE id = ?'
            );
            $stmt->execute([$kullaniciId, $p256dh, $auth, $userAgent, (int) $mevcutId]);
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO push_abonelikleri (kullanici_id, endpoint_hash, endpoint, p256dh, auth, user_agent)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$kullaniciId, $endpointHash, $endpoint, $p256dh, $auth, $userAgent]);
    }

    public function silEndpointIle(string $endpoint): void
    {
        $endpointHash = hash('sha256', $endpoint);
        $stmt = $this->db->prepare('DELETE FROM push_abonelikleri WHERE endpoint_hash = ?');
        $stmt->execute([$endpointHash]);
    }

    public function silId(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM push_abonelikleri WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function kullaniciAbonelikleri(int $kullaniciId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, endpoint, p256dh, auth FROM push_abonelikleri WHERE kullanici_id = ?'
        );
        $stmt->execute([$kullaniciId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function sonDurumAl(int $kullaniciId, string $tip): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT son_deger FROM push_son_durumlar WHERE kullanici_id = ? AND tip = ?'
        );
        $stmt->execute([$kullaniciId, $tip]);
        $deger = $stmt->fetchColumn();

        return $deger === false ? null : (string) $deger;
    }

    public function sonDurumKaydet(int $kullaniciId, string $tip, string $deger): void
    {
        $mevcut = $this->sonDurumAl($kullaniciId, $tip);

        if ($mevcut === null) {
            $stmt = $this->db->prepare(
                'INSERT INTO push_son_durumlar (kullanici_id, tip, son_deger) VALUES (?, ?, ?)'
            );
            $stmt->execute([$kullaniciId, $tip, $deger]);
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE push_son_durumlar
             SET son_deger = ?, guncelleme_tarihi = CURRENT_TIMESTAMP
             WHERE kullanici_id = ? AND tip = ?'
        );
        $stmt->execute([$deger, $kullaniciId, $tip]);
    }
}
