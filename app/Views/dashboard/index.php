<header class="page-head">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Hoy es <?= e($fechaLarga) ?></p>
    </div>
</header>

<section class="stats" aria-label="Resumen">
    <article class="stat stat--pink">
        <p class="stat__label">Citas de hoy</p>
        <p class="stat__value"><?= e(count($citas)) ?></p>
        <p class="stat__note"><?= e($pendientes) ?> por atender</p>
    </article>
    <article class="stat stat--cyan">
        <p class="stat__label">Ingresos de <?= e($mesNombre) ?></p>
        <p class="stat__value"><?= e(dinero($ingresosMes)) ?></p>
        <p class="stat__note">Solo citas atendidas</p>
    </article>
    <article class="stat stat--sun">
        <p class="stat__label">Clientes activos</p>
        <p class="stat__value"><?= e($clientes) ?></p>
        <p class="stat__note">En la cartera</p>
    </article>
</section>

<div class="grid-2">
    <section class="card" data-tour-step="agenda">
        <h2 class="card__title">Agenda del día</h2>

        <?php if (!$citas): ?>
        <p class="empty">No hay citas para hoy.</p>
        <?php else: ?>
        <ol class="timeline">
            <?php foreach ($citas as $cita): ?>
            <li><a href="<?= url("/citas/{$cita['id']}") ?>" class="timeline__item" style="--emp-color: <?= e($cita['color']) ?>">
                <time class="timeline__time" datetime="<?= e($cita['fecha_hora']) ?>"><?= e(hora($cita['fecha_hora'])) ?></time>
                <div class="timeline__body">
                    <strong><?= e($cita['cliente']) ?></strong>
                    <span><?= e($cita['servicio']) ?> con <?= e($cita['empleado']) ?></span>
                </div>
                <span class="badge badge--<?= e($cita['estado']) ?>"><?= e(estado_cita($cita['estado'])) ?></span>
            </a></li>
            <?php endforeach; ?>
        </ol>
        <?php endif; ?>
    </section>

    <section class="card" data-tour-step="top">
        <h2 class="card__title">Servicios más solicitados</h2>
        <p class="card__hint">Últimos 90 días</p>

        <?php if (!$topServicios): ?>
        <p class="empty">Aún no hay citas registradas.</p>
        <?php else: ?>
        <ul class="bars">
            <?php foreach ($topServicios as $s): ?>
            <li class="bars__row">
                <span class="bars__name"><?= e($s['nombre']) ?></span>
                <span class="bars__track"><span class="bars__fill" style="--pct: <?= e($s['pct']) ?>%"></span></span>
                <span class="bars__value"><?= e($s['total']) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>
</div>
