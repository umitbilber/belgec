<?php

declare(strict_types=1);

return [
    'up' => function (PDO $db): void {
        try {
            $db->exec("ALTER TABLE teklif_kalemleri ADD COLUMN para_birimi TEXT DEFAULT 'TL'");
        } catch (\Throwable $e) {
        }

        try {
            $db->exec("ALTER TABLE teklifler ADD COLUMN teklif_notlari TEXT");
        } catch (\Throwable $e) {
        }
    },
];