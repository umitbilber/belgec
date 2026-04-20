<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\TeklifRepositoryInterface;

class TeklifRepository extends BaseRepository implements TeklifRepositoryInterface
{
    public function all(): array
    {
        $sql = "
            SELECT t.*, c.ad_soyad AS cari_adi
            FROM teklifler t
            LEFT JOIN musteriler c ON t.cari_id = c.id
            ORDER BY t.id DESC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, c.ad_soyad AS cari_adi
            FROM teklifler t
            LEFT JOIN musteriler c ON t.cari_id = c.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getItems(int $teklifId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM teklif_kalemleri
            WHERE teklif_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$teklifId]);

        return $stmt->fetchAll();
    }

    public function findCariIdByName(string $name): int|false
    {
        $stmt = $this->db->prepare("SELECT id FROM musteriler WHERE ad_soyad = ?");
        $stmt->execute([$name]);

        return $stmt->fetchColumn();
    }

    public function createCari(string $name): int
    {
        $stmt = $this->db->prepare("INSERT INTO musteriler (ad_soyad, bakiye) VALUES (?, 0)");
        $stmt->execute([$name]);

        return (int) $this->db->lastInsertId();
    }

    public function createOffer(string $teklifNo, int $cariId, string $tarih, string $paraBirimi, string $teklifNotlari = ''): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO teklifler (teklif_no, cari_id, tarih, para_birimi, genel_toplam, teklif_notlari)
            VALUES (?, ?, ?, ?, 0, ?)
        ");
        $stmt->execute([$teklifNo, $cariId, $tarih, $paraBirimi, $teklifNotlari]);

        return (int) $this->db->lastInsertId();
    }

    public function updateOfferHeader(int $id, string $teklifNo, int $cariId, string $tarih, string $paraBirimi, string $teklifNotlari = ''): void
    {
        $stmt = $this->db->prepare("
            UPDATE teklifler
            SET teklif_no = ?, cari_id = ?, tarih = ?, para_birimi = ?, teklif_notlari = ?
            WHERE id = ?
        ");
        $stmt->execute([$teklifNo, $cariId, $tarih, $paraBirimi, $teklifNotlari, $id]);
    }

    public function updateOfferTotal(int $id, float $genelToplam): void
    {
        $stmt = $this->db->prepare("UPDATE teklifler SET genel_toplam = ? WHERE id = ?");
        $stmt->execute([$genelToplam, $id]);
    }

    public function insertItem(
    int $teklifId,
    string $urunAdi,
    string $marka,
    float $miktar,
    float $birimFiyat,
    float $satirToplam,
    string $termin,
    string $paraBirimi
): void {
    $stmt = $this->db->prepare("
        INSERT INTO teklif_kalemleri (
            teklif_id,
            urun_adi,
            marka,
            miktar,
            birim_fiyat,
            satir_toplam,
            termin,
            para_birimi
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $teklifId,
        $urunAdi,
        $marka,
        $miktar,
        $birimFiyat,
        $satirToplam,
        $termin,
        $paraBirimi
    ]);
}

    public function deleteItems(int $teklifId): void
    {
        $stmt = $this->db->prepare("DELETE FROM teklif_kalemleri WHERE teklif_id = ?");
        $stmt->execute([$teklifId]);
    }

    public function deleteOffer(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM teklifler WHERE id = ?");
        $stmt->execute([$id]);
    }
}