<?php

declare(strict_types=1);

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = '';
if ($scriptName !== '') {
    $parts = explode('/', trim(dirname($scriptName), '/'));
    if (!empty($parts) && end($parts) === 'public') {
        array_pop($parts);
    }
    $baseUrl = $parts ? '/' . implode('/', $parts) : '';
}

return [
    'name' => 'Belgeç',
    'version' => '1.0.0',
    'env' => 'production',
    'debug' => false,
    'base_path' => BASE_PATH,
    'base_url' => $baseUrl,
    'views_path' => BASE_PATH . '/app/Views',
];