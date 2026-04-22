<?php

declare(strict_types=1);

use App\Core\SqlTranslator;

return [
    'up' => function (PDO $db, ?SqlTranslator $translator = null): void {
        $translator = $translator ?? new SqlTranslator('sqlite');

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS audit_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                kullanici_adi TEXT NOT NULL DEFAULT 'yonetici',
                islem TEXT NOT NULL,
                modul TEXT NOT NULL,
                kayit_id INTEGER NULL,
                aciklama TEXT NULL,
                ip TEXT NULL,
                tarih DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "));
    },
];
