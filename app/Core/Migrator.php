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
        // MySQL'de DDL komutlari (CREATE TABLE, ALTER TABLE) implicit commit tetikler,
            // transaction garantisi yoktur. Bu yuzden sadece SQLite'ta transaction kullaniyoruz.
            $useTransaction = Database::driver() === 'sqlite';

            if ($useTransaction) {
                $this->db->beginTransaction();
            }

            try {
                $migration['up']($this->db, $this->translator);
                $this->markAsRun($migrationName);

                if ($useTransaction && $this->db->inTransaction()) {
                    $this->db->commit();
                }

                echo '[OK] ' . $migrationName . PHP_EOL;
            } catch (\Throwable $e) {
                if ($useTransaction && $this->db->inTransaction()) {
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
