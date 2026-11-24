<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Subida segura de imagenes:
 *  - valida el tipo real con finfo (no confia en la extension),
 *  - limita el tamano,
 *  - genera un nombre aleatorio,
 *  - reescala con GD a un maximo de lado (si GD esta disponible), lo
 *    que tambien elimina datos EXIF como la ubicacion de la foto.
 */
final class Uploader
{
    private const TIPOS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public const MAX_BYTES = 4 * 1024 * 1024;
    private const MAX_LADO = 1200;

    /**
     * @return array{ok:bool, file?:string, error?:string}
     */
    public static function image(?array $file, string $carpeta): array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => self::mensajeError((int) $file['error'])];
        }

        if ($file['size'] > self::MAX_BYTES) {
            return ['ok' => false, 'error' => 'La imagen pesa más de 4 MB.'];
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::TIPOS[$mime])) {
            return ['ok' => false, 'error' => 'Sube una imagen JPG, PNG o WebP.'];
        }

        $dir = self::dir($carpeta);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return ['ok' => false, 'error' => 'No se pudo preparar la carpeta de imágenes.'];
        }

        $nombre = bin2hex(random_bytes(12)) . '.' . self::TIPOS[$mime];
        $destino = $dir . '/' . $nombre;

        $guardado = self::reescalar($file['tmp_name'], $destino, $mime)
            || move_uploaded_file($file['tmp_name'], $destino);

        if (!$guardado) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen.'];
        }

        return ['ok' => true, 'file' => $nombre];
    }

    public static function delete(?string $nombre, string $carpeta): void
    {
        if (!$nombre) {
            return;
        }
        $ruta = self::dir($carpeta) . '/' . basename($nombre);
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }

    public static function dir(string $carpeta): string
    {
        return BASE_PATH . '/storage/uploads/' . basename($carpeta);
    }

    private static function reescalar(string $origen, string $destino, string $mime): bool
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($origen),
            'image/png'  => @imagecreatefrompng($origen),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($origen) : false,
            default      => false,
        };

        if (!$img) {
            return false;
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $escala = min(1, self::MAX_LADO / max($w, $h));
        $nw = (int) round($w * $escala);
        $nh = (int) round($h * $escala);

        $lienzo = imagecreatetruecolor($nw, $nh);
        imagealphablending($lienzo, false);
        imagesavealpha($lienzo, true);
        imagecopyresampled($lienzo, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($lienzo, $destino, 85),
            'image/png'  => imagepng($lienzo, $destino, 6),
            'image/webp' => imagewebp($lienzo, $destino, 85),
        };

        imagedestroy($img);
        imagedestroy($lienzo);

        return $ok;
    }

    private static function mensajeError(int $codigo): string
    {
        return match ($codigo) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'La imagen es demasiado grande para el servidor.',
            UPLOAD_ERR_PARTIAL => 'La imagen se subió incompleta. Inténtalo de nuevo.',
            default => 'No se pudo subir la imagen.',
        };
    }
}
