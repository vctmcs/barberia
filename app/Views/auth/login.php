<main class="login">
    <a class="login__back" href="<?= url('/') ?>">Volver al sitio</a>
    <div class="login__scene" aria-hidden="true">
        <div class="login__sun"></div>
        <div class="login__grid"></div>
        <svg class="login__palms" viewBox="0 0 1200 400" preserveAspectRatio="xMidYMax slice">
            <path d="M120 400 C128 300 136 230 160 160 M160 160 C120 140 70 150 30 185 M160 160 C130 120 90 105 50 110 M160 160 C170 120 200 95 245 90 M160 160 C200 140 250 145 290 175 M160 160 C150 125 155 95 175 70"/>
            <path d="M250 400 C252 330 258 285 275 240 M275 240 C245 225 210 232 185 255 M275 240 C255 210 225 200 200 205 M275 240 C285 210 310 195 340 195 M275 240 C305 228 340 235 365 255"/>
            <path d="M1060 400 C1050 290 1040 220 1010 150 M1010 150 C1050 130 1100 140 1145 175 M1010 150 C1040 110 1080 95 1120 100 M1010 150 C1000 110 970 85 925 80 M1010 150 C970 130 920 135 880 165 M1010 150 C1020 115 1015 85 995 60"/>
        </svg>
    </div>

    <section class="login__card">
        <h1 class="login__brand">Hair<span class="brand-plus">+</span></h1>
        <p class="login__lead">Acceso del personal: agenda, historial y recordatorios de tus clientes.</p>

        <?php require BASE_PATH . '/app/Views/partials/flash.php'; ?>

        <?php if ($bloqueo > 0): ?>
        <p class="alert alert--error" role="alert">
            Demasiados intentos fallidos. Vuelve a intentar en <?= e($bloqueo) ?> min.
        </p>
        <?php elseif (!empty($errors['login'])): ?>
        <p class="alert alert--error" role="alert"><?= e($errors['login']) ?></p>
        <?php endif; ?>

        <form class="form" method="post" action="<?= url('/login') ?>" novalidate>
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

            <div class="field">
                <label for="email">Correo</label>
                <input id="email" name="email" type="email" autocomplete="username"
                       value="<?= old($old, 'email') ?>" required autofocus>
            </div>

            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>

            <button class="btn btn--primary btn--block" type="submit" <?= $bloqueo > 0 ? 'disabled' : '' ?>>
                Entrar
            </button>
        </form>
    </section>
</main>
