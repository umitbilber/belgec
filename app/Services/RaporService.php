<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Interfaces\RaporServiceInterface;

class RaporService implements RaporServiceInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function aylikFaturaOzeti(string $baslangic, string $bitis): array
    {
        $stmt = $this->db->prepare("
            SELECT
                strftime('%Y-%m', tarih) AS ay,
                tip,
                COUNT(*) AS fatura_adedi,
                SUM(genel_toplam) AS toplam_tutar
            FROM faturalar
            WHERE tarih >= ? AND tarih <= ?
            GROUP BY ay, tip
            ORDER BY ay ASC, tip ASC
        ");
        $stmt->execute([$baslangic, $bitis]);
        $rows = $stmt->fetchAll() ?: [];

        // Ayları grupla
        $aylar = [];
        foreach ($rows as $row) {
            $ay  = $row['ay'];
            $tip = $row['tip'];
            if (!isset($aylar[$ay])) {
                $aylar[$ay] = ['ay' => $ay, 'alis' => 0.0, 'satis' => 0.0, 'alis_adet' => 0, 'satis_adet' => 0];
            }
            if ($tip === 'alis') {
                $aylar[$ay]['alis']       = (float) $row['toplam_tutar'];
                $aylar[$ay]['alis_adet']  = (int)   $row['fatura_adedi'];
            } elseif ($tip === 'satis') {
                $aylar[$ay]['satis']      = (float) $row['toplam_tutar'];
                $aylar[$ay]['satis_adet'] = (int)   $row['fatura_adedi'];
            }
        }

        return array_values($aylar);
    }

    public function enCokSatilanUrunler(string $baslangic, string $bitis, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COALESCE(s.urun_adi, 'Bilinmiyor') AS urun_adi,
                COALESCE(s.stok_kodu, '') AS stok_kodu,
                SUM(fk.miktar) AS toplam_miktar,
                SUM(fk.miktar * fk.birim_fiyat) AS toplam_ciro,
                COUNT(DISTINCT f.id) AS fatura_adedi
            FROM fatura_kalemleri fk
            JOIN faturalar f ON f.id = fk.fatura_id
            LEFT JOIN stoklar s ON s.id = fk.stok_id
            WHERE f.tip = 'satis'
              AND f.tarih >= ? AND f.tarih <= ?
            GROUP BY fk.stok_id
            ORDER BY toplam_miktar DESC
            LIMIT ?
        ");
        $stmt->execute([$baslangic, $bitis, $limit]);
        return $stmt->fetchAll() ?: [];
    }

    public function cariAlisSatisOzeti(string $baslangic, string $bitis): array
    {
        $stmt = $this->db->prepare("
            SELECT
                m.ad_soyad AS cari_adi,
                f.tip,
                COUNT(*) AS fatura_adedi,
                SUM(f.genel_toplam) AS toplam_tutar
            FROM faturalar f
            JOIN musteriler m ON m.id = f.cari_id
            WHERE f.tarih >= ? AND f.tarih <= ?
            GROUP BY f.cari_id, f.tip
            ORDER BY toplam_tutar DESC
        ");
        $stmt->execute([$baslangic, $bitis]);
        $rows = $stmt->fetchAll() ?: [];

        $cariler = [];
        foreach ($rows as $row) {
            $ad  = $row['cari_adi'];
            $tip = $row['tip'];
            if (!isset($cariler[$ad])) {
                $cariler[$ad] = ['cari_adi' => $ad, 'alis' => 0.0, 'satis' => 0.0, 'alis_adet' => 0, 'satis_adet' => 0];
            }
            if ($tip === 'alis') {
                $cariler[$ad]['alis']      = (float) $row['toplam_tutar'];
                $cariler[$ad]['alis_adet'] = (int)   $row['fatura_adedi'];
            } elseif ($tip === 'satis') {
                $cariler[$ad]['satis']      = (float) $row['toplam_tutar'];
                $cariler[$ad]['satis_adet'] = (int)   $row['fatura_adedi'];
            }
        }

        // Toplam tutara göre sırala
        usort($cariler, fn($a, $b) => ($b['alis'] + $b['satis']) <=> ($a['alis'] + $a['satis']));

        return array_values($cariler);
    }

    public function enYuksekBakiyeliCariler(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT ad_soyad, bakiye
            FROM musteriler
            WHERE bakiye != 0
            ORDER BY ABS(bakiye) DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll() ?: [];
    }
}