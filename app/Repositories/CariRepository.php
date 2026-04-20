<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\CariRepositoryInterface;

class CariRepository extends BaseRepository implements CariRepositoryInterface
{
    public function all(): array
    {
        $sql = "SELECT * FROM musteriler ORDER BY id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM musteriler WHERE id = ?");
        $stmt->execute([$id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function hasLinkedRecords(int $cariId): bool
    {
        $checks = [
            "SELECT COUNT(*) FROM faturalar WHERE cari_id = ?",
            "SELECT COUNT(*) FROM teklifler WHERE cari_id = ?",
            "SELECT COUNT(*) FROM stok_hareketler WHERE cari_id = ?",
        ];

        foreach ($checks as $sql) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$cariId]);

            if ((int) $stmt->fetchColumn() > 0) {
                return true;
            }
        }

        return false;
    }

    public function create(array $data): int
{
    $stmt = $this->db->prepare("
        INSERT INTO musteriler (ad_soyad, telefon, eposta, adres, vergi_no, bakiye, varsayilan_vade_gun)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['ad_soyad'],
        $data['telefon'],
        $data['eposta'],
        $data['adres'],
        $data['vergi_no'],
        $data['bakiye'],
        $data['varsayilan_vade_gun'],
    ]);

    return (int) $this->db->lastInsertId();
}

    public function update(int $id, array $data): void
{
    $stmt = $this->db->prepare("
        UPDATE musteriler
        SET ad_soyad = ?, telefon = ?, eposta = ?, adres = ?, vergi_no = ?, varsayilan_vade_gun = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $data['ad_soyad'],
        $data['telefon'],
        $data['eposta'],
        $data['adres'],
        $data['vergi_no'],
        $data['varsayilan_vade_gun'],
        $id,
    ]);
}

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM musteriler WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function deleteMovementsByCariId(int $cariId): void
    {
        $stmt = $this->db->prepare("DELETE FROM cari_hareketler WHERE cari_id = ?");
        $stmt->execute([$cariId]);
    }

    public function insertMovement(array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO cari_hareketler (cari_id, islem_tipi, tutar, aciklama, tarih)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['cari_id'],
            $data['islem_tipi'],
            $data['tutar'],
            $data['aciklama'],
            $data['tarih'],
        ]);
    }

    public function decreaseBalance(int $cariId, float $amount): void
    {
        $stmt = $this->db->prepare("UPDATE musteriler SET bakiye = bakiye - ? WHERE id = ?");
        $stmt->execute([$amount, $cariId]);
    }

    public function increaseBalance(int $cariId, float $amount): void
    {
        $stmt = $this->db->prepare("UPDATE musteriler SET bakiye = bakiye + ? WHERE id = ?");
        $stmt->execute([$amount, $cariId]);
    }

    public function setBalance(int $cariId, float $amount): void
    {
        $stmt = $this->db->prepare("UPDATE musteriler SET bakiye = ? WHERE id = ?");
        $stmt->execute([$amount, $cariId]);
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

public function resetAllInvoicePaidAmounts(): void
{
    $stmt = $this->db->prepare("UPDATE faturalar SET odenen_tutar = 0");
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

    public function getMovementsByCariId(int $cariId): array
{
    $stmt = $this->db->prepare("
        SELECT
            ch.*,
            f.id AS fatura_id,
            f.fatura_no,
            f.tip AS fatura_tipi,
            COALESCE(ch.tarih, f.tarih) AS hareket_tarihi,
            COALESCE(ch.tarih, f.tarih || ' 00:00:00') AS siralama_tarihi
        FROM cari_hareketler ch
        LEFT JOIN faturalar f
            ON f.fatura_no = ch.aciklama
            AND f.cari_id = ch.cari_id
            AND (
                (LOWER(ch.islem_tipi) = 'alis' AND f.tip = 'alis')
                OR
                (LOWER(ch.islem_tipi) = 'satis' AND f.tip = 'satis')
            )
        WHERE ch.cari_id = ?
        ORDER BY datetime(COALESCE(ch.tarih, f.tarih || ' 00:00:00')) ASC, ch.id ASC
    ");
    $stmt->execute([$cariId]);

    return $stmt->fetchAll();
}
}