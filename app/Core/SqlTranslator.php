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

        // 2) TEXT DEFAULT 'xxx' ve NOT NULL DEFAULT 'xxx' patterni -> VARCHAR(255)
        // MySQL'de TEXT kolonlar DEFAULT degeri kabul etmiyor. Bu yuzden TEXT DEFAULT yerine VARCHAR.
        // Once bu 4 ozel pattern'i yakala:
        $sql = preg_replace(
            "/\bTEXT\s+NOT\s+NULL\s+DEFAULT\s+('[^']*')/i",
            'VARCHAR(255) NOT NULL DEFAULT $1',
            $sql
        );
        $sql = preg_replace(
            "/\bTEXT\s+DEFAULT\s+('[^']*')\s+NOT\s+NULL/i",
            'VARCHAR(255) DEFAULT $1 NOT NULL',
            $sql
        );
        $sql = preg_replace(
            "/\bTEXT\s+DEFAULT\s+('[^']*')/i",
            'VARCHAR(255) DEFAULT $1',
            $sql
        );

        // 3) TEXT NOT NULL UNIQUE -> VARCHAR(191) NOT NULL UNIQUE (utf8mb4 index limit)
        $sql = preg_replace('/\bTEXT\s+NOT\s+NULL\s+UNIQUE\b/i', 'VARCHAR(191) NOT NULL UNIQUE', $sql);
        $sql = preg_replace('/\bTEXT\s+UNIQUE\b/i', 'VARCHAR(191) UNIQUE', $sql);

        // 4) INTEGER -> INT (tek basina kalanlar)
        $sql = preg_replace('/\bINTEGER\b/i', 'INT', $sql);

        // 5) REAL -> DECIMAL(15,4)
        $sql = preg_replace('/\bREAL\b/i', 'DECIMAL(15,4)', $sql);

        // 6) CREATE TABLE sonuna ENGINE=InnoDB ekle (yoksa)
        if (stripos($sql, 'CREATE TABLE') !== false && stripos($sql, 'ENGINE=') === false) {
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
