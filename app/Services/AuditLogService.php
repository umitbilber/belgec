<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class AuditLogService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function log(
        string $islem,
        string $modul,
        ?int   $kayitId = null,
        ?string $aciklama = null
    ): void {
        try {
            $kullaniciAdi = $_SESSION['kullanici_adi'] ?? 'yonetici';
            $ip           = $_SERVER['REMOTE_ADDR'] ?? null;

            $stmt = $this->db->prepare("
                INSERT INTO audit_log (kullanici_adi, islem, modul, kayit_id, aciklama, ip)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$kullaniciAdi, $islem, $modul, $kayitId, $aciklama, $ip]);
        } catch (\Throwable) {
            // Log hatası asla ana işlemi engellemesin
        }
    }

    public function getFiltered(array $filters): array
    {
        $sql    = "SELECT * FROM audit_log WHERE 1=1 ";
        $params = [];

        if (!empty($filters['kullanici_adi'])) {
            $sql     .= " AND kullanici_adi = ? ";
            $params[] = $filters['kullanici_adi'];
        }

        if (!empty($filters['modul'])) {
            $sql     .= " AND modul = ? ";
            $params[] = $filters['modul'];
        }

        if (!empty($filters['islem'])) {
            $sql     .= " AND islem = ? ";
            $params[] = $filters['islem'];
        }

        if (!empty($filters['tarih_baslangic'])) {
            $sql     .= " AND date(tarih) >= ? ";
            $params[] = $filters['tarih_baslangic'];
        }

        if (!empty($filters['tarih_bitis'])) {
            $sql     .= " AND date(tarih) <= ? ";
            $params[] = $filters['tarih_bitis'];
        }

        $sql .= " ORDER BY id DESC LIMIT 500 ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public function getKullanicilar(): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT kullanici_adi FROM audit_log ORDER BY kullanici_adi ASC");
        $stmt->execute();
        return array_column($stmt->fetchAll() ?: [], 'kullanici_adi');
    }

    public function getModuller(): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT modul FROM audit_log ORDER BY modul ASC");
        $stmt->execute();
        return array_column($stmt->fetchAll() ?: [], 'modul');
    }
}