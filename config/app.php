<?php

declare(strict_types=1);

// base_url'ü script'in bulunduğu klasörden otomatik tespit et
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = '';
if ($scriptName !== '') {
    // örn: /belgec/public/index.php → /belgec
    // örn: /test/public/index.php → /test
    // örn: /public/index.php → '' (kökte)
    $parts = explode('/', trim(dirname($scriptName), '/'));
    array_pop($parts); // 'public'i at
    $baseUrl = $parts ? '/' . implode('/', $parts) : '';
}

return [
    'name' => 'Belgeç',
    'env' => 'production',
    'debug' => false,
    'base_path' => BASE_PATH,
    'base_url' => $baseUrl,
    'views_path' => BASE_PATH . '/app/Views',
];