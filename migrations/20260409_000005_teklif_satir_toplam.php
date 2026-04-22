<?php

declare(strict_types=1);

use App\Core\SqlTranslator;

return [
    'up' => function (PDO $db, ?SqlTranslator $translator = null): void {
        $translator = $translator ?? new SqlTranslator('sqlite');

        $kolonlar = $translator->tableColumns($db, 'teklif_kalemleri');

        if (!in_array('satir_toplam', $kolonlar, true)) {
            $db->exec($translator->translate("ALTER TABLE teklif_kalemleri ADD COLUMN satir_toplam REAL DEFAULT 0"));
        }
    },
];
