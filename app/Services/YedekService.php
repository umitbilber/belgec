<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

/**
 * Yedek alma, geri yukleme, silme islemleri.
 * SQLite icin dosya bazli, MySQL icin SQL dump bazli calisir.
 */
class YedekService
{
    private string $driver;
    private ?string $sqlitePath;
    private string $backupDir;

    public function __construct()
    {
        $config = require BASE_PATH . '/config/database.php';
        $this->driver = (string) ($config['default'] ?? 'sqlite');
        $this->sqlitePath = $config['connections']['sqlite']['database'] ?? null;
        $this->backupDir = BASE_PATH . '/storage/backups';
    }

    // ==================== YEDEK ALMA ====================

    public function manuelYedekAl(): string
    {
        $this->dizinOlustur();

        if ($this->driver === 'sqlite') {
            return $this->sqliteYedekAl();
        }

        if ($this->driver === 'mysql') {
            return $this->mysqlYedekAl();
        }

        throw new RuntimeException('Desteklenmeyen veritabani driver: ' . $this->driver);
    }

    public function otomatikYedekAl(int $maxYedek = 5): void
    {
        $this->dizinOlustur();

        try {
            $yol = $this->manuelYedekAl();
        } catch (\Throwable $e) {
            return; // otomatik yedekte hata sessizce yutulur
        }

        $this->eskiYedekleriSil($maxYedek);

        // Bildirim flag'i
        $flag = BASE_PATH . '/storage/backups/son_otomatik_bildirim.json';
        file_put_contents($flag, json_encode([
            'dosya'  => basename($yol),
            'zaman'  => date('d.m.Y H:i'),
            'okundu' => false,
        ]), LOCK_EX);
    }

    private function sqliteYedekAl(): string
    {
        if ($this->sqlitePath === null || !file_exists($this->sqlitePath)) {
            throw new RuntimeException('SQLite dosyasi bulunamadi.');
        }

        $dosyaAdi = 'belgec_' . date('Y-m-d_H-i-s') . '.sqlite';
        $hedef = $this->backupDir . '/' . $dosyaAdi;

        if (!copy($this->sqlitePath, $hedef)) {
            throw new RuntimeException('Yedek dosyasi olusturulamadi.');
        }

        return $hedef;
    }

    private function mysqlYedekAl(): string
    {
        $dosyaAdi = 'belgec_' . date('Y-m-d_H-i-s') . '.sql';
        $hedef = $this->backupDir . '/' . $dosyaAdi;

        $fp = fopen($hedef, 'w');
        if (!$fp) {
            throw new RuntimeException('Yedek dosyasi acilamadi.');
        }

        try {
            $db = Database::connection();

            // Header
            fwrite($fp, "-- Belgec MySQL Yedek\n");
            fwrite($fp, "-- Tarih: " . date('Y-m-d H:i:s') . "\n");
            fwrite($fp, "-- Driver: mysql\n\n");
            fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\n");
            fwrite($fp, "SET NAMES utf8mb4;\n\n");

            // Tablo listesi
            $tablolar = [];
            $stmt = $db->query('SHOW TABLES');
            foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
                $tablolar[] = $row[0];
            }

            foreach ($tablolar as $tablo) {
                // DROP + CREATE
                fwrite($fp, "-- ----------------------------\n");
                fwrite($fp, "-- Tablo: $tablo\n");
                fwrite($fp, "-- ----------------------------\n");
                fwrite($fp, "DROP TABLE IF EXISTS `$tablo`;\n");

                $createStmt = $db->query("SHOW CREATE TABLE `$tablo`");
                $createRow = $createStmt->fetch(PDO::FETCH_NUM);
                fwrite($fp, $createRow[1] . ";\n\n");

                // Veri
                $dataStmt = $db->query("SELECT * FROM `$tablo`");
                $satirSayisi = 0;

                while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
                    $kolonlar = array_map(fn($k) => "`$k`", array_keys($row));
                    $degerler = array_map(function ($v) use ($db) {
                        if ($v === null) return 'NULL';
                        if (is_int($v) || is_float($v)) return (string) $v;
                        return $db->quote((string) $v);
                    }, array_values($row));

                    fwrite($fp, sprintf(
                        "INSERT INTO `%s` (%s) VALUES (%s);\n",
                        $tablo,
                        implode(', ', $kolonlar),
                        implode(', ', $degerler)
                    ));
                    $satirSayisi++;
                }

                fwrite($fp, "-- $tablo: $satirSayisi satir\n\n");
            }

            fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
        } finally {
            fclose($fp);
        }

        return $hedef;
    }

    // ==================== YEDEK LISTELEME ====================

    public function yedekleriListele(): array
    {
        $this->dizinOlustur();

        $sqlite = glob($this->backupDir . '/belgec_*.sqlite') ?: [];
        $mysql  = glob($this->backupDir . '/belgec_*.sql')    ?: [];
        $dosyalar = array_merge($sqlite, $mysql);

        // En yeni en ustte
        usort($dosyalar, fn($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map(function (string $yol) {
            $adi = basename($yol);
            $tip = str_ends_with($adi, '.sqlite') ? 'sqlite' : 'mysql';
            return [
                'dosya_adi' => $adi,
                'tip'       => $tip,
                'boyut'     => $this->boyutFormatla((int) filesize($yol)),
                'tarih'     => date('d.m.Y H:i:s', (int) filemtime($yol)),
            ];
        }, $dosyalar);
    }

    public function yedekIndir(string $dosyaAdi): string
    {
        $yol = $this->backupDir . '/' . basename($dosyaAdi);

        if (!file_exists($yol) || !str_starts_with(basename($yol), 'belgec_')) {
            throw new RuntimeException('Yedek dosyasi bulunamadi.');
        }

        return $yol;
    }

    public function yedekSil(string $dosyaAdi): void
    {
        $yol = $this->backupDir . '/' . basename($dosyaAdi);

        if (!file_exists($yol) || !str_starts_with(basename($yol), 'belgec_')) {
            throw new RuntimeException('Yedek dosyasi bulunamadi.');
        }

        if (!@unlink($yol)) {
            throw new RuntimeException('Yedek dosyasi silinemedi.');
        }
    }

    // ==================== GERI YUKLEME ====================

    /**
     * Mevcut yedeklerden birini aktif veritabanina geri yukler.
     * Oncesinde mevcut durumu "before_restore_*" prefixi ile yedekler (failsafe).
     */
    public function yedektenGeriYukle(string $dosyaAdi): array
    {
        $yol = $this->backupDir . '/' . basename($dosyaAdi);

        if (!file_exists($yol) || !str_starts_with(basename($yol), 'belgec_')) {
            throw new RuntimeException('Yedek dosyasi bulunamadi.');
        }

        $tip = str_ends_with($yol, '.sqlite') ? 'sqlite' : 'mysql';

        // Aktif driver ile yedek tipi eslesmeli
        if ($tip !== $this->driver) {
            throw new RuntimeException(sprintf(
                'Yedek tipi (%s) aktif veritabani (%s) ile uyumsuz.',
                $tip,
                $this->driver
            ));
        }

        // Once mevcut durumu yedekle (failsafe)
        $failsafe = $this->failsafeYedekAl();

        try {
            if ($tip === 'sqlite') {
                $this->sqliteGeriYukle($yol);
            } else {
                $this->mysqlGeriYukle($yol);
            }

            return [
                'ok'       => true,
                'failsafe' => basename($failsafe),
            ];
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Geri yukleme basarisiz: ' . $e->getMessage() .
                ' | Onceki durum failsafe olarak kaydedildi: ' . basename($failsafe)
            );
        }
    }

    /**
     * Disaridan yuklenen bir yedek dosyasini geri yukler.
     */
    public function yuklenmisYedektenGeriYukle(string $gecicYol, string $orijinalAd): array
    {
        // Dosyayi once backups klasorune kopyala (belgec_* prefix ile)
        $uzanti = pathinfo($orijinalAd, PATHINFO_EXTENSION);
        if (!in_array($uzanti, ['sqlite', 'sql'], true)) {
            throw new RuntimeException('Sadece .sqlite veya .sql uzantili dosyalar kabul edilir.');
        }

        $yeniAd = 'belgec_yuklenen_' . date('Y-m-d_H-i-s') . '.' . $uzanti;
        $hedefYol = $this->backupDir . '/' . $yeniAd;

        if (!move_uploaded_file($gecicYol, $hedefYol)) {
            // bazi durumlarda rename de denenir (test ortami)
            if (!@rename($gecicYol, $hedefYol) && !@copy($gecicYol, $hedefYol)) {
                throw new RuntimeException('Yuklenen dosya kaydedilemedi.');
            }
        }

        return $this->yedektenGeriYukle($yeniAd);
    }

    private function sqliteGeriYukle(string $yedekDosya): void
    {
        if ($this->sqlitePath === null) {
            throw new RuntimeException('SQLite yolu tanimsiz.');
        }

        // PDO baglantisini kapat ki dosya kilitli olmasin
        Database::reset();

        if (!copy($yedekDosya, $this->sqlitePath)) {
            throw new RuntimeException('SQLite dosyasi yazilamadi.');
        }
    }

    private function mysqlGeriYukle(string $yedekDosya): void
    {
        $icerik = file_get_contents($yedekDosya);
        if ($icerik === false || $icerik === '') {
            throw new RuntimeException('Yedek dosyasi okunamadi veya bos.');
        }

        $db = Database::connection();

        // SQL dosyasini statement'lara ayir (; ile biten, string literal'lerde kesilmez)
        $statements = $this->sqlStatementParsele($icerik);

        $db->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '' || str_starts_with($stmt, '--')) continue;
                $db->exec($stmt);
            }
        } finally {
            $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * Basit SQL statement parser. ; ile ayirir ama string literal'ler ('...') icindeki
     * ; karakterlerini gormezden gelir.
     */
    private function sqlStatementParsele(string $sql): array
    {
        $statements = [];
        $current = '';
        $stringIci = false;
        $escape = false;
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];

            if ($escape) {
                $current .= $ch;
                $escape = false;
                continue;
            }

            if ($ch === '\\') {
                $current .= $ch;
                $escape = true;
                continue;
            }

            if ($ch === "'") {
                $stringIci = !$stringIci;
                $current .= $ch;
                continue;
            }

            if ($ch === ';' && !$stringIci) {
                $statements[] = $current;
                $current = '';
                continue;
            }

            $current .= $ch;
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }

    private function failsafeYedekAl(): string
    {
        $this->dizinOlustur();

        if ($this->driver === 'sqlite') {
            $dosyaAdi = 'belgec_failsafe_' . date('Y-m-d_H-i-s') . '.sqlite';
            $hedef = $this->backupDir . '/' . $dosyaAdi;
            if (!copy($this->sqlitePath, $hedef)) {
                throw new RuntimeException('Failsafe yedek alinamadi.');
            }
            return $hedef;
        }

        // mysql
        $yol = $this->mysqlYedekAl();
        // dosya adina failsafe prefix ekleyip rename et
        $yeniAd = str_replace('belgec_', 'belgec_failsafe_', basename($yol));
        $yeniYol = dirname($yol) . '/' . $yeniAd;
        rename($yol, $yeniYol);
        return $yeniYol;
    }

    // ==================== ZAMAN KONTROLU ====================

    public function zamanKontrol(string $siklık, string $sonYedekZamani): bool
    {
        if ($sonYedekZamani === '') return true;

        $son  = strtotime($sonYedekZamani);
        $simdi = time();

        return match ($siklık) {
            'gunluk'  => ($simdi - $son) >= 86400,
            'haftalik' => ($simdi - $son) >= 604800,
            'aylik'   => ($simdi - $son) >= 2592000,
            default   => false,
        };
    }

    // ==================== YARDIMCI ====================

    private function eskiYedekleriSil(int $maxYedek): void
    {
        $sqlite = glob($this->backupDir . '/belgec_*.sqlite') ?: [];
        $mysql  = glob($this->backupDir . '/belgec_*.sql')    ?: [];
        $dosyalar = array_merge($sqlite, $mysql);

        // failsafe ve yuklenen dosyalari sayma
        $dosyalar = array_filter($dosyalar, function ($y) {
            $b = basename($y);
            return !str_contains($b, 'failsafe') && !str_contains($b, 'yuklenen');
        });

        usort($dosyalar, fn($a, $b) => filemtime($b) <=> filemtime($a));

        $silinecekler = array_slice($dosyalar, $maxYedek);
        foreach ($silinecekler as $dosya) {
            @unlink($dosya);
        }
    }

    private function dizinOlustur(): void
    {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    private function boyutFormatla(int $bayt): string
    {
        if ($bayt >= 1048576) return round($bayt / 1048576, 1) . ' MB';
        if ($bayt >= 1024)    return round($bayt / 1024, 1) . ' KB';
        return $bayt . ' B';
    }
}
