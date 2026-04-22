<?php

declare(strict_types=1);

use App\Core\SqlTranslator;

return [
    'up' => function (PDO $db, ?SqlTranslator $translator = null): void {
        // Geriye uyumluluk: translator gelmediyse SQLite varsay
        $translator = $translator ?? new SqlTranslator('sqlite');

        $tekliflerKolonlari = $translator->tableColumns($db, 'teklifler');

        if (!in_array('teklif_notlari', $tekliflerKolonlari, true)) {
            $db->exec($translator->translate("ALTER TABLE teklifler ADD COLUMN teklif_notlari TEXT"));
        }

        $teklifKalemleriKolonlari = $translator->tableColumns($db, 'teklif_kalemleri');

        if (!in_array('para_birimi', $teklifKalemleriKolonlari, true)) {
            $db->exec($translator->translate("ALTER TABLE teklif_kalemleri ADD COLUMN para_birimi TEXT DEFAULT 'TL'"));
        }
    },
];
