<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

class Migrator
{
    private PDO $db;
    private string $migrationsPath;
    private SqlTranslator $translator;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->migrationsPath = BASE_PATH . '/migrations';
        $this->translator = Database::translator();
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
                throw new RuntimeException('Gecersiz migration dosyasi: ' . $migrationName);
            }

            $this->db->beginTransaction();

            try {
                // Migration'a hem PDO hem de translator geciriyoruz.
                // Eski migration'lar sadece PDO alir (geriye uyumlu),
                // yeni migration'lar ikinci parametreyle translator'a erisir.
                $migration['up']($this->db, $this->translator);
                $this->markAsRun($migrationName);
                $this->db->commit();

                echo '[OK] ' . $migrationName . PHP_EOL;
            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw new RuntimeException(
                    'Migration calistirilirken hata olustu: ' . $migrationName . ' | ' . $e->getMessage()
                );
            }
        }

        echo 'Tum migration islemleri tamamlandi.' . PHP_EOL;
    }

    private function ensureMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL UNIQUE,
                run_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->db->exec($this->translator->translate($sql));
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
