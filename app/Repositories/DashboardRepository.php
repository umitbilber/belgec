<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\DashboardRepositoryInterface;

class DashboardRepository extends BaseRepository implements DashboardRepositoryInterface
{
    public function getDailyTahsilat(string $date): float
    {
        $stmt = $this->db->prepare("
            SELECT SUM(tutar) AS toplam
            FROM cari_hareketler
            WHERE islem_tipi = 'tahsilat' AND date(tarih) = ?
        ");
        $stmt->execute([$date]);

        return (float) ($stmt->fetchColumn() ?: 0);
    }

    public function getDailyTediye(string $date): float
    {
        $stmt = $this->db->prepare("
            SELECT SUM(tutar) AS toplam
            FROM cari_hareketler
            WHERE islem_tipi = 'tediye' AND date(tarih) = ?
        ");
        $stmt->execute([$date]);

        return (float) ($stmt->fetchColumn() ?: 0);
    }

    public function getDailyAlis(string $date): float
    {
        $stmt = $this->db->prepare("
            SELECT SUM(genel_toplam) AS toplam
            FROM faturalar
            WHERE tip = 'alis' AND tarih = ?
        ");
        $stmt->execute([$date]);

        return (float) ($stmt->fetchColumn() ?: 0);
    }

    public function getDailySatis(string $date): float
    {
        $stmt = $this->db->prepare("
            SELECT SUM(genel_toplam) AS toplam
            FROM faturalar
            WHERE tip = 'satis' AND tarih = ?
        ");
        $stmt->execute([$date]);

        return (float) ($stmt->fetchColumn() ?: 0);
    }
}