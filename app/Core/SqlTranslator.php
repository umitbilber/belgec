<?php

declare(strict_types=1);

namespace App\Core;

/**
 * SQLite syntax ile yazilmis SQL'i MySQL'e cevirir.
 * Tum migration'lar ve service'ler SQLite syntax'i ile yazilir,
 * MySQL driver kullaniliyorsa bu sinif SQL'i uyumlu hale getirir.
 */
class SqlTranslator
{
    private string $driver;

    public function __construct(string $driver)
    {
        $this->driver = $driver;
    }

    public function translate(string $sql): string
    {
        if ($this->driver === 'sqlite') {
            return $sql;
        }

        if ($this->driver === 'mysql') {
            return $this->toMysql($sql);
        }

        return $sql;
    }

    private function toMysql(string $sql): string
    {
        // 1) INTEGER PRIMARY KEY AUTOINCREMENT -> INT AUTO_INCREMENT PRIMARY KEY
        $sql = preg_replace(
            '/\bINTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT\b/i',
            'INT AUTO_INCREMENT PRIMARY KEY',
            $sql
        );

        // 2) INTEGER (tek basina) -> INT
        $sql = preg_replace('/\bINTEGER\b/i', 'INT', $sql);

        // 3) REAL -> DECIMAL(15,4)
        $sql = preg_replace('/\bREAL\b/i', 'DECIMAL(15,4)', $sql);

        // 4) TEXT UNIQUE -> VARCHAR(191) UNIQUE (UNIQUE index icin gerekli)
        $sql = preg_replace('/\bTEXT\s+NOT\s+NULL\s+UNIQUE\b/i', 'VARCHAR(191) NOT NULL UNIQUE', $sql);
        $sql = preg_replace('/\bTEXT\s+UNIQUE\b/i', 'VARCHAR(191) UNIQUE', $sql);

        // 4b) TEXT DEFAULT 'xxx' -> VARCHAR(255) DEFAULT 'xxx'
        // MySQL'de TEXT kolonlar DEFAULT degeri kabul etmiyor
        $sql = preg_replace(
            "/\bTEXT\s+DEFAULT\s+('[^']*')/i",
            'VARCHAR(255) DEFAULT $1',
            $sql
        );

        // 5) SQLite'da "CREATE TABLE ... IF NOT EXISTS" MySQL'de de ayni, dokunma
        // 6) DATETIME DEFAULT CURRENT_TIMESTAMP - MySQL'de de gecerli, dokunma
        // 7) FOREIGN KEY - aynen gecerli, ama InnoDB gerekli (charset/engine satirinda bildirilir)

        // 8) CREATE TABLE sonuna ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ekle
        // Sadece "CREATE TABLE ... (...)" bitislerinde ve zaten ENGINE yoksa
        if (stripos($sql, 'CREATE TABLE') !== false && stripos($sql, 'ENGINE=') === false) {
            // Son ")" karakteri ile ";" arasina (veya string sonu) ENGINE ekle
            $sql = preg_replace(
                '/\)\s*(;)?\s*$/',
                ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci$1',
                $sql,
                1
            );
        }

        return $sql;
    }

    /**
     * Bir tablonun kolon isimlerini driver'a gore tek tip listede dondurur.
     * SQLite'ta PRAGMA, MySQL'de information_schema kullanilir.
     *
     * @return string[]
     */
    public function tableColumns(\PDO $db, string $table): array
    {
        if ($this->driver === 'sqlite') {
            $stmt = $db->query("PRAGMA table_info(" . $table . ")");
            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
            return array_map(static fn($r) => (string) $r['name'], $rows);
        }

        if ($this->driver === 'mysql') {
            $stmt = $db->prepare(
                'SELECT COLUMN_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
            );
            $stmt->execute([$table]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_map(static fn($r) => (string) $r['COLUMN_NAME'], $rows);
        }

        return [];
    }

    /**
     * Yil-ay string'i icin SQL fragment dondurur. Rapor ve grafikler icin.
     * Ornek: $translator->yearMonth('tarih') -> "strftime('%Y-%m', tarih)" SQLite'ta
     *                                         -> "DATE_FORMAT(tarih, '%Y-%m')" MySQL'de
     */
    public function yearMonth(string $column): string
    {
        if ($this->driver === 'mysql') {
            return "DATE_FORMAT($column, '%Y-%m')";
        }
        return "strftime('%Y-%m', $column)";
    }

    public function getDriver(): string
    {
        return $this->driver;
    }
}
