<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Enrutador sencillo con soporte para parametros: /clientes/{id}.
 * Funciona igual en la raiz del dominio (InfinityFree) que dentro
 * de una subcarpeta (http://localhost/barberia).
 */
final class Router
{
    /** @var array<string, array<int, array{pattern:string, params:array, handler:array}>> */
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $params = [];

        $pattern = preg_replace_callback(
            '#\{([a-zA-Z_]+)\}#',
            function (array $m) use (&$params): string {
                $params[] = $m[1];
                return '([^/]+)';
            },
            rtrim($path, '/') ?: '/'
        );

        $this->routes[$method][] = [
            'pattern' => '#^' . $pattern . '$#',
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalize($uri);

        foreach ($this->routes[strtoupper($method)] ?? [] as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            array_shift($matches);
            $args = array_combine($route['params'], array_map('urldecode', $matches)) ?: [];

            [$class, $action] = $route['handler'];
            $controller = new $class();
            $controller->$action(...array_values($args));
            return;
        }

        http_response_code(404);
        (new View())->render('errors/404', ['title' => 'No encontrado'], 'layouts/blank');
    }

    /** Quita la query string y la subcarpeta base de la URL. */
    private function normalize(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        $path = '/' . trim($path, '/');

        return $path === '/index.php' ? '/' : $path;
    }
}
