<header class="page-head">
    <div>
        <a class="back-link" href="<?= url('/servicios') ?>">Volver a servicios</a>
        <h1 class="page-title"><?= e($title) ?></h1>
    </div>
</header>

<form class="card form form--narrow" method="post" action="<?= e($action) ?>" novalidate>
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div class="<?= field_class($errors, 'nombre') ?>">
        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" maxlength="80" value="<?= val($old, $servicio, 'nombre') ?>" required>
        <?= field_error($errors, 'nombre') ?>
    </div>

    <div class="form-row">
        <div class="<?= field_class($errors, 'tipo') ?>">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo">
                <?php foreach ($tipos as $valor => $texto): ?>
                <option value="<?= e($valor) ?>" <?= $tipoActual === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'tipo') ?>
        </div>

        <div class="<?= field_class($errors, 'precio') ?>">
            <label for="precio">Precio (MXN)</label>
            <input id="precio" name="precio" type="number" min="0" step="0.01" inputmode="decimal"
                   value="<?= val($old, $servicio, 'precio') ?>" required>
            <?= field_error($errors, 'precio') ?>
        </div>

        <div class="<?= field_class($errors, 'duracion_min') ?>">
            <label for="duracion_min">Duración (minutos)</label>
            <input id="duracion_min" name="duracion_min" type="number" min="5" step="5" inputmode="numeric"
                   value="<?= val($old, $servicio, 'duracion_min') ?>" required>
            <?= field_error($errors, 'duracion_min') ?>
        </div>
    </div>

    <div class="<?= field_class($errors, 'descripcion') ?>">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" maxlength="255" rows="3"><?= val($old, $servicio, 'descripcion') ?></textarea>
        <?= field_error($errors, 'descripcion') ?>
    </div>

    <label class="check">
        <input type="checkbox" name="activo" value="1" <?= checked($old, $servicio, 'activo') ?>>
        <span>Disponible para agendar</span>
    </label>

    <div class="form__actions">
        <a class="btn btn--ghost" href="<?= url('/servicios') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit">Guardar servicio</button>
    </div>
</form>
