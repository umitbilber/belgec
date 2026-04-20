<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function __construct(private Container $container) {}

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->method();
        $uri = $request->uri();

        $handler = $this->routes[$method][$uri] ?? null;

        if (!$handler) {
    $response->abort(404, 'Aradığın sayfa mevcut değil veya taşınmış olabilir.');
}

        if (is_callable($handler)) {
            $handler($request, $response);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $action] = $handler;
            $controller = $this->container->make($controllerClass);
            $controller->$action($request, $response);
            return;
        }

        $response->abort(500, 'Geçersiz rota tanımı.');
    }
}