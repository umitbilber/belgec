<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\CariHareketRepositoryInterface;

class CariHareketRepository extends BaseRepository implements CariHareketRepositoryInterface
{
    public function getFiltered(array $filters): array
    {
        $sql = "
            SELECT
                ch.*,
                m.ad_soyad AS cari_adi
            FROM cari_hareketler ch
            INNER JOIN musteriler m ON m.id = ch.cari_id
            WHERE 1 = 1
        ";

        $params = [];

        if (!empty($filters['cari_id'])) {
            $sql .= " AND ch.cari_id = ? ";
            $params[] = (int) $filters['cari_id'];
        }

        if (!empty($filters['islem_tipi'])) {
            $sql .= " AND ch.islem_tipi = ? ";
            $params[] = $filters['islem_tipi'];
        }

        if (!empty($filters['tarih_baslangic'])) {
            $sql .= " AND date(ch.tarih) >= ? ";
            $params[] = $filters['tarih_baslangic'];
        }

        if (!empty($filters['tarih_bitis'])) {
            $sql .= " AND date(ch.tarih) <= ? ";
            $params[] = $filters['tarih_bitis'];
        }

        $sql .= " ORDER BY datetime(ch.tarih) DESC, ch.id DESC ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }
    public function findById(int $id): ?array
{
    $stmt = $this->db->prepare("
        SELECT *
        FROM cari_hareketler
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);

    $row = $stmt->fetch();
    return $row ?: null;
}

public function updateMovement(int $id, array $data): void
{
    $stmt = $this->db->prepare("
        UPDATE cari_hareketler
        SET cari_id = ?, islem_tipi = ?, tutar = ?, aciklama = ?, tarih = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['cari_id'],
        $data['islem_tipi'],
        $data['tutar'],
        $data['aciklama'],
        $data['tarih'],
        $id,
    ]);
}

public function deleteMovement(int $id): void
{
    $stmt = $this->db->prepare("
        DELETE FROM cari_hareketler
        WHERE id = ?
    ");
    $stmt->execute([$id]);
}
}