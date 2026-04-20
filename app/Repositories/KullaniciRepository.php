<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\KullaniciRepositoryInterface;

class KullaniciRepository extends BaseRepository implements KullaniciRepositoryInterface
{
    public function all(): array
    {
        $stmt = $this->db->prepare("SELECT id, ad, kullanici_adi, rol, aktif, olusturma_tarihi FROM kullanicilar ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM kullanicilar WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByKullaniciAdi(string $kullaniciAdi): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = ? AND aktif = 1 LIMIT 1");
        $stmt->execute([$kullaniciAdi]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO kullanicilar (ad, kullanici_adi, sifre_hash, rol, aktif)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['ad'],
            $data['kullanici_adi'],
            $data['sifre_hash'],
            $data['rol'],
            $data['aktif'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE kullanicilar
            SET ad = ?, kullanici_adi = ?, rol = ?, aktif = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['ad'],
            $data['kullanici_adi'],
            $data['rol'],
            $data['aktif'] ?? 1,
            $id,
        ]);

        if (!empty($data['sifre_hash'])) {
            $stmt2 = $this->db->prepare("UPDATE kullanicilar SET sifre_hash = ? WHERE id = ?");
            $stmt2->execute([$data['sifre_hash'], $id]);
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM kullanicilar WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getIzinler(int $kullaniciId): array
    {
        $stmt = $this->db->prepare("SELECT izin FROM kullanici_izinler WHERE kullanici_id = ?");
        $stmt->execute([$kullaniciId]);
        return array_column($stmt->fetchAll() ?: [], 'izin');
    }

    public function setIzinler(int $kullaniciId, array $izinler): void
    {
        $this->db->prepare("DELETE FROM kullanici_izinler WHERE kullanici_id = ?")->execute([$kullaniciId]);

        $stmt = $this->db->prepare("INSERT INTO kullanici_izinler (kullanici_id, izin) VALUES (?, ?)");
        foreach ($izinler as $izin) {
            $stmt->execute([$kullaniciId, $izin]);
        }
    }
}