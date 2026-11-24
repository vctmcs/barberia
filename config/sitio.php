<?php

/*
 * Contenido de la pagina publica (la que se ve antes del login).
 * Todo lo marcado como EJEMPLO debe reemplazarse con los datos reales
 * del negocio. Los servicios y precios NO van aqui: se toman del
 * modulo de servicios del sistema.
 */

return [

    'eslogan' => 'Corte, color y tratamientos para dama y caballero.',

    // EJEMPLO: numero de WhatsApp a 10 digitos (se le agrega 52 de Mexico)
    'whatsapp' => '5500000000',
    'mensaje_whatsapp' => 'Hola, quiero agendar una cita en Hair +.',

    // EJEMPLO: telefono fijo opcional; dejalo vacio para ocultarlo
    'telefono' => '',

    // EJEMPLO: direccion; se usa tambien para el enlace a Google Maps
    'direccion' => 'Calle Ejemplo 123, Col. Centro',
    'referencia' => 'Frente al parque, local con letrero rosa.',

    // Redes: deja vacio lo que no uses
    'instagram' => '',
    'facebook'  => '',

    // Horario que se muestra al publico
    'horario' => [
        ['dias' => 'Lunes a viernes', 'horas' => '10:00 a 20:00'],
        ['dias' => 'Sábado',          'horas' => '10:00 a 18:00'],
        ['dias' => 'Domingo',         'horas' => '10:00 a 15:00'],
    ],

    // Texto que acompana a cada categoria de servicio
    'categorias' => [
        'corte'       => 'El corte incluye shampoo, corte y secado.',
        'tinte'       => 'Desde un color uniforme hasta degradados pintados a mano.',
        'tratamiento' => 'Recuperan brillo, fuerza y suavidad del cabello maltratado.',
        'alaciado'    => 'Japonés para un lacio permanente o express para semanas sin frizz.',
        'split_ender' => 'Elimina las puntas abiertas sin sacrificar el largo.',
        'otro'        => '',
    ],

    // EJEMPLO: seccion "Acerca de mi"
    'acerca' => [
        'nombre' => 'Daniela Ríos',
        'puesto' => 'Estilista y colorista',
        // Foto opcional en public/img/ (por ejemplo 'acerca.jpg')
        'foto'   => '',
        'parrafos' => [
            'Hair + nació de una idea sencilla: que salgas del salón sintiéndote tú, pero en tu mejor versión. Escucho lo que quieres antes de tomar las tijeras y te digo con honestidad qué le conviene a tu cabello.',
            'Me especializo en color y en alaciados. Cada fórmula que uso queda registrada en tu historial, así que la próxima vez partimos de lo que ya funcionó y no de cero.',
        ],
        'puntos' => [
            'Diagnóstico de tu cabello antes de cada servicio',
            'Historial de tus fórmulas de color y tratamientos',
            'Recordatorio de tu cita un día antes',
        ],
    ],

    // Galeria: archivos dentro de public/img/galeria/
    // Reemplaza las imagenes de ejemplo por fotos reales de tus trabajos.
    'galeria' => [
        ['archivo' => 'corte.webp',       'titulo' => 'Corte',        'texto' => 'Líneas limpias y forma que dura.'],
        ['archivo' => 'color.webp',       'titulo' => 'Color',        'texto' => 'Degradados de tono a mano.'],
        ['archivo' => 'alaciado.webp',    'titulo' => 'Alaciado',     'texto' => 'Lacio, brillo y cero frizz.'],
        ['archivo' => 'tratamiento.webp', 'titulo' => 'Tratamiento',  'texto' => 'Brillo que se nota.'],
        ['archivo' => 'split-ender.webp', 'titulo' => 'Split ender',  'texto' => 'Adiós puntas abiertas.'],
        ['archivo' => 'mechas.webp',      'titulo' => 'Mechas',       'texto' => 'Luz alrededor del rostro.'],
    ],
];
