<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envio de correo por SMTP externo con PHPMailer.
 * InfinityFree no tiene servidor de correo ni mail(), asi que se usa,
 * por ejemplo, Gmail con una contrasena de aplicacion.
 */
final class Correo
{
    /** True cuando la seccion mail de config.php ya tiene datos reales. */
    public static function configurado(): bool
    {
        $m = (array) Config::get('mail', []);

        return !empty($m['host']) && !empty($m['username']) && !empty($m['password'])
            && !str_contains((string) $m['username'], 'tu-correo')
            && $m['password'] !== 'contrasena-de-aplicacion';
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public static function enviar(string $para, string $nombre, string $asunto, string $html, string $texto): array
    {
        if (!self::configurado()) {
            return ['ok' => false, 'error' => 'El correo no está configurado en config/config.php.'];
        }

        $m = (array) Config::get('mail');
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = (string) $m['host'];
            $mail->Port       = (int) ($m['port'] ?? 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) $m['username'];
            $mail->Password   = (string) $m['password'];
            $mail->Timeout    = 15;
            $mail->CharSet    = PHPMailer::CHARSET_UTF8;
            $mail->Encoding   = PHPMailer::ENCODING_BASE64;

            $cifrado = strtolower((string) ($m['encryption'] ?? 'tls'));
            if ($cifrado === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($cifrado === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                // Sin cifrado: solo para servidores de prueba locales
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $mail->setFrom((string) ($m['from_email'] ?? $m['username']), (string) ($m['from_name'] ?? Config::get('app.name')));
            $mail->addAddress($para, $nombre);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $html;
            $mail->AltBody = $texto;

            $mail->send();
            return ['ok' => true];
        } catch (MailException $e) {
            return ['ok' => false, 'error' => self::mensajeClaro($mail->ErrorInfo ?: $e->getMessage())];
        }
    }

    /** Traduce los errores mas comunes de SMTP a algo accionable. */
    private static function mensajeClaro(string $error): string
    {
        $e = strtolower($error);

        return match (true) {
            str_contains($e, 'authenticate'), str_contains($e, 'username and password')
                => 'Gmail rechazó el usuario o la contraseña. Usa una contraseña de aplicación, no la de tu cuenta.',
            str_contains($e, 'connect()'), str_contains($e, 'could not connect'), str_contains($e, 'timed out')
                => 'No se pudo conectar al servidor de correo. Revisa host, puerto y cifrado.',
            str_contains($e, 'invalid address'), str_contains($e, 'recipient')
                => 'La dirección de correo del cliente no es válida.',
            default => mb_substr($error, 0, 200),
        };
    }
}
