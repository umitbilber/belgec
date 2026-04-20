<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

class Migrator
{
    private PDO $db;
    private string $migrationsPath;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->migrationsPath = BASE_PATH . '/migrations';
    }

    public function run(): void
    {
        $this->ensureMigrationsTable();

        $files = glob($this->migrationsPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $migrationName = basename($file);

            if ($this->hasRun($migrationName)) {
                continue;
            }

            $migration = require $file;

            if (!is_array($migration) || !isset($migration['up']) || !is_callable($migration['up'])) {
                throw new RuntimeException('Geçersiz migration dosyası: ' . $migrationName);
            }

            $this->db->beginTransaction();

            try {
                $migration['up']($this->db);
                $this->markAsRun($migrationName);
                $this->db->commit();

                echo '[OK] ' . $migrationName . PHP_EOL;
            } catch (\Throwable $e) {
                $this->db->rollBack();
                throw new RuntimeException(
                    'Migration çalıştırılırken hata oluştu: ' . $migrationName . ' | ' . $e->getMessage()
                );
            }
        }

        echo 'Tüm migration işlemleri tamamlandı.' . PHP_EOL;
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL UNIQUE,
                run_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    private function hasRun(string $migrationName): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
        $stmt->execute([$migrationName]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function markAsRun(string $migrationName): void
    {
        $stmt = $this->db->prepare('INSERT INTO migrations (migration) VALUES (?)');
        $stmt->execute([$migrationName]);
    }
}