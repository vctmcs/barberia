<header class="page-head">
    <div>
        <a class="back-link" href="<?= url('/citas?fecha=' . substr($cita['fecha_hora'], 0, 10)) ?>">Volver a la agenda</a>
        <h1 class="page-title"><?= e($cita['cliente']) ?></h1>
        <p class="page-sub"><span class="badge badge--<?= e($cita['estado']) ?>"><?= e($estados[$cita['estado']]) ?></span></p>
    </div>
    <?php if ($editable): ?>
    <div class="page-actions">
        <a class="btn btn--primary" href="<?= url("/citas/{$cita['id']}/editar") ?>">Editar o reprogramar</a>
    </div>
    <?php endif; ?>
</header>

<div class="appointment">
    <section class="card appointment__main" style="--emp-color: <?= e($cita['color']) ?>" data-tour-step="resumen">
        <div class="appointment__when">
            <p class="appointment__date"><?= e(fecha_larga($cita['fecha_hora'])) ?></p>
            <p class="appointment__time"><?= e(hora($cita['fecha_hora'])) ?> a <?= e(hora($cita['fecha_fin'])) ?></p>
        </div>

        <dl class="facts facts--grid">
            <div><dt>Servicio</dt><dd><?= e($cita['servicio']) ?></dd></div>
            <div><dt>Duración</dt><dd><?= e(duracion($cita['duracion_min'])) ?></dd></div>
            <div><dt>Estilista</dt><dd><span class="dot" aria-hidden="true"></span><?= e($cita['empleado']) ?></dd></div>
            <div><dt>Precio</dt><dd class="price"><?= e(dinero($cita['precio'])) ?></dd></div>
        </dl>

        <?php if ($cita['notas']): ?>
        <div class="appointment__notes">
            <p class="label-like">Notas de la cita</p>
            <p><?= nl2br(e($cita['notas'])) ?></p>
        </div>
        <?php endif; ?>
    </section>

    <aside class="appointment__side">
        <section class="card" data-tour-step="estado">
            <h2 class="card__title">Estado</h2>
            <p class="card__hint">
                <?php if ($cita['estado'] === 'programada' && !$yaEmpezo): ?>
                Cuando llegue la hora podrás marcarla como atendida o como no asistió.
                <?php elseif ($cita['estado'] === 'programada'): ?>
                La hora ya pasó. Registra qué sucedió.
                <?php else: ?>
                Si fue un error, puedes volver a programarla.
                <?php endif; ?>
            </p>

            <div class="state-actions">
                <?php if ($cita['estado'] === 'programada'): ?>
                    <?php if ($yaEmpezo): ?>
                    <form method="post" action="<?= url("/citas/{$cita['id']}/estado") ?>">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="estado" value="atendida">
                        <button class="btn btn--primary btn--block" type="submit">Marcar como atendida</button>
                    </form>
                    <form method="post" action="<?= url("/citas/{$cita['id']}/estado") ?>">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="estado" value="no_asistio">
                        <button class="btn btn--block" type="submit">No asistió</button>
                    </form>
                    <?php endif; ?>
                    <form method="post" action="<?= url("/citas/{$cita['id']}/estado") ?>"
                          data-confirm="¿Cancelar la cita de <?= e($cita['cliente']) ?>? El horario quedará libre para otra persona.">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="estado" value="cancelada">
                        <button class="btn btn--danger btn--block" type="submit">Cancelar cita</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= url("/citas/{$cita['id']}/estado") ?>">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="estado" value="programada">
                        <button class="btn btn--block" type="submit">Volver a programar</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($cita['estado'] === 'programada' && !$yaEmpezo): ?>
        <section class="card" data-tour-step="recordatorio">
            <h2 class="card__title">Recordatorio</h2>

            <?php if (isset($recordatorios['correo'])): ?>
            <p class="reminder-line"><span class="status status--on">Correo</span> enviado el <?= e(fecha_corta($recordatorios['correo']['enviado_en'])) ?> a las <?= e(hora($recordatorios['correo']['enviado_en'])) ?></p>
            <?php endif; ?>
            <?php if (isset($recordatorios['whatsapp'])): ?>
            <p class="reminder-line"><span class="status status--on">WhatsApp</span> abierto el <?= e(fecha_corta($recordatorios['whatsapp']['enviado_en'])) ?> a las <?= e(hora($recordatorios['whatsapp']['enviado_en'])) ?></p>
            <?php endif; ?>

            <?php if ($tieneTelefono): ?>
            <form class="form" method="post" action="<?= url("/citas/{$cita['id']}/whatsapp") ?>" target="_blank" rel="noopener">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <div class="field">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="4"><?= e($mensajeWa) ?></textarea>
                </div>
                <button class="btn btn--ghost btn--block" type="submit">Enviar por WhatsApp</button>
            </form>
            <?php else: ?>
            <p class="card__hint">El cliente no tiene teléfono registrado.</p>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <section class="card client-mini">
            <a class="client-cell" href="<?= url("/clientes/{$cita['cliente_id']}") ?>">
                <?php if ($fotoCliente): ?>
                <img class="avatar avatar--lg avatar--photo" src="<?= e($fotoCliente) ?>" alt="">
                <?php else: ?>
                <span class="avatar avatar--lg"><?= e(iniciales($cita['cliente'])) ?></span>
                <?php endif; ?>
                <span>
                    <strong><?= e($cita['cliente']) ?></strong>
                    <span class="table__sub">Ver ficha del cliente</span>
                </span>
            </a>
            <?php if ($cita['cliente_preferencias']): ?>
            <p class="client-mini__pref"><span class="label-like">Preferencias</span><?= e($cita['cliente_preferencias']) ?></p>
            <?php endif; ?>
            <?php if ($cita['cliente_notas']): ?>
            <p class="client-mini__pref"><span class="label-like">Notas internas</span><?= e($cita['cliente_notas']) ?></p>
            <?php endif; ?>
        </section>
    </aside>
</div>
