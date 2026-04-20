<?php

declare(strict_types=1);

session_start();

define('BASE_PATH', __DIR__);

require BASE_PATH . '/app/bootstrap.php';

use App\Core\Migrator;

try {
    $migrator = new Migrator();
    $migrator->run();
} catch (Throwable $e) {
    echo 'HATA: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}