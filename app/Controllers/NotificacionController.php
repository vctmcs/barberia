<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Session;
use App\Services\Avisos;
use App\Services\Correo;
use App\Services\Recordatorios;

final class NotificacionController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $avisos = new Avisos(Auth::isAdmin() ? null : Auth::empleadoId());
        $negocio = (string) Config::get('app.name');
        $semanas = max(1, (int) Config::get('notifications.inactive_weeks', 6));

        $cumpleanos = array_map(fn ($c) => $c + [
            'cuando' => match ($c['en']) {
                0 => 'Hoy',
                1 => 'Mañana',
                default => ucfirst(fecha_larga($c['dia'])),
            },
            'wa' => $c['telefono'] ? whatsapp_url($c['telefono'], sprintf(
                '¡Feliz cumpleaños, %s! Todo el equipo de %s te desea un gran día. Te esperamos para consentirte.',
                explode(' ', $c['nombre'])[0], $negocio
            )) : null,
        ], $avisos->cumpleanos(7));

        $inactivos = array_map(fn ($c) => $c + [
            'semanas' => intdiv((int) (new \DateTime(substr($c['ultima'], 0, 10)))->diff(new \DateTime('today'))->days, 7),
            'wa' => $c['telefono'] ? whatsapp_url($c['telefono'], sprintf(
                'Hola %s, hace tiempo que no te vemos en %s. ¿Te apartamos un horario para tu próximo %s?',
                explode(' ', $c['nombre'])[0], $negocio, mb_strtolower((string) $c['ultimo_servicio'])
            )) : null,
        ], $avisos->inactivos($semanas));

        $data = [
            'title'        => 'Notificaciones',
            'tour'         => 'notificaciones',
            'citasHoy'     => $avisos->citasHoy(),
            'sinCerrar'    => $avisos->sinCerrar(),
            'sinHistorial' => $avisos->sinHistorial(),
            'cumpleanos'   => $cumpleanos,
            'inactivos'    => $inactivos,
            'semanas'      => $semanas,
        ];

        if (Auth::isAdmin()) {
            $estado = Recordatorios::estado();
            $data += [
                'correoListo'     => Correo::configurado(),
                'ultimaRevision'  => !empty($estado['ultima']) ? date('Y-m-d H:i:s', (int) $estado['ultima']) : null,
                'porEnviar'       => Recordatorios::pendientes(20),
                'recientes'       => Recordatorios::recientes(8),
                'horasAntes'      => (int) Config::get('reminders.hours_before', 24),
                'correoAdmin'     => (string) (Auth::user()['email'] ?? ''),
            ];
        }

        $this->view('notificaciones/index', $data);
    }

    /** Envia ahora los recordatorios pendientes (administrador). */
    public function enviar(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $r = Recordatorios::procesar();

        if ($r['sin_configurar']) {
            Session::flash('error', 'Primero configura el correo en config/config.php.');
        } elseif ($r['revisadas'] === 0) {
            Session::flash('info', 'No hay recordatorios pendientes por ahora.');
        } else {
            Session::flash($r['fallidos'] ? 'error' : 'success', sprintf(
                'Recordatorios enviados: %d. Fallidos: %d.', $r['enviados'], $r['fallidos']
            ));
        }
        $this->redirect('/notificaciones#correo');
    }

    /** Correo de prueba para revisar la configuracion SMTP (administrador). */
    public function prueba(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $para = trim((string) ($_POST['para'] ?? ''));
        if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Escribe un correo válido para la prueba.');
            $this->redirect('/notificaciones#correo');
        }

        $r = Recordatorios::prueba($para, (string) (Auth::user()['nombre'] ?? 'Prueba'));
        Session::flash($r['ok'] ? 'success' : 'error', $r['ok']
            ? "Correo de prueba enviado a {$para}. Revisa también la carpeta de spam."
            : 'No se pudo enviar: ' . $r['error']);
        $this->redirect('/notificaciones#correo');
    }
}
