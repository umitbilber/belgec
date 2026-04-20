<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require BASE_PATH . '/config/database.php';
        $dbPath = $config['connections']['sqlite']['database'];

        try {
            self::$connection = new PDO('sqlite:' . $dbPath);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Veritabanı bağlantı hatası: ' . $e->getMessage());
        }

        return self::$connection;
    }
}