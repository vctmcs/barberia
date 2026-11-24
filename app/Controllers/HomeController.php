<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Models\Servicio;

/**
 * Pagina publica de la estetica: servicios con precios tomados de la
 * base, galeria, acerca de mi, horario y contacto.
 */
final class HomeController extends Controller
{
    public function index(): void
    {
        $sitio = (array) Config::get('sitio', []);

        try {
            $catalogo = (new Servicio())->catalogo();
        } catch (\Throwable $e) {
            $catalogo = []; // sin base de datos todavia: la pagina carga igual
        }

        /* Solo las imagenes que existen en public/img/galeria */
        $galeria = array_values(array_filter(
            $sitio['galeria'] ?? [],
            fn ($g) => is_file(BASE_PATH . '/public/img/galeria/' . basename($g['archivo']))
        ));

        $acerca = $sitio['acerca'] ?? [];
        $fotoAcerca = !empty($acerca['foto']) && is_file(BASE_PATH . '/public/img/' . basename($acerca['foto']))
            ? asset('img/' . basename($acerca['foto']))
            : null;

        $this->view('sitio/index', [
            'title'       => 'Estética para dama y caballero',
            'sitio'       => $sitio,
            'categorias'  => Servicio::TIPOS,
            'catalogo'    => $catalogo,
            'galeria'     => $galeria,
            'acerca'      => $acerca,
            'fotoAcerca'  => $fotoAcerca,
            'firma'       => explode(' ', (string) ($acerca['nombre'] ?? ''))[0],
            'whatsapp'    => whatsapp_url($sitio['whatsapp'] ?? '', $sitio['mensaje_whatsapp'] ?? ''),
            'mapa'        => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode((string) ($sitio['direccion'] ?? '')),
            'logueado'    => Auth::check(),
            'anio'        => date('Y'),
        ], 'layouts/public');
    }
}
