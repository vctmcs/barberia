<header class="page-head">
    <div>
        <h1 class="page-title">Mi cuenta</h1>
        <p class="page-sub">
            <?= $isAdmin ? 'Administrador' : 'Barbero / estilista' ?><?php if ($ultimoAcceso): ?>, sesión iniciada el <?= e(fecha_corta($ultimoAcceso)) ?> a las <?= e(hora($ultimoAcceso)) ?><?php endif; ?>
        </p>
    </div>
</header>

<form class="form form--wide" method="post" action="<?= url('/cuenta') ?>" novalidate>
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div class="form-cols">
        <section class="card form" data-tour-step="datos">
            <h2 class="card__title">Tus datos</h2>

            <div class="<?= field_class($errors, 'nombre') ?>">
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" maxlength="80" autocomplete="name" value="<?= val($old, $cuenta, 'nombre') ?>" required>
                <?= field_error($errors, 'nombre') ?>
            </div>

            <div class="<?= field_class($errors, 'email') ?>">
                <label for="email">Correo para iniciar sesión</label>
                <input id="email" name="email" type="email" autocomplete="username" value="<?= val($old, $cuenta, 'email') ?>" required>
                <?= field_error($errors, 'email') ?>
            </div>
        </section>

        <section class="card form" data-tour-step="password">
            <h2 class="card__title">Cambiar contraseña</h2>
            <p class="card__hint">Déjalo vacío si no quieres cambiarla. Usa al menos 8 caracteres; una frase de varias palabras es más fácil de recordar y más difícil de adivinar.</p>

            <div class="<?= field_class($errors, 'password_nueva') ?>">
                <label for="password_nueva">Nueva contraseña</label>
                <input id="password_nueva" name="password_nueva" type="password" minlength="8" autocomplete="new-password">
                <?= field_error($errors, 'password_nueva') ?>
            </div>

            <div class="<?= field_class($errors, 'password_repetir') ?>">
                <label for="password_repetir">Repite la nueva contraseña</label>
                <input id="password_repetir" name="password_repetir" type="password" minlength="8" autocomplete="new-password">
                <?= field_error($errors, 'password_repetir') ?>
            </div>
        </section>
    </div>

    <section class="card form confirm-card" data-tour-step="actual">
        <div class="<?= field_class($errors, 'password_actual') ?> field--short-wide">
            <label for="password_actual">Contraseña actual</label>
            <input id="password_actual" name="password_actual" type="password" autocomplete="current-password">
            <p class="field__help">Necesaria solo si cambias el correo o la contraseña.</p>
            <?= field_error($errors, 'password_actual') ?>
        </div>
    </section>

    <div class="form__actions form__actions--bar">
        <button class="btn btn--primary" type="submit">Guardar cambios</button>
    </div>
</form>
