<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

        $app = config('app');
        $baseUrl = rtrim((string) ($app['base_url'] ?? ''), '/');

        if ($baseUrl !== '' && strpos($path, $baseUrl) === 0) {
            $path = substr($path, strlen($baseUrl));
        }

        if ($path === '' || $path === '/index.php') {
            return '/';
        }

        if (strpos($path, '/index.php/') === 0) {
            $path = substr($path, strlen('/index.php'));
        }

        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path) ?? '/';

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path === '' ? '/' : $path;
    }

    public function query(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    public function input(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }
    
    public function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

public function verifyCsrf(): bool
{
    $token = $this->input('_csrf_token') ?? $this->query('_csrf_token') ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if ($sessionToken === '' || $token === '') {
        return false;
    }

    return hash_equals($sessionToken, (string) $token);
}
}