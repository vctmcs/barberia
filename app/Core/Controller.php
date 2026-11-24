<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Controlador base: render de vistas, redirecciones, validacion CSRF,
 * control de acceso y respuestas JSON (para FullCalendar y fetch).
 */
abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $data['flash']   = Session::pullFlash();
        $data['csrf']    = Session::csrfToken();
        $data['authUser'] = Auth::user();
        $data['isAdmin'] = Auth::isAdmin();
        $data['old']     = Session::get('_old', []);
        $data['errors']  = Session::get('_errors', []);

        /* Recorrido guiado de la seccion: se abre solo si el usuario no lo ha visto */
        if (!empty($data['tour']) && Auth::check()) {
            $data['tourPendiente'] = !(new \App\Models\Recorrido())->visto((int) Auth::id(), $data['tour']);
        }

        Session::forget('_old');
        Session::forget('_errors');

        (new View())->render($view, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }

    /** Regresa al formulario con errores y lo capturado. */
    protected function back(string $path, array $errors, array $old = []): never
    {
        Session::set('_errors', $errors);
        Session::set('_old', $old);
        $this->redirect($path);
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
            (new View())->render('errors/419', ['title' => 'Sesión expirada'], 'layouts/blank');
            exit;
        }
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Session::flash('info', 'Inicia sesión para continuar.');
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Sin permiso']);
            exit;
        }
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }
}
