<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

class View
{
    public static function make(string $view, array $data = [], ?string $layout = null): string
    {
        $app = config('app');
        $viewsPath = $app['views_path'] ?? (BASE_PATH . '/app/Views');

        $viewPath = $viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new RuntimeException('View dosyası bulunamadı: ' . $viewPath);
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $viewPath;
            $content = (string) ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        if ($layout === null) {
            return $content;
        }

        $layoutPath = $viewsPath . '/' . str_replace('.', '/', $layout) . '.php';

        if (!file_exists($layoutPath)) {
            throw new RuntimeException('Layout dosyası bulunamadı: ' . $layoutPath);
        }

        ob_start();

        try {
            require $layoutPath;
            return (string) ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}