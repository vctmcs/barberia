<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', [
            'title'    => 'Iniciar sesión',
            'bloqueo'  => (int) ceil(Auth::bloqueoRestante() / 60),
        ], 'layouts/blank');
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email    = (string) $this->input('email', '');
        $password = (string) ($_POST['password'] ?? '');

        if (Auth::bloqueoRestante() > 0) {
            $this->redirect('/login');
        }

        if ($email === '' || $password === '') {
            $this->back('/login', ['login' => 'Escribe tu correo y tu contraseña.'], ['email' => $email]);
        }

        if (!Auth::attempt($email, $password)) {
            $mensaje = Auth::bloqueoRestante() > 0
                ? 'Demasiados intentos. Espera unos minutos para volver a intentar.'
                : 'El correo o la contraseña no coinciden.';
            $this->back('/login', ['login' => $mensaje], ['email' => $email]);
        }

        Session::flash('success', 'Bienvenido, ' . Auth::user()['nombre'] . '.');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        Auth::logout();
        $this->redirect('/login');
    }
}
