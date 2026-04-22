<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\TranslatedPdo;

abstract class BaseFaturaRepository
{
    protected TranslatedPdo $db;

    public function __construct()
    {
        $this->db = Database::translatedConnection();
    }

    public function all(): array
    {
        $stmt = $this->db->prepare("
            SELECT f.*, c.ad_soyad AS cari_adi
            FROM faturalar f
            LEFT JOIN musteriler c ON f.cari_id = c.id
            WHERE f.tip = ?
            ORDER BY f.id DESC
        ");
        $stmt->execute([$this->getInvoiceType()]);

        return $stmt->fetchAll();
    }
    public function getFiltered(array $filters): array
{
    $sql = "
        SELECT f.*, c.ad_soyad AS cari_adi
        FROM faturalar f
        LEFT JOIN musteriler c ON f.cari_id = c.id
        WHERE f.tip = ?
    ";
    $params = [$this->getInvoiceType()];

    if (!empty($filters['cari_id'])) {
        $sql .= " AND f.cari_id = ? ";
        $params[] = (int) $filters['cari_id'];
    }

    if (!empty($filters['tarih_baslangic'])) {
        $sql .= " AND date(f.tarih) >= ? ";
        $params[] = $filters['tarih_baslangic'];
    }

    if (!empty($filters['tarih_bitis'])) {
        $sql .= " AND date(f.tarih) <= ? ";
        $params[] = $filters['tarih_bitis'];
    }

    if (!empty($filters['tutar_min'])) {
        $sql .= " AND f.genel_toplam >= ? ";
        $params[] = (float) $filters['tutar_min'];
    }

    if (!empty($filters['tutar_max'])) {
        $sql .= " AND f.genel_toplam <= ? ";
        $params[] = (float) $filters['tutar_max'];
    }

    if (!empty($filters['fatura_no'])) {
        $sql .= " AND f.fatura_no LIKE ? ";
        $params[] = '%' . $filters['fatura_no'] . '%';
    }

    $sql .= " ORDER BY f.id DESC ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}

    public function findInvoiceById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM faturalar
            WHERE id = ? AND tip = ?
        ");
        $stmt->execute([$id, $this->getInvoiceType()]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findInvoiceWithCariName(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT f.*, c.ad_soyad AS cari_adi
            FROM faturalar f
            LEFT JOIN musteriler c ON f.cari_id = c.id
            WHERE f.id = ? AND f.tip = ?
        ");
        $stmt->execute([$id, $this->getInvoiceType()]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getInvoiceItems(int $faturaId): array
    {
        $stmt = $this->db->prepare("
            SELECT fk.*, s.urun_adi, s.stok_kodu
            FROM fatura_kalemleri fk
            LEFT JOIN stoklar s ON fk.stok_id = s.id
            WHERE fk.fatura_id = ?
            ORDER BY fk.id ASC
        ");
        $stmt->execute([$faturaId]);

        return $stmt->fetchAll();
    }

    public function getInvoiceItemsRaw(int $faturaId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM fatura_kalemleri
            WHERE fatura_id = ?
        ");
        $stmt->execute([$faturaId]);

        return $stmt->fetchAll();
    }

    public function findCariIdByName(string $name): int|false
    {
        $stmt = $this->db->prepare("SELECT id FROM musteriler WHERE ad_soyad = ?");
        $stmt->execute([$name]);

        $id = $stmt->fetchColumn();
        return $id === false ? false : (int) $id;
    }
    
    public function findCariById(int $id): ?array
{
    $stmt = $this->db->prepare("
        SELECT *
        FROM musteriler
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);

    $row = $stmt->fetch();
    return $row ?: null;
}

    public function createCari(string $name): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO musteriler (ad_soyad, bakiye)
            VALUES (?, 0)
        ");
        $stmt->execute([$name]);

        return (int) $this->db->lastInsertId();
    }

    public function findStockId(string $urunAdi, string $stokKodu): int|false
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM stoklar
            WHERE urun_adi = ? OR (stok_kodu = ? AND stok_kodu != '')
        ");
        $stmt->execute([$urunAdi, $stokKodu]);

        $id = $stmt->fetchColumn();
        return $id === false ? false : (int) $id;
    }

    public function createStock(string $urunAdi, string $stokKodu): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO stoklar (urun_adi, stok_kodu, birim, stok_miktari)
            VALUES (?, ?, 'Adet', 0)
        ");
        $stmt->execute([$urunAdi, $stokKodu]);

        return (int) $this->db->lastInsertId();
    }

    public function createInvoice(string $faturaNo, int $cariId, string $tarih, ?string $vadeTarihi = null): int
{
    $stmt = $this->db->prepare("
        INSERT INTO faturalar (fatura_no, cari_id, tip, tarih, vade_tarihi)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$faturaNo, $cariId, $this->getInvoiceType(), $tarih, $vadeTarihi]);

    return (int) $this->db->lastInsertId();
}

    public function updateInvoiceHeader(int $faturaId, string $faturaNo, int $cariId, string $tarih, ?string $vadeTarihi = null): void
{
    $stmt = $this->db->prepare("
        UPDATE faturalar
        SET cari_id = ?, fatura_no = ?, tarih = ?, vade_tarihi = ?, genel_toplam = 0
        WHERE id = ? AND tip = ?
    ");
    $stmt->execute([$cariId, $faturaNo, $tarih, $vadeTarihi, $faturaId, $this->getInvoiceType()]);
}

    public function updateInvoiceTotal(int $faturaId, float $genelToplam): void
{
    $stmt = $this->db->prepare("
        UPDATE faturalar
        SET
            genel_toplam = ?,
            odenen_tutar = CASE
                WHEN COALESCE(odenen_tutar, 0) > ? THEN ?
                WHEN COALESCE(odenen_tutar, 0) < 0 THEN 0
                ELSE COALESCE(odenen_tutar, 0)
            END
        WHERE id = ?
    ");
    $stmt->execute([$genelToplam, $genelToplam, $genelToplam, $faturaId]);
}

    public function insertInvoiceItem(int $faturaId, int $stokId, float $miktar, float $birimFiyat, int $kdvOrani): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO fatura_kalemleri (fatura_id, stok_id, miktar, birim_fiyat, kdv_orani)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$faturaId, $stokId, $miktar, $birimFiyat, $kdvOrani]);
    }

    public function deleteInvoiceItems(int $faturaId): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM fatura_kalemleri
            WHERE fatura_id = ?
        ");
        $stmt->execute([$faturaId]);
    }

