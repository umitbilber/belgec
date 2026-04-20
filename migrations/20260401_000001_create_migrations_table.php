<?php

declare(strict_types=1);

use PDO;

return [
    'up' => function (PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL UNIQUE,
                run_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    },
];