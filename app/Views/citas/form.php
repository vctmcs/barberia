<header class="page-head">
    <div>
        <a class="back-link" href="<?= url($citaId ? "/citas/{$citaId}" : '/citas') ?>"><?= $citaId ? 'Volver a la cita' : 'Volver a la agenda' ?></a>
        <h1 class="page-title"><?= e($title) ?></h1>
    </div>
</header>

<form class="form form--wide" method="post" action="<?= e($action) ?>" novalidate
      data-cita-form data-disponibilidad="<?= e($urlDisponibilidad) ?>" data-excepto="<?= e($citaId ?? '') ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <?php if (!empty($errors['fecha'])): ?>
    <p class="alert alert--error" role="alert"><?= e($errors['fecha']) ?></p>
    <?php endif; ?>

    <div class="form-cols form-cols--cita">
        <section class="card form">
            <h2 class="card__title">Quién y qué</h2>

            <div class="<?= field_class($errors, 'cliente_id') ?>" data-tour-step="cliente">
                <label for="cliente_id">Cliente</label>
                <input class="select-filter" type="search" placeholder="Escribe para filtrar la lista" aria-label="Filtrar clientes" data-filtrar="#cliente_id">
                <select id="cliente_id" name="cliente_id" size="5" required>
                    <?php foreach ($clientes as $c): ?>
                    <option value="<?= e($c['id']) ?>" <?= $valClienteId === (string) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['nombre']) ?><?= $c['telefono'] ? ' (' . e(telefono($c['telefono'])) . ')' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <p class="field__help">¿Es nuevo? <a href="<?= url('/clientes/nuevo') ?>">Regístralo primero</a>.</p>
                <?= field_error($errors, 'cliente_id') ?>
            </div>

            <div class="<?= field_class($errors, 'servicio_id') ?>">
                <label for="servicio_id">Servicio</label>
                <select id="servicio_id" name="servicio_id" required data-servicio>
                    <option value="">Elige un servicio</option>
                    <?php foreach ($serviciosPorTipo as $grupo => $items): ?>
                    <optgroup label="<?= e($grupo) ?>">
                        <?php foreach ($items as $s): ?>
                        <option value="<?= e($s['id']) ?>" data-precio="<?= e($s['precio']) ?>" data-duracion="<?= e($s['duracion_min']) ?>"
                                <?= $valServicioId === (string) $s['id'] ? 'selected' : '' ?>>
                            <?= e($s['nombre']) ?>, <?= e(duracion($s['duracion_min'])) ?>, <?= e(precio($s['precio'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
                <?= field_error($errors, 'servicio_id') ?>
            </div>

            <?php if ($esAdmin): ?>
            <div class="<?= field_class($errors, 'empleado_id') ?>">
                <label for="empleado_id">Estilista</label>
                <select id="empleado_id" name="empleado_id" required data-empleado>
                    <option value="">Elige quién atiende</option>
                    <?php foreach ($empleados as $emp): ?>
                    <option value="<?= e($emp['id']) ?>" <?= $valEmpleadoId === (string) $emp['id'] ? 'selected' : '' ?>><?= e($emp['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= field_error($errors, 'empleado_id') ?>
            </div>
            <?php else: ?>
            <input type="hidden" name="empleado_id" value="<?= e($valEmpleadoId) ?>" data-empleado>
            <?php endif; ?>
        </section>

        <section class="card form">
            <h2 class="card__title">Cuándo</h2>

            <div class="form-row form-row--2">
                <div class="<?= field_class($errors, 'fecha') ?>">
                    <label for="fecha">Fecha</label>
                    <input id="fecha" name="fecha" type="date" min="<?= e($hoy) ?>" value="<?= e($valFecha) ?>" required data-fecha>
                </div>
                <div class="<?= field_class($errors, 'fecha') ?>">
                    <label for="hora">Hora</label>
                    <input id="hora" name="hora" type="time" step="900" value="<?= e($valHora) ?>" required data-hora>
                </div>
            </div>

            <div class="slots" data-tour-step="horarios" aria-live="polite">
                <p class="slots__title">Horarios libres</p>
                <p class="slots__msg" data-slots-msg>Elige servicio<?= $esAdmin ? ', estilista' : '' ?> y fecha para ver los horarios libres.</p>
                <div class="slots__list" data-slots></div>
            </div>

            <div class="form-row form-row--2">
                <div class="<?= field_class($errors, 'precio') ?>">
                    <label for="precio">Precio a cobrar (MXN)</label>
                    <input id="precio" name="precio" type="number" min="0" step="0.01" inputmode="decimal" value="<?= e($valPrecio) ?>" data-precio-input>
                    <p class="field__help">Vacío usa el precio del servicio.</p>
                    <?= field_error($errors, 'precio') ?>
                </div>
                <div class="field">
                    <span class="label-like">Termina aprox.</span>
                    <p class="end-time" data-termina>Sin calcular</p>
                </div>
            </div>

            <div class="<?= field_class($errors, 'notas') ?>">
                <label for="notas">Notas de la cita</label>
                <textarea id="notas" name="notas" rows="3" maxlength="500" placeholder="Por ejemplo: trae foto de referencia"><?= e($valNotas) ?></textarea>
                <?= field_error($errors, 'notas') ?>
            </div>
        </section>
    </div>

    <div class="form__actions form__actions--bar">
        <a class="btn btn--ghost" href="<?= url($citaId ? "/citas/{$citaId}" : '/citas') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit"><?= $citaId ? 'Guardar cambios' : 'Agendar cita' ?></button>
    </div>
</form>