    public function deleteInvoice(int $faturaId): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM faturalar
            WHERE id = ? AND tip = ?
        ");
        $stmt->execute([$faturaId, $this->getInvoiceType()]);
    }

    public function increaseStock(int $stokId, float $miktar): void
    {
        $stmt = $this->db->prepare("
            UPDATE stoklar
            SET stok_miktari = stok_miktari + ?
            WHERE id = ?
        ");
        $stmt->execute([$miktar, $stokId]);
    }

    public function decreaseStock(int $stokId, float $miktar): void
    {
        $stmt = $this->db->prepare("
            UPDATE stoklar
            SET stok_miktari = stok_miktari - ?
            WHERE id = ?
        ");
        $stmt->execute([$miktar, $stokId]);
    }

    public function insertStockMovement(int $stokId, int $cariId, string $faturaNo, float $miktar, float $birimFiyat): void
{
    $stmt = $this->db->prepare("
        INSERT INTO stok_hareketler (stok_id, cari_id, fatura_no, islem_tipi, miktar, birim_fiyat, aciklama, tarih)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $stokId,
        $cariId,
        $faturaNo,
        $this->getInvoiceType(),
        $this->normalizeStockMovementAmount($miktar),
        $birimFiyat,
        $this->getStockMovementDescription(),
        date('Y-m-d'),
    ]);
}

    public function deleteStockMovementsByInvoiceNo(string $faturaNo): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM stok_hareketler
            WHERE fatura_no = ? AND islem_tipi = ?
        ");
        $stmt->execute([$faturaNo, $this->getInvoiceType()]);
    }

    public function deleteStockMovementByStockIdAndInvoiceNo(int $stokId, string $faturaNo): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM stok_hareketler
            WHERE stok_id = ? AND fatura_no = ? AND islem_tipi = ?
        ");
        $stmt->execute([$stokId, $faturaNo, $this->getInvoiceType()]);
    }

    public function increaseCariBalance(int $cariId, float $amount): void
    {
        $stmt = $this->db->prepare("
            UPDATE musteriler
            SET bakiye = bakiye + ?
            WHERE id = ?
        ");
        $stmt->execute([$amount, $cariId]);
    }

    public function decreaseCariBalance(int $cariId, float $amount): void
    {
        $stmt = $this->db->prepare("
            UPDATE musteriler
            SET bakiye = bakiye - ?
            WHERE id = ?
        ");
        $stmt->execute([$amount, $cariId]);
    }

    public function insertCariMovement(int $cariId, float $tutar, string $aciklama): void
{
    $stmt = $this->db->prepare("
        INSERT INTO cari_hareketler (cari_id, islem_tipi, tutar, aciklama, tarih)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $cariId,
        $this->getInvoiceType(),
        $tutar,
        $aciklama,
        date('Y-m-d'),
    ]);
}

    public function deleteCariMovementLikeInvoiceNo(int $cariId, string $faturaNo): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM cari_hareketler
            WHERE cari_id = ? AND islem_tipi = ? AND aciklama = ?
        ");
        $stmt->execute([$cariId, $this->getInvoiceType(), $faturaNo]);
    }

    public function beginTransaction(): void
    {
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    protected function normalizeStockMovementAmount(float $miktar): float
    {
        return $miktar;
    }

    abstract protected function getInvoiceType(): string;
    abstract protected function getStockMovementDescription(): string;
}
