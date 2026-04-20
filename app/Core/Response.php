<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    public function view(string $view, array $data = [], ?string $layout = null): void
    {
        echo View::make($view, $data, $layout);
        exit;
    }

    public function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public function abort(int $status = 404, string $message = 'Sayfa bulunamadı.'): void
{
    http_response_code($status);

    $basliklar = [
        403 => 'Erişim Reddedildi',
        404 => 'Sayfa Bulunamadı',
        419 => 'İstek Süresi Doldu',
        500 => 'Sunucu Hatası',
    ];

    $kod    = $status;
    $baslik = $basliklar[$status] ?? 'Hata';
    $mesaj  = $message;

    $viewPath = BASE_PATH . '/app/Views/errors/error.php';
    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        echo e($message);
    }
    exit;
}
}