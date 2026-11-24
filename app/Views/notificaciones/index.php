<header class="page-head">
    <div>
        <h1 class="page-title">Notificaciones</h1>
        <p class="page-sub">Lo que necesita atención hoy y a quién vale la pena escribirle.</p>
    </div>
</header>

<div class="notice-grid">
    <section class="card notice notice--cyan" data-tour-step="hoy">
        <h2 class="notice__title">Para hoy <span class="notice__count"><?= e(count($citasHoy)) ?></span></h2>
        <?php if (!$citasHoy): ?>
        <p class="empty">No quedan citas por atender hoy.</p>
        <?php else: ?>
        <ul class="notice__list">
            <?php foreach ($citasHoy as $c): ?>
            <li>
                <a class="notice__row" href="<?= url("/citas/{$c['id']}") ?>" style="--emp-color: <?= e($c['color']) ?>">
                    <time class="notice__time"><?= e(hora($c['fecha_hora'])) ?></time>
                    <span><strong><?= e($c['cliente']) ?></strong><span class="table__sub"><?= e($c['servicio']) ?>, <?= e($c['empleado']) ?></span></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>

    <section class="card notice notice--danger" data-tour-step="cerrar">
        <h2 class="notice__title">Por cerrar <span class="notice__count"><?= e(count($sinCerrar)) ?></span></h2>
        <p class="card__hint">Citas que ya pasaron y siguen como programadas.</p>
        <?php if (!$sinCerrar): ?>
        <p class="empty">Todo al día.</p>
        <?php else: ?>
        <ul class="notice__list">
            <?php foreach ($sinCerrar as $c): ?>
            <li>
                <a class="notice__row" href="<?= url("/citas/{$c['id']}") ?>" style="--emp-color: <?= e($c['color']) ?>">
                    <time class="notice__time notice__time--date"><?= e(fecha_corta($c['fecha_hora'])) ?><small><?= e(hora($c['fecha_hora'])) ?></small></time>
                    <span><strong><?= e($c['cliente']) ?></strong><span class="table__sub"><?= e($c['servicio']) ?></span></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>

    <section class="card notice notice--pink" data-tour-step="historial">
        <h2 class="notice__title">Sin registrar <span class="notice__count"><?= e(count($sinHistorial)) ?></span></h2>
        <p class="card__hint">Atendidas sin fórmula ni fotos en el historial.</p>
        <?php if (!$sinHistorial): ?>
        <p class="empty">Todos los servicios están registrados.</p>
        <?php else: ?>
        <ul class="notice__list">
            <?php foreach ($sinHistorial as $c): ?>
            <li>
                <a class="notice__row" href="<?= url("/historial/nuevo?cita={$c['id']}") ?>">
                    <time class="notice__time notice__time--date"><?= e(fecha_corta($c['fecha_hora'])) ?></time>
                    <span><strong><?= e($c['cliente']) ?></strong><span class="table__sub"><?= e($c['servicio']) ?></span></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>
</div>

<div class="grid-2 grid-2--even notice-people">
    <section class="card notice notice--sun" data-tour-step="cumple">
        <h2 class="notice__title">Cumpleaños <span class="notice__count"><?= e(count($cumpleanos)) ?></span></h2>
        <p class="card__hint">Próximos 7 días.</p>
        <?php if (!$cumpleanos): ?>
        <p class="empty">Nadie cumple años esta semana.</p>
        <?php else: ?>
        <ul class="people-list">
            <?php foreach ($cumpleanos as $c): ?>
            <li class="people-list__item <?= $c['en'] === 0 ? 'is-today' : '' ?>">
                <a class="client-cell" href="<?= url("/clientes/{$c['id']}") ?>">
                    <span class="avatar"><?= e(iniciales($c['nombre'])) ?></span>
                    <span><strong><?= e($c['nombre']) ?></strong><span class="table__sub"><?= e($c['cuando']) ?>, cumple <?= e($c['cumple']) ?></span></span>
                </a>
                <?php if ($c['wa']): ?>
                <a class="btn btn--sm <?= $c['en'] === 0 ? 'btn--primary' : 'btn--ghost' ?>" href="<?= e($c['wa']) ?>" target="_blank" rel="noopener">Felicitar</a>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>

    <section class="card notice notice--violet" data-tour-step="inactivos">
        <h2 class="notice__title">No han regresado <span class="notice__count"><?= e(count($inactivos)) ?></span></h2>
        <p class="card__hint">Su última visita fue hace más de <?= e($semanas) ?> semanas y no tienen cita.</p>
        <?php if (!$inactivos): ?>
        <p class="empty">Todos tus clientes han vuelto a tiempo.</p>
        <?php else: ?>
        <ul class="people-list">
            <?php foreach ($inactivos as $c): ?>
            <li class="people-list__item">
                <a class="client-cell" href="<?= url("/clientes/{$c['id']}") ?>">
                    <span class="avatar"><?= e(iniciales($c['nombre'])) ?></span>
                    <span><strong><?= e($c['nombre']) ?></strong><span class="table__sub">Hace <?= e($c['semanas']) ?> semanas, <?= e(mb_strtolower((string) $c['ultimo_servicio'])) ?></span></span>
                </a>
                <span class="people-list__actions">
                    <?php if ($c['wa']): ?>
                    <a class="btn btn--sm btn--ghost" href="<?= e($c['wa']) ?>" target="_blank" rel="noopener">Escribir</a>
                    <?php endif; ?>
                    <a class="btn btn--sm" href="<?= url("/citas/nueva?cliente={$c['id']}") ?>">Agendar</a>
                </span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>
