<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;
use App\Core\Container;
use App\Core\ServiceProvider;

class App
{
    public function run(): void
    {
        $container = new Container();
        ServiceProvider::register($container);

        $router = new Router($container);
        $request = new Request();
        $response = new Response();

        require BASE_PATH . '/routes/web.php';

        try {
            $router->dispatch($request, $response);
        } catch (Throwable $e) {
            $app = config('app');
            $debug = (bool) ($app['debug'] ?? false);

            if ($debug) {
                $response->abort(500, 'Uygulama hatası: ' . $e->getMessage());
            }

            $response->abort(500, 'Beklenmeyen bir hata oluştu.');
        }
    }
}