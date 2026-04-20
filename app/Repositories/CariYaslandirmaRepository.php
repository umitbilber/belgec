<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\CariYaslandirmaRepositoryInterface;

class CariYaslandirmaRepository extends BaseRepository implements CariYaslandirmaRepositoryInterface
{
    public function getOpenInvoices(?string $tip = null, int $cariId = 0): array
{
    $sql = "
        SELECT
            f.id,
            f.fatura_no,
            f.tip,
            f.tarih,
            f.vade_tarihi,
            f.genel_toplam,
            COALESCE(f.odenen_tutar, 0) AS odenen_tutar,
            (COALESCE(f.genel_toplam, 0) - COALESCE(f.odenen_tutar, 0)) AS acik_tutar,
            m.id AS cari_id,
m.ad_soyad AS cari_adi,
m.eposta AS cari_eposta
        FROM faturalar f
        INNER JOIN musteriler m ON m.id = f.cari_id
        WHERE (COALESCE(f.genel_toplam, 0) - COALESCE(f.odenen_tutar, 0)) > 0
    ";

    $params = [];

    if ($tip !== null && in_array($tip, ['alis', 'satis'], true)) {
        $sql .= " AND f.tip = ? ";
        $params[] = $tip;
    }

    if ($cariId > 0) {
        $sql .= " AND f.cari_id = ? ";
        $params[] = $cariId;
    }

    $sql .= " ORDER BY f.vade_tarihi ASC, f.tarih ASC, f.id ASC ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll() ?: [];
}
}