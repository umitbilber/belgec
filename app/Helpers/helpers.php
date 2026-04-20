<?php

declare(strict_types=1);

if (!function_exists('config')) {
    function config(string $file): array
    {
        $path = BASE_PATH . '/config/' . $file . '.php';

        if (!file_exists($path)) {
            return [];
        }

        return require $path;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $app = config('app');
        $baseUrl = rtrim((string) ($app['base_url'] ?? ''), '/');

        $path = ltrim($path, '/');

        if ($path === '') {
            return $baseUrl === '' ? '/' : $baseUrl . '/';
        }

        return $baseUrl === '' ? '/' . $path : $baseUrl . '/' . $path;
    }
}