<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\CariAktarimRepositoryInterface;

class CariAktarimRepository extends BaseRepository implements CariAktarimRepositoryInterface
{
    public function findCariById(int $cariId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM musteriler
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$cariId]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getInvoiceIdsByCariId(int $cariId): array
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM faturalar
            WHERE cari_id = ?
        ");
        $stmt->execute([$cariId]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
    }

    public function deleteInvoiceItemsByInvoiceIds(array $invoiceIds): void
    {
        if (empty($invoiceIds)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($invoiceIds), '?'));

        $stmt = $this->db->prepare("
            DELETE FROM fatura_kalemleri
            WHERE fatura_id IN ($placeholders)
        ");
        $stmt->execute($invoiceIds);
    }

    public function deleteInvoicesByCariId(int $cariId): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM faturalar
            WHERE cari_id = ?
        ");
        $stmt->execute([$cariId]);
    }

    public function deleteMovementsByCariId(int $cariId): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM cari_hareketler
            WHERE cari_id = ?
        ");
        $stmt->execute([$cariId]);
    }

    public function deleteStockMovementsByCariId(int $cariId): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM stok_hareketler
            WHERE cari_id = ?
        ");
        $stmt->execute([$cariId]);
    }

    public function setCariBalance(int $cariId, float $balance): void
    {
        $stmt = $this->db->prepare("
            UPDATE musteriler
            SET bakiye = ?
            WHERE id = ?
        ");
        $stmt->execute([$balance, $cariId]);
    }

    public function insertInvoice(
        int $cariId,
        string $tip,
        string $faturaNo,
        string $tarih,
        float $genelToplam,
        ?string $vadeTarihi = null
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO faturalar (cari_id, tip, fatura_no, tarih, genel_toplam, vade_tarihi, odenen_tutar)
            VALUES (?, ?, ?, ?, ?, ?, 0)
        ");
        $stmt->execute([
            $cariId,
            $tip,
            $faturaNo,
            $tarih,
            $genelToplam,
            $vadeTarihi,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function insertInvoiceMovement(
        int $cariId,
        string $tip,
        float $tutar,
        string $faturaNo,
        string $tarih
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO cari_hareketler (cari_id, islem_tipi, tutar, aciklama, tarih)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $cariId,
            $tip,
            $tutar,
            $faturaNo,
            $tarih,
        ]);
    }

    public function insertPaymentMovement(
        int $cariId,
        string $tip,
        float $tutar,
        string $aciklama,
        string $tarih
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO cari_hareketler (cari_id, islem_tipi, tutar, aciklama, tarih)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $cariId,
            $tip,
            $tutar,
            $aciklama,
            $tarih,
        ]);
    }

    public function increaseBalance(int $cariId, float $amount): void
    {
        $stmt = $this->db->prepare("
            UPDATE musteriler
            SET bakiye = bakiye + ?
            WHERE id = ?
        ");
        $stmt->execute([$amount, $cariId]);
    }

    public function decreaseBalance(int $cariId, float $amount): void
    {
        $stmt = $this->db->prepare("
            UPDATE musteriler
            SET bakiye = bakiye - ?
            WHERE id = ?
        ");
        $stmt->execute([$amount, $cariId]);
    }

    public function resetAllInvoicePaidAmounts(): void
    {
        $stmt = $this->db->prepare("
            UPDATE faturalar
            SET odenen_tutar = 0
        ");
        $stmt->execute();
    }

    public function getAllPaymentMovements(): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM cari_hareketler
            WHERE islem_tipi IN ('tahsilat', 'tediye')
            ORDER BY datetime(tarih) ASC, id ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function getOpenInvoicesForCariAndType(int $cariId, string $tip): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                fatura_no,
                tip,
                tarih,
                vade_tarihi,
                genel_toplam,
                COALESCE(odenen_tutar, 0) AS odenen_tutar,
                (COALESCE(genel_toplam, 0) - COALESCE(odenen_tutar, 0)) AS acik_tutar
            FROM faturalar
            WHERE cari_id = ?
              AND tip = ?
              AND (COALESCE(genel_toplam, 0) - COALESCE(odenen_tutar, 0)) > 0
            ORDER BY
                CASE
                    WHEN vade_tarihi IS NULL OR vade_tarihi = '' THEN 1
                    ELSE 0
                END ASC,
                date(vade_tarihi) ASC,
                date(tarih) ASC,
                id ASC
        ");
        $stmt->execute([$cariId, $tip]);

        return $stmt->fetchAll() ?: [];
    }

    public function increaseInvoicePaidAmount(int $faturaId, float $amount): void
    {
        $stmt = $this->db->prepare("
            UPDATE faturalar
            SET odenen_tutar = COALESCE(odenen_tutar, 0) + ?
            WHERE id = ?
        ");
        $stmt->execute([$amount, $faturaId]);
    }

    public function clampInvoicePaidAmounts(): void
    {
        $stmt = $this->db->prepare("
            UPDATE faturalar
            SET odenen_tutar = CASE
                WHEN COALESCE(odenen_tutar, 0) < 0 THEN 0
                WHEN COALESCE(odenen_tutar, 0) > COALESCE(genel_toplam, 0) THEN COALESCE(genel_toplam, 0)
                ELSE COALESCE(odenen_tutar, 0)
            END
        ");
        $stmt->execute();
    }
}