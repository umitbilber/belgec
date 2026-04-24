<?php

declare(strict_types=1);

use App\Core\SqlTranslator;

return [
    'up' => function (PDO $db, ?SqlTranslator $translator = null): void {
        $translator = $translator ?? new SqlTranslator('sqlite');

        // Cihaz bazli push abonelikleri. Bir kullanicinin birden fazla cihazi olabilir.
        // endpoint_hash = SHA256(endpoint). MySQL'de TEXT UNIQUE index sorununu
        // cozmek icin hash uzerinden unique kontrol yapilir.
        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS push_abonelikleri (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                kullanici_id INTEGER NOT NULL,
                endpoint_hash TEXT NOT NULL UNIQUE,
                endpoint TEXT NOT NULL,
                p256dh TEXT NOT NULL,
                auth TEXT NOT NULL,
                user_agent TEXT NULL,
                olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
                son_goruldu DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
            )
        "));

        // Duplicate push gonderimini engellemek icin son bildirim degerlerini tutuyoruz.
        // tip: 'edm_toplam' (int), 'guncelleme_surum' (string) gibi.
        // Topbar polling yaptiginda onceki deger ile karsilastirip push tetiklenir.
        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS push_son_durumlar (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                kullanici_id INTEGER NOT NULL,
                tip VARCHAR(50) NOT NULL,
                son_deger TEXT NULL,
                guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(kullanici_id, tip),
                FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
            )
        "));
    },
];
