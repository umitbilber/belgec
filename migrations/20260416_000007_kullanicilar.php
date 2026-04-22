<?php

declare(strict_types=1);

use App\Core\SqlTranslator;

return [
    'up' => function (PDO $db, ?SqlTranslator $translator = null): void {
        $translator = $translator ?? new SqlTranslator('sqlite');

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS kullanicilar (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ad TEXT NOT NULL,
                kullanici_adi TEXT NOT NULL UNIQUE,
                sifre_hash TEXT NOT NULL,
                rol TEXT NOT NULL DEFAULT 'kullanici',
                aktif INTEGER NOT NULL DEFAULT 1,
                olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "));

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS kullanici_izinler (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                kullanici_id INTEGER NOT NULL,
                izin TEXT NOT NULL,
                UNIQUE(kullanici_id, izin),
                FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
            )
        "));
    },
];
