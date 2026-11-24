Hola, <?= $nombre ?>.

Te recordamos tu cita en <?= $negocio ?> <?= $cuando ?>.

Fecha: <?= $fecha ?>

Horario: <?= $horario ?>

Servicio: <?= $servicio ?>, con <?= $empleado ?>

Precio base: <?= $precio ?>

<?php if ($direccion): ?>
Dónde: <?= $direccion ?>

<?= $referencia ?>

<?php endif; ?>
<?php if ($whatsapp): ?>
¿Necesitas cambiarla? Escríbenos: <?= $whatsapp ?>

<?php endif; ?>
<?= $sitioUrl ?>
