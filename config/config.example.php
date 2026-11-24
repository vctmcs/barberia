<?php

/*
 * Copia este archivo como config.php y llena tus datos.
 * config.php esta en .gitignore: nunca se sube al repositorio.
 */

return [

    'app' => [
        'name'         => 'Hair +',
        // URL base sin diagonal final.
        // Local con "php -S localhost:8000": http://localhost:8000
        // InfinityFree: https://tusitio.infinityfreeapp.com
        'url'          => 'http://localhost:8000',
        'timezone'     => 'America/Mexico_City',
        'debug'        => true,
        'session_name' => 'hairplus_sid',
        // Minutos sin actividad antes de cerrar la sesion
        'session_idle' => 120,
    ],

    // SQLite: un solo archivo. La carpeta database/ debe tener permisos
    // de escritura porque SQLite crea ahi su archivo temporal de diario.
    'db' => [
        'path' => 'database/barberia.sqlite',
    ],

    // SMTP externo (InfinityFree no ofrece servicio de correo propio).
    // Con Gmail se usa una "contrasena de aplicacion", no la normal.
    'mail' => [
        'host'       => 'smtp.gmail.com',
        'port'       => 587,
        'encryption' => 'tls',
        'username'   => 'tu-correo@gmail.com',
        'password'   => 'contrasena-de-aplicacion',
        'from_email' => 'tu-correo@gmail.com',
        'from_name'  => 'Hair +',
    ],

    'reminders' => [
        // Horas antes de la cita para enviar el recordatorio
        'hours_before'   => 24,
        // Cada cuantos minutos como maximo se revisan pendientes
        // cuando se disparan desde las visitas al sistema
        'check_interval' => 15,
        // Token para ejecutar cron/recordatorios.php por URL
        'cron_token'     => 'cambia-este-token-por-uno-largo',
    ],

    'notifications' => [
        // Semanas sin visita para marcar a un cliente como inactivo
        'inactive_weeks' => 6,
    ],
];
