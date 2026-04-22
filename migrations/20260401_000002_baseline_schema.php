<?php

declare(strict_types=1);

use App\Core\SqlTranslator;

return [
    'up' => function (PDO $db, ?SqlTranslator $translator = null): void {
        $translator = $translator ?? new SqlTranslator('sqlite');

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS musteriler (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ad_soyad TEXT NOT NULL,
                telefon TEXT,
                eposta TEXT,
                adres TEXT,
                vergi_no TEXT,
                bakiye REAL DEFAULT 0.00,
                kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "));

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS cari_hareketler (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cari_id INTEGER,
                islem_tipi TEXT,
                tutar REAL,
                aciklama TEXT,
                tarih DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "));

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS stoklar (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                urun_adi TEXT NOT NULL,
                stok_kodu TEXT,
                birim TEXT DEFAULT 'Adet',
                stok_miktari REAL DEFAULT 0.00
            )
        "));

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS stok_hareketler (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                stok_id INTEGER,
                cari_id INTEGER NULL,
                fatura_no TEXT NULL,
                islem_tipi TEXT,
                miktar REAL,
                birim_fiyat REAL DEFAULT 0.00,
                aciklama TEXT,
                tarih DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "));

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS faturalar (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fatura_no TEXT,
                cari_id INTEGER,
                tip TEXT,
                tarih DATE,
                genel_toplam REAL DEFAULT 0.00,
                kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "));

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS fatura_kalemleri (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fatura_id INTEGER,
                stok_id INTEGER,
                miktar REAL,
                birim_fiyat REAL,
                kdv_orani INTEGER DEFAULT 20
            )
        "));

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS teklifler (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                teklif_no TEXT,
                cari_id INTEGER,
                tarih DATE,
                para_birimi TEXT DEFAULT 'TL',
                genel_toplam REAL DEFAULT 0.00,
                kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "));

        $db->exec($translator->translate("
            CREATE TABLE IF NOT EXISTS teklif_kalemleri (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                teklif_id INTEGER,
                urun_adi TEXT,
                marka TEXT,
                miktar REAL,
                birim_fiyat REAL,
                termin TEXT
            )
        "));

        // Legacy ile uyumlu güvenli sütun eklemeleri
        try {
            $db->exec($translator->translate("ALTER TABLE musteriler ADD COLUMN eposta TEXT"));
        } catch (\Throwable $e) {}

        try {
            $db->exec($translator->translate("ALTER TABLE musteriler ADD COLUMN adres TEXT"));
        } catch (\Throwable $e) {}

        try {
            $db->exec($translator->translate("ALTER TABLE musteriler ADD COLUMN vergi_no TEXT"));
        } catch (\Throwable $e) {}
    },
];
