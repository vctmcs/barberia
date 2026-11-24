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

        <?php if ($ultimaFormula): ?>
        <section class="card last-formula last-formula--profile" data-tour-step="formula">
            <h2 class="card__title">Última fórmula de color</h2>
            <p class="card__hint"><?= e(fecha_corta($ultimaFormula['fecha'])) ?>, <?= e($ultimaFormula['servicio']) ?><?= $ultimaFormula['empleado'] ? ' con ' . e($ultimaFormula['empleado']) : '' ?></p>
            <p class="last-formula__text"><?= nl2br(e($ultimaFormula['formula_tinte'])) ?></p>
        </section>
        <?php endif; ?>

        <section class="card" id="historial" data-tour-step="historial">
            <div class="card__head">
                <h2 class="card__title">Historial</h2>
                <a class="btn btn--sm btn--ghost" href="<?= url("/historial/nuevo?cliente={$cliente['id']}") ?>">Registrar servicio</a>
            </div>

            <?php if (!$historial): ?>
            <p class="empty">Aún no hay servicios registrados. Se agregan al marcar una cita como atendida o con el botón de arriba.</p>
            <?php else: ?>
            <ol class="history">
                <?php foreach ($historial as $h): ?>
                <li class="history__item" style="--emp-color: <?= e($h['color'] ?? '#b9a2d6') ?>">
                    <header class="history__head">
                        <div>
                            <p class="history__service"><?= e($h['servicio']) ?></p>
                            <p class="history__meta"><?= e(fecha_larga($h['fecha'])) ?><?= $h['empleado'] ? ', con ' . e($h['empleado']) : '' ?></p>
                        </div>
                        <?php if ($h['editable']): ?>
                        <a class="btn btn--sm btn--ghost" href="<?= url("/historial/{$h['id']}/editar") ?>">Editar</a>
                        <?php endif; ?>
                    </header>

                    <?php if ($h['formula_tinte']): ?>
                    <div class="formula">
                        <span class="label-like">Fórmula</span>
                        <p><?= nl2br(e($h['formula_tinte'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($h['observaciones']): ?>
                    <p class="history__notes"><?= nl2br(e($h['observaciones'])) ?></p>
                    <?php endif; ?>

                    <?php if ($h['antesUrl'] && $h['despuesUrl']): ?>
                    <div class="compare" data-compare>
                        <img class="compare__img" src="<?= e($h['antesUrl']) ?>" alt="Antes: <?= e($h['servicio']) ?>" loading="lazy">
                        <img class="compare__img compare__after" src="<?= e($h['despuesUrl']) ?>" alt="Después: <?= e($h['servicio']) ?>" loading="lazy">
                        <span class="compare__tag compare__tag--antes" aria-hidden="true">Antes</span>
                        <span class="compare__tag compare__tag--despues" aria-hidden="true">Después</span>
                        <span class="compare__handle" aria-hidden="true"></span>
                        <input class="compare__range" type="range" min="0" max="100" value="50"
                               aria-label="Deslizar para comparar antes y después" data-compare-range>
                    </div>
                    <?php elseif ($h['antesUrl'] || $h['despuesUrl']): ?>
                    <figure class="single-photo">
                        <img src="<?= e($h['antesUrl'] ?: $h['despuesUrl']) ?>" alt="<?= $h['antesUrl'] ? 'Antes' : 'Después' ?>: <?= e($h['servicio']) ?>" loading="lazy">
                        <figcaption class="label-like"><?= $h['antesUrl'] ? 'Antes' : 'Después' ?></figcaption>
                    </figure>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
        </section>

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
