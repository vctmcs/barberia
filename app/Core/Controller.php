<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Controlador base: render de vistas, redirecciones, validacion CSRF
 * y respuestas JSON (para FullCalendar y peticiones fetch).
 */
abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $data['flash'] = Session::pullFlash();
        $data['csrf']  = Session::csrfToken();
        (new View())->render($view, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function verifyCsrf(): void
    {
        if (!Session::validCsrf($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            (new View())->render('errors/419', ['title' => 'Sesion expirada'], 'layouts/blank');
            exit;
        }
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }
}
