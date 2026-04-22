<?php

declare(strict_types=1);

use App\Core\SqlTranslator;

return [
    'up' => function (PDO $db, ?SqlTranslator $translator = null): void {
        $translator = $translator ?? new SqlTranslator('sqlite');

        try {
            $db->exec($translator->translate("ALTER TABLE teklif_kalemleri ADD COLUMN para_birimi TEXT DEFAULT 'TL'"));
        } catch (\Throwable $e) {}

        try {
            $db->exec($translator->translate("ALTER TABLE teklifler ADD COLUMN teklif_notlari TEXT"));
        } catch (\Throwable $e) {}
    },
];
