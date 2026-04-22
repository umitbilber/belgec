<?php

declare(strict_types=1);

use App\Core\SqlTranslator;

return [
    'up' => function (PDO $db, ?SqlTranslator $translator = null): void {
        $translator = $translator ?? new SqlTranslator('sqlite');

        try {
            $db->exec($translator->translate("ALTER TABLE musteriler ADD COLUMN varsayilan_vade_gun INTEGER DEFAULT 0"));
        } catch (\Throwable $e) {}

        try {
            $db->exec($translator->translate("ALTER TABLE faturalar ADD COLUMN vade_tarihi DATE NULL"));
        } catch (\Throwable $e) {}

        try {
            $db->exec($translator->translate("ALTER TABLE faturalar ADD COLUMN odenen_tutar REAL DEFAULT 0"));
        } catch (\Throwable $e) {}
    },
];