</div>

<?php if ($isAdmin): ?>
<section class="card mail-panel" id="correo" data-tour-step="correo">
    <div class="card__head">
        <h2 class="card__title">Recordatorios por correo</h2>
        <?php if ($correoListo): ?>
        <span class="status status--on">Correo configurado</span>
        <?php else: ?>
        <span class="status status--off">Sin configurar</span>
        <?php endif; ?>
    </div>

    <?php if (!$correoListo): ?>
    <div class="setup-note">
        <p>Para que el sistema mande el recordatorio <?= e($horasAntes) ?> horas antes de cada cita, llena la sección <code>mail</code> de <code>config/config.php</code> con una cuenta de Gmail y una contraseña de aplicación.</p>
        <p class="card__hint">La contraseña de aplicación se crea en tu cuenta de Google, en Seguridad, con la verificación en dos pasos activada.</p>
    </div>
    <?php else: ?>
    <p class="card__hint">
        Se envían solos <?= e($horasAntes) ?> horas antes de cada cita a los clientes con correo.
        Última revisión: <?= $ultimaRevision ? e(fecha_corta($ultimaRevision)) . ' a las ' . e(hora($ultimaRevision)) : 'todavía no' ?>.
    </p>
    <?php endif; ?>

    <div class="mail-panel__grid">
        <div>
            <h3 class="mail-panel__sub">En cola (<?= e(count($porEnviar)) ?>)</h3>
            <?php if (!$porEnviar): ?>
            <p class="empty">Ninguna cita en las próximas <?= e($horasAntes) ?> horas espera recordatorio.</p>
            <?php else: ?>
            <ul class="mail-list">
                <?php foreach ($porEnviar as $p): ?>
                <li>
                    <strong><?= e($p['cliente']) ?></strong>
                    <span class="table__sub"><?= e($p['email']) ?>, cita <?= e(fecha_corta($p['fecha_hora'])) ?> a las <?= e(hora($p['fecha_hora'])) ?><?= $p['rec_estado'] === 'fallido' ? ', reintento' : '' ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <form method="post" action="<?= url('/notificaciones/recordatorios') ?>">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn btn--sm btn--primary" type="submit" <?= $correoListo ? '' : 'disabled' ?>>Enviar ahora</button>
            </form>
            <?php endif; ?>
        </div>

        <div>
            <h3 class="mail-panel__sub">Últimos envíos</h3>
            <?php if (!$recientes): ?>
            <p class="empty">Aún no se ha enviado ningún recordatorio por correo.</p>
            <?php else: ?>
            <ul class="mail-list">
                <?php foreach ($recientes as $r): ?>
                <li>
                    <span class="status <?= $r['estado'] === 'enviado' ? 'status--on' : 'status--off' ?>"><?= $r['estado'] === 'enviado' ? 'Enviado' : 'Falló' ?></span>
                    <a href="<?= url("/citas/{$r['cita_id']}") ?>"><?= e($r['cliente']) ?></a>
                    <span class="table__sub"><?= e(fecha_corta($r['enviado_en'])) ?> a las <?= e(hora($r['enviado_en'])) ?><?= $r['detalle'] ? ': ' . e($r['detalle']) : '' ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <form class="form mail-test" method="post" action="<?= url('/notificaciones/correo-prueba') ?>">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <h3 class="mail-panel__sub">Probar la configuración</h3>
            <div class="field">
                <label for="para">Enviar un recordatorio de ejemplo a</label>
                <input id="para" name="para" type="email" value="<?= e($correoAdmin) ?>" required>
            </div>
            <button class="btn btn--sm btn--ghost" type="submit" <?= $correoListo ? '' : 'disabled' ?>>Enviar prueba</button>
        </form>
    </div>
</section>
<?php endif; ?>
