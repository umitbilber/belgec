<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\StokHareketRepositoryInterface;

class StokHareketRepository extends BaseRepository implements StokHareketRepositoryInterface
{
    public function getFiltered(array $filters): array
    {
        $sql = "
            SELECT
                sh.*,
                s.stok_kodu,
                s.urun_adi,
                s.birim,
                m.ad_soyad AS cari_adi
            FROM stok_hareketler sh
            LEFT JOIN stoklar s ON s.id = sh.stok_id
            LEFT JOIN musteriler m ON m.id = sh.cari_id
            WHERE 1 = 1
        ";

        $params = [];

        if (!empty($filters['stok_id'])) {
            $sql .= " AND sh.stok_id = ? ";
            $params[] = (int) $filters['stok_id'];
        }

        if (!empty($filters['islem_tipi'])) {
            $sql .= " AND sh.islem_tipi = ? ";
            $params[] = $filters['islem_tipi'];
        }

        if (!empty($filters['tarih_baslangic'])) {
            $sql .= " AND date(sh.tarih) >= ? ";
            $params[] = $filters['tarih_baslangic'];
        }

        if (!empty($filters['tarih_bitis'])) {
            $sql .= " AND date(sh.tarih) <= ? ";
            $params[] = $filters['tarih_bitis'];
        }

        $sql .= " ORDER BY date(sh.tarih) DESC, sh.id DESC ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }
}