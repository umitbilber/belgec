<?php

declare(strict_types=1);

return [
    'up' => function (PDO $db): void {
        $kolonlar = [];
        $stmt = $db->query("PRAGMA table_info(teklif_kalemleri)");

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $kolon) {
            $kolonlar[] = $kolon['name'];
        }

        if (!in_array('satir_toplam', $kolonlar, true)) {
            $db->exec("ALTER TABLE teklif_kalemleri ADD COLUMN satir_toplam REAL DEFAULT 0");
        }
    },
];