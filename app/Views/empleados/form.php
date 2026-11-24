<header class="page-head">
    <div>
        <a class="back-link" href="<?= url('/empleados') ?>">Volver a empleados</a>
        <h1 class="page-title"><?= e($title) ?></h1>
    </div>
</header>

<form class="form form--wide" method="post" action="<?= e($action) ?>" novalidate>
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div class="form-cols form-cols--empleado">
        <div class="form-stack">
        <section class="card form">
            <h2 class="card__title">Datos</h2>

            <div class="<?= field_class($errors, 'nombre') ?>">
                <label for="nombre">Nombre completo</label>
                <input id="nombre" name="nombre" maxlength="80" value="<?= val($old, $empleado, 'nombre') ?>" required>
                <?= field_error($errors, 'nombre') ?>
            </div>

            <div class="form-row form-row--2">
                <div class="<?= field_class($errors, 'telefono') ?>">
                    <label for="telefono">Teléfono</label>
                    <input id="telefono" name="telefono" type="tel" inputmode="numeric" maxlength="14"
                           value="<?= val($old, $empleado, 'telefono') ?>" placeholder="10 dígitos">
                    <?= field_error($errors, 'telefono') ?>
                </div>
                <div class="<?= field_class($errors, 'email') ?>">
                    <label for="email">Correo de contacto</label>
                    <input id="email" name="email" type="email" maxlength="120" value="<?= val($old, $empleado, 'email') ?>">
                    <?= field_error($errors, 'email') ?>
                </div>
            </div>

            <div class="<?= field_class($errors, 'especialidad') ?>">
                <label for="especialidad">Especialidad</label>
                <select id="especialidad" name="especialidad">
                    <?php foreach ($especialidades as $valor => $texto): ?>
                    <option value="<?= e($valor) ?>" <?= $espActual === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <fieldset class="<?= field_class($errors, 'color') ?> swatches">
                <legend>Color en la agenda</legend>
                <div class="swatches__list">
                    <?php foreach ($colores as $c): ?>
                    <label class="swatch" style="--swatch: <?= e($c) ?>">
                        <input type="radio" name="color" value="<?= e($c) ?>" <?= $colorActual === $c ? 'checked' : '' ?>>
                        <span class="sr-only"><?= e($c) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?= field_error($errors, 'color') ?>
            </fieldset>

            <label class="check">
                <input type="checkbox" name="activo" value="1" <?= checked($old, $empleado, 'activo') ?>>
                <span>Activo (aparece en la agenda)</span>
            </label>
        </section>

        <section class="card form" data-tour-step="acceso">
            <h2 class="card__title">Acceso al sistema</h2>

            <?php if ($tieneCuenta): ?>
            <p class="card__hint">Inicia sesión con este correo y ve solo su propia agenda.</p>
            <?php else: ?>
            <label class="check">
                <input type="checkbox" name="acceso" value="1" <?= $conAcceso ? 'checked' : '' ?> data-reveal="#acceso-campos">
                <span>Crear cuenta para que vea su agenda</span>
            </label>
            <?php endif; ?>

            <div id="acceso-campos" class="form" <?= $conAcceso ? '' : 'hidden' ?>>
                <div class="<?= field_class($errors, 'acceso_email') ?>">
                    <label for="acceso_email">Correo para iniciar sesión</label>
                    <input id="acceso_email" name="acceso_email" type="email" autocomplete="off"
                           value="<?= e($old['acceso_email'] ?? $empleado['usuario_email'] ?? '') ?>">
                    <?= field_error($errors, 'acceso_email') ?>
                </div>
                <div class="<?= field_class($errors, 'acceso_password') ?>">
                    <label for="acceso_password"><?= $tieneCuenta ? 'Nueva contraseña' : 'Contraseña' ?></label>
                    <input id="acceso_password" name="acceso_password" type="password" autocomplete="new-password" minlength="8">
                    <p class="field__help"><?= $tieneCuenta ? 'Déjala vacía para conservar la actual.' : 'Mínimo 8 caracteres.' ?></p>
                    <?= field_error($errors, 'acceso_password') ?>
                </div>
            </div>
        </section>
        </div>

        <section class="card form" data-tour-step="horario">
            <h2 class="card__title">Horario semanal</h2>
            <p class="card__hint">Marca los días que trabaja. Las citas solo se podrán agendar dentro de este horario.</p>

            <div class="schedule">
                <?php foreach ($horarioForm as $num => $h): ?>
                <div class="schedule__row <?= isset($errors["horario_{$num}"]) ? 'has-error' : '' ?>" data-schedule-row>
                    <label class="check schedule__day">
                        <input type="checkbox" name="horario[<?= $num ?>][trabaja]" value="1" <?= $h['trabaja'] ? 'checked' : '' ?> data-schedule-toggle>
                        <span><?= e($h['dia']) ?></span>
                    </label>
                    <label class="sr-only" for="ini-<?= $num ?>">Entrada del <?= e($h['dia']) ?></label>
                    <input id="ini-<?= $num ?>" type="time" step="900" name="horario[<?= $num ?>][inicio]" value="<?= e($h['inicio']) ?>">
                    <span class="schedule__to" aria-hidden="true">a</span>
                    <label class="sr-only" for="fin-<?= $num ?>">Salida del <?= e($h['dia']) ?></label>
                    <input id="fin-<?= $num ?>" type="time" step="900" name="horario[<?= $num ?>][fin]" value="<?= e($h['fin']) ?>">
                    <?= field_error($errors, "horario_{$num}") ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>

    <div class="form__actions form__actions--bar">
        <?php if (!empty($empleado['id'])): ?>
        <button class="btn btn--danger btn--push-left" type="submit" form="form-eliminar">Eliminar empleado</button>
        <?php endif; ?>
        <a class="btn btn--ghost" href="<?= url('/empleados') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit">Guardar empleado</button>
    </div>
</form>

<?php if (!empty($empleado['id'])): ?>
<form id="form-eliminar" method="post" action="<?= url("/empleados/{$empleado['id']}/eliminar") ?>"
      data-confirm="¿Eliminar a <?= e($empleado['nombre']) ?> y su cuenta de acceso? Solo es posible si no tiene citas registradas.">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
</form>
<?php endif; ?>
