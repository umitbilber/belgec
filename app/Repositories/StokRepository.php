<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\StokRepositoryInterface;

class StokRepository extends BaseRepository implements StokRepositoryInterface
{
    public function all(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM stoklar ORDER BY id DESC");
$stmt->execute();
return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM stoklar WHERE id = ?");
        $stmt->execute([$id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function hasLinkedRecords(int $stokId): bool
    {
        $checks = [
            "SELECT COUNT(*) FROM fatura_kalemleri WHERE stok_id = ?",
            "SELECT COUNT(*) FROM stok_hareketler WHERE stok_id = ?",
        ];

        foreach ($checks as $sql) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$stokId]);

            if ((int) $stmt->fetchColumn() > 0) {
                return true;
            }
        }

        return false;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO stoklar (urun_adi, stok_kodu, birim, stok_miktari)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['urun_adi'],
            $data['stok_kodu'],
            $data['birim'],
            $data['stok_miktari'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE stoklar
            SET urun_adi = ?, stok_kodu = ?, birim = ?, stok_miktari = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $data['urun_adi'],
            $data['stok_kodu'],
            $data['birim'],
            $data['stok_miktari'],
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM stoklar WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    public function findByKodOrAd(string $stokKodu, string $urunAdi): ?array
{
    if ($stokKodu !== '') {
        $stmt = $this->db->prepare("SELECT * FROM stoklar WHERE stok_kodu = ? LIMIT 1");
        $stmt->execute([$stokKodu]);
        $row = $stmt->fetch();
        if ($row) return $row;
    }

    if ($urunAdi !== '') {
        $stmt = $this->db->prepare("SELECT * FROM stoklar WHERE urun_adi = ? LIMIT 1");
        $stmt->execute([$urunAdi]);
        $row = $stmt->fetch();
        if ($row) return $row;
    }

    return null;
}

public function getFiyatGecmisi(int $stokId): array
{
    $stmt = $this->db->prepare("
        SELECT
            f.tarih,
            f.fatura_no,
            f.tip,
            m.ad_soyad AS cari_adi,
            fk.miktar,
            fk.birim_fiyat,
            fk.kdv_orani
        FROM fatura_kalemleri fk
        JOIN faturalar f ON f.id = fk.fatura_id
        LEFT JOIN musteriler m ON m.id = f.cari_id
        WHERE fk.stok_id = ?
        ORDER BY f.tarih DESC, f.id DESC
        LIMIT 30
    ");
    $stmt->execute([$stokId]);
    return $stmt->fetchAll() ?: [];
}

    public function deleteMovementsByStokId(int $stokId): void
    {
        $stmt = $this->db->prepare("DELETE FROM stok_hareketler WHERE stok_id = ?");
        $stmt->execute([$stokId]);
    }

    public function insertMovement(array $data): void
{
    $stmt = $this->db->prepare("
        INSERT INTO stok_hareketler (stok_id, islem_tipi, miktar, aciklama, tarih)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['stok_id'],
        $data['islem_tipi'],
        $data['miktar'],
        $data['aciklama'],
        $data['tarih'] ?? date('Y-m-d'),
    ]);
}

    public function getMovementsByStokId(int $stokId): array
{
    $stmt = $this->db->prepare("
        SELECT
            sh.*,
            COALESCE(sh.tarih, f.tarih) AS hareket_tarihi,
            m.ad_soyad AS cari_adi,
            f.id AS fatura_id,
            f.fatura_no,
            f.tip AS fatura_tipi
        FROM stok_hareketler sh
        LEFT JOIN faturalar f
            ON f.fatura_no = sh.fatura_no
            AND (
                (LOWER(sh.islem_tipi) = 'alis' AND f.tip = 'alis')
                OR
                (LOWER(sh.islem_tipi) = 'satis' AND f.tip = 'satis')
            )
        LEFT JOIN musteriler m
            ON m.id = sh.cari_id
        WHERE sh.stok_id = ?
        ORDER BY date(COALESCE(sh.tarih, f.tarih)) DESC, sh.id DESC
    ");
    $stmt->execute([$stokId]);

    return $stmt->fetchAll();
}
}