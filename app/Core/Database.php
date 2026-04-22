<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $connection = null;
    private static ?TranslatedPdo $translatedConnection = null;
    private static ?SqlTranslator $translator = null;
    private static ?string $driver = null;
    

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require BASE_PATH . '/config/database.php';
        $driver = (string) ($config['default'] ?? 'sqlite');
        $conn   = $config['connections'][$driver] ?? null;

        if (!is_array($conn)) {
            throw new RuntimeException('Gecersiz veritabani konfigurasyonu: ' . $driver);
        }

        try {
            if ($driver === 'sqlite') {
                self::$connection = new PDO('sqlite:' . $conn['database']);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$connection->exec('PRAGMA foreign_keys = ON');
            } elseif ($driver === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $conn['host'],
                    (int) $conn['port'],
                    $conn['database'],
                    $conn['charset']
                );
                self::$connection = new PDO(
                    $dsn,
                    $conn['username'],
                    $conn['password'],
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } else {
                throw new RuntimeException('Desteklenmeyen veritabani driver: ' . $driver);
            }

            self::$driver = $driver;
            self::$translator = new SqlTranslator($driver);
        } catch (PDOException $e) {
            die('Veritabani baglanti hatasi: ' . $e->getMessage());
        }

        return self::$connection;
    }

    /**
     * Repository'ler icin TranslatedPdo dondurur. SQL otomatik translator'dan gecer.
     * Migrator ve CLI kullanim icin `connection()` direkt PDO donduruyor.
     */
    public static function translatedConnection(): TranslatedPdo
    {
        if (self::$translatedConnection === null) {
            self::connection(); // PDO ve translator initialize edilir
            self::$translatedConnection = new TranslatedPdo(self::$connection, self::$translator);
        }
        return self::$translatedConnection;
    }

    /**
     * Aktif driver ismi (sqlite | mysql).
     */
    public static function driver(): string
    {
        if (self::$driver === null) {
            self::connection();
        }
        return (string) self::$driver;
    }

    /**
     * SqlTranslator instance'i. Migration'lar, Services ve Migrator bunu kullanir.
     */
    public static function translator(): SqlTranslator
    {
        if (self::$translator === null) {
            self::connection();
        }
        return self::$translator;
    }

    /**
     * SQL'i driver'a gore cevirip execute eder. exec() yerine kullanilir.
     */
    public static function exec(string $sql): int
    {
        $db = self::connection();
        $translated = self::translator()->translate($sql);
        $result = $db->exec($translated);
        return $result === false ? 0 : (int) $result;
    }

    /**
     * Baglantiyi resetler. Test veya setup sirasinda kullanilir.
     */
    public static function reset(): void
    {
        self::$connection = null;
        self::$translatedConnection = null;
        self::$translator = null;
        self::$driver = null;
    }
}
