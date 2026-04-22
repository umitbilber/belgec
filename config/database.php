<?php

declare(strict_types=1);

/**
 * Varsayilan veritabani konfigurasyonu.
 * Gercek driver secimi ve MySQL baglanti bilgileri `ayarlar.json` icinde tutulur.
 * Bu dosya sadece yedek/fallback olarak kullanilir.
 */

// ayarlar.json'dan veritabani secimini oku
$ayarlarDosyasi = BASE_PATH . '/ayarlar.json';
$driver = 'sqlite';
$mysqlAyar = [];

if (file_exists($ayarlarDosyasi)) {
    $ayarlar = json_decode((string) file_get_contents($ayarlarDosyasi), true);
    if (is_array($ayarlar)) {
        $driver = (string) ($ayarlar['db_driver'] ?? 'sqlite');
        $mysqlAyar = is_array($ayarlar['db_mysql'] ?? null) ? $ayarlar['db_mysql'] : [];
    }
}

return [
    'default' => $driver,
    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => BASE_PATH . '/veritabani.sqlite',
        ],
        'mysql' => [
            'driver'   => 'mysql',
            'host'     => (string) ($mysqlAyar['host']     ?? '127.0.0.1'),
            'port'     => (int)    ($mysqlAyar['port']     ?? 3306),
            'database' => (string) ($mysqlAyar['database'] ?? 'belgec'),
            'username' => (string) ($mysqlAyar['username'] ?? 'root'),
            'password' => (string) ($mysqlAyar['password'] ?? ''),
            'charset'  => (string) ($mysqlAyar['charset']  ?? 'utf8mb4'),
        ],
    ],
];
