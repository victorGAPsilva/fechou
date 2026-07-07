<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $handler): void
    {
        $this->add('GET', $uri, $handler);
    }

    public function post(string $uri, callable|array $handler): void
    {
        $this->add('POST', $uri, $handler);
    }

    public function add(string $method, string $uri, callable|array $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $this->normalize($uri),
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $requestUri): void
    {
        $method = strtoupper($method);
        $path = $this->normalize(parse_url($requestUri, PHP_URL_PATH) ?: '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            array_shift($matches);
            $this->execute($route['handler'], $matches);

            return;
        }

        http_response_code(404);
        View::render('errors/404', [
            'title' => 'Página não encontrada',
        ], 'layouts/guest');
    }

    private function execute(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);

            return;
        }

        [$class, $method] = $handler;
        $controller = new $class();
        call_user_func_array([$controller, $method], $params);
    }

    private function normalize(string $uri): string
    {
        $uri = '/' . trim($uri, '/');

        return $uri === '/' ? '/' : rtrim($uri, '/');
    }
}