<header class="page-head">
    <div>
        <a class="back-link" href="<?= url('/clientes') ?>">Volver a clientes</a>
        <h1 class="page-title"><?= e($cliente['nombre']) ?></h1>
        <?php if (!$cliente['activo']): ?><p class="page-sub"><span class="status">Inactivo</span></p><?php endif; ?>
    </div>
    <div class="page-actions">
        <a class="btn btn--primary" href="<?= url("/clientes/{$cliente['id']}/editar") ?>">Editar datos</a>
    </div>
</header>

<div class="profile">
    <aside class="card profile__side">
        <?php if ($cliente['foto']): ?>
        <img class="profile__photo" src="<?= e(foto_url($cliente['foto'], 'clientes')) ?>" alt="Foto de referencia de <?= e($cliente['nombre']) ?>">
        <?php else: ?>
        <div class="profile__photo profile__photo--empty"><span><?= e(iniciales($cliente['nombre'])) ?></span></div>
        <?php endif; ?>

        <dl class="facts">
            <div>
                <dt>Teléfono</dt>
                <dd><?= $cliente['telefono'] ? e(telefono($cliente['telefono'])) : 'Sin registrar' ?></dd>
            </div>
            <div>
                <dt>Correo</dt>
                <dd class="break"><?= $cliente['email'] ? e($cliente['email']) : 'Sin registrar' ?></dd>
            </div>
            <div>
                <dt>Cumpleaños</dt>
                <dd><?= $cliente['fecha_nacimiento'] ? e(fecha_corta($cliente['fecha_nacimiento'])) . ' (' . e($edad) . ' años)' : 'Sin registrar' ?></dd>
            </div>
            <div>
                <dt>Cliente desde</dt>
                <dd><?= e(fecha_corta($cliente['creado_en'])) ?></dd>
            </div>
        </dl>

        <div class="profile__actions">
            <form method="post" action="<?= url("/clientes/{$cliente['id']}/estado") ?>">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn btn--sm btn--block" type="submit"><?= $cliente['activo'] ? 'Pasar a inactivos' : 'Reactivar cliente' ?></button>
            </form>
            <?php if ($isAdmin): ?>
            <form method="post" action="<?= url("/clientes/{$cliente['id']}/eliminar") ?>"
                  data-confirm="¿Eliminar a <?= e($cliente['nombre']) ?>? Solo es posible si no tiene citas registradas.">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn btn--sm btn--danger btn--block" type="submit">Eliminar cliente</button>
            </form>
            <?php endif; ?>
        </div>
    </aside>

    <div class="profile__main">
        <section class="stats stats--compact" aria-label="Resumen del cliente">
            <article class="stat stat--pink">
                <p class="stat__label">Visitas</p>
                <p class="stat__value"><?= e($resumen['visitas']) ?></p>
            </article>
            <article class="stat stat--cyan">
                <p class="stat__label">Total gastado</p>
                <p class="stat__value"><?= e(dinero($resumen['gastado'])) ?></p>
            </article>
            <article class="stat stat--sun">
                <p class="stat__label">Última visita</p>
                <p class="stat__value stat__value--text"><?= e(hace($resumen['ultima'])) ?></p>
            </article>
        </section>

        <div class="grid-2 grid-2--even">
            <section class="card">
                <h2 class="card__title">Preferencias</h2>
                <p class="prose"><?= $cliente['preferencias'] ? nl2br(e($cliente['preferencias'])) : '<span class="muted">Sin preferencias registradas.</span>' ?></p>
            </section>
            <section class="card">
                <h2 class="card__title">Notas internas</h2>
                <p class="prose"><?= $cliente['notas'] ? nl2br(e($cliente['notas'])) : '<span class="muted">Sin notas.</span>' ?></p>
            </section>
        </div>

        <section class="card" data-tour-step="citas">
            <h2 class="card__title">Citas</h2>
            <?php if (!$citas): ?>
            <p class="empty">Aún no tiene citas.</p>
            <?php else: ?>
            <ol class="timeline">
                <?php foreach ($citas as $cita): ?>
                <li><a href="<?= url("/citas/{$cita['id']}") ?>" class="timeline__item timeline__item--wide" style="--emp-color: <?= e($cita['color']) ?>">
                    <time class="timeline__date" datetime="<?= e($cita['fecha_hora']) ?>">
                        <?= e(fecha_corta($cita['fecha_hora'])) ?>
                        <small><?= e(hora($cita['fecha_hora'])) ?></small>
                    </time>
                    <div class="timeline__body">
                        <strong><?= e($cita['servicio']) ?></strong>
                        <span>con <?= e($cita['empleado']) ?>, <?= e(dinero($cita['precio'])) ?></span>
                    </div>
                    <span class="badge badge--<?= e($cita['estado']) ?>"><?= e(estado_cita($cita['estado'])) ?></span>
                </a></li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
        </section>
    </div>
</div>
