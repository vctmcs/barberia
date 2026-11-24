<header class="page-head">
    <div>
        <?php if (!empty($cliente['id'])): ?>
        <a class="back-link" href="<?= url("/clientes/{$cliente['id']}") ?>">Volver a la ficha</a>
        <?php else: ?>
        <a class="back-link" href="<?= url('/clientes') ?>">Volver a clientes</a>
        <?php endif; ?>
        <h1 class="page-title"><?= e($title) ?></h1>
    </div>
</header>

<form class="form form--wide" method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div class="form-cols form-cols--photo">
        <section class="card form">
            <h2 class="card__title">Datos de contacto</h2>

            <div class="<?= field_class($errors, 'nombre') ?>">
                <label for="nombre">Nombre completo</label>
                <input id="nombre" name="nombre" maxlength="80" value="<?= val($old, $cliente, 'nombre') ?>" required>
                <?= field_error($errors, 'nombre') ?>
            </div>

            <div class="form-row form-row--2" data-tour-step="contacto">
                <div class="<?= field_class($errors, 'telefono') ?>">
                    <label for="telefono">Teléfono (WhatsApp)</label>
                    <input id="telefono" name="telefono" type="tel" inputmode="numeric" maxlength="14"
                           value="<?= val($old, $cliente, 'telefono') ?>" placeholder="10 dígitos">
                    <?= field_error($errors, 'telefono') ?>
                </div>
                <div class="<?= field_class($errors, 'email') ?>">
                    <label for="email">Correo</label>
                    <input id="email" name="email" type="email" maxlength="120" value="<?= val($old, $cliente, 'email') ?>">
                    <?= field_error($errors, 'email') ?>
                </div>
            </div>

            <div class="<?= field_class($errors, 'fecha_nacimiento') ?> field--short">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" max="<?= e(date('Y-m-d')) ?>"
                       value="<?= val($old, $cliente, 'fecha_nacimiento') ?>">
                <p class="field__help">Para avisarte de su cumpleaños.</p>
                <?= field_error($errors, 'fecha_nacimiento') ?>
            </div>

            <div class="<?= field_class($errors, 'preferencias') ?>">
                <label for="preferencias">Preferencias</label>
                <textarea id="preferencias" name="preferencias" maxlength="500" rows="3"
                          placeholder="Cómo le gusta el corte, productos, horario preferido"><?= val($old, $cliente, 'preferencias') ?></textarea>
                <?= field_error($errors, 'preferencias') ?>
            </div>

            <div class="<?= field_class($errors, 'notas') ?>">
                <label for="notas">Notas internas</label>
                <textarea id="notas" name="notas" maxlength="1000" rows="3"
                          placeholder="Alergias a productos, observaciones del personal"><?= val($old, $cliente, 'notas') ?></textarea>
                <?= field_error($errors, 'notas') ?>
            </div>

            <label class="check">
                <input type="checkbox" name="activo" value="1" <?= checked($old, $cliente, 'activo') ?>>
                <span>Cliente activo</span>
            </label>
        </section>

        <section class="card form photo-card" data-photo>
            <h2 class="card__title">Foto de referencia</h2>
            <p class="card__hint">El corte o el color que quiere conservar. JPG, PNG o WebP de hasta 4 MB.</p>

            <div class="photo-drop <?= field_class($errors, 'foto') ?>">
                <img class="photo-drop__preview" src="<?= e($fotoUrl ?? '') ?>" alt="Vista previa de la foto" data-photo-preview <?= $fotoUrl ? '' : 'hidden' ?>>
                <p class="photo-drop__empty" data-photo-empty <?= $fotoUrl ? 'hidden' : '' ?>>Sin foto</p>
                <label class="btn btn--ghost photo-drop__btn">
                    <?= $fotoUrl ? 'Cambiar foto' : 'Elegir foto' ?>
                    <input class="sr-only" type="file" name="foto" accept="image/jpeg,image/png,image/webp" data-photo-input>
                </label>
                <?= field_error($errors, 'foto') ?>
            </div>

            <?php if ($fotoUrl): ?>
            <label class="check">
                <input type="checkbox" name="quitar_foto" value="1">
                <span>Quitar la foto actual</span>
            </label>
            <?php endif; ?>
        </section>
    </div>

    <div class="form__actions form__actions--bar">
        <a class="btn btn--ghost" href="<?= url(!empty($cliente['id']) ? "/clientes/{$cliente['id']}" : '/clientes') ?>">Cancelar</a>
        <button class="btn btn--primary" type="submit">Guardar cliente</button>
    </div>
</form>
