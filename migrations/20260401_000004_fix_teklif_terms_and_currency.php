<?php

declare(strict_types=1);

return [
    'up' => function (PDO $db): void {
        $tekliflerKolonlari = [];
        $stmt = $db->query("PRAGMA table_info(teklifler)");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $kolon) {
            $tekliflerKolonlari[] = $kolon['name'];
        }

        if (!in_array('teklif_notlari', $tekliflerKolonlari, true)) {
            $db->exec("ALTER TABLE teklifler ADD COLUMN teklif_notlari TEXT");
        }

        $teklifKalemleriKolonlari = [];
        $stmt = $db->query("PRAGMA table_info(teklif_kalemleri)");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $kolon) {
            $teklifKalemleriKolonlari[] = $kolon['name'];
        }

        if (!in_array('para_birimi', $teklifKalemleriKolonlari, true)) {
            $db->exec("ALTER TABLE teklif_kalemleri ADD COLUMN para_birimi TEXT DEFAULT 'TL'");
        }
    },
];