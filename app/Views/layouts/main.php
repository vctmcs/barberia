<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a0b2e">
    <title><?= e($title ?? '') ?> | <?= e(App\Core\Config::get('app.name')) ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0' stop-color='%23ffd23f'/%3E%3Cstop offset='1' stop-color='%23ff3d9a'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='32' height='32' rx='6' fill='%231a0b2e'/%3E%3Cpath d='M4 22a12 12 0 0 1 24 0z' fill='url(%23g)'/%3E%3Cpath d='M4 17h24M4 20h24' stroke='%231a0b2e' stroke-width='1.5'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php foreach ($styles ?? [] as $style): ?>
    <link rel="stylesheet" href="<?= e($style) ?>">
    <?php endforeach; ?>
</head>
<body class="layout-app"<?php if (!empty($tour)): ?>
      data-tour="<?= e($tour) ?>" data-tour-pending="<?= !empty($tourPendiente) ? '1' : '0' ?>"
      data-tour-url="<?= url('/recorridos/' . $tour) ?>" data-csrf="<?= e($csrf) ?>"<?php endif; ?>>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>

    <?php require BASE_PATH . '/app/Views/partials/sidebar.php'; ?>

    <div class="app-main">
        <header class="topbar">
            <button class="topbar__menu" type="button" data-toggle-sidebar aria-controls="sidebar" aria-expanded="false">
                <span class="sr-only">Abrir menú</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>

            <?php if (!empty($tour)): ?>
            <button class="btn btn--ghost btn--sm topbar__tour" type="button" data-tour-replay>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM9.5 9.2a2.6 2.6 0 0 1 5 .9c0 1.7-2.5 2.2-2.5 3.6M12 17h.01"/></svg>
                <span>Ver recorrido</span>
            </button>
            <?php endif; ?>

            <div class="topbar__user">
                <span class="avatar" style="--avatar-color: <?= e($authUser['color'] ?? '#2de2e6') ?>"><?= e(iniciales($authUser['nombre'])) ?></span>
                <a class="topbar__who" href="<?= url('/cuenta') ?>" title="Mi cuenta">
                    <strong><?= e($authUser['nombre']) ?></strong>
                    <small><?= $isAdmin ? 'Administrador' : 'Barbero / estilista' ?></small>
                </a>
                <form method="post" action="<?= url('/logout') ?>">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <button class="btn btn--ghost btn--sm" type="submit">Cerrar sesión</button>
                </form>
            </div>
        </header>

        <main id="contenido" class="content" tabindex="-1">
            <?php require BASE_PATH . '/app/Views/partials/flash.php'; ?>
            <?php if ($passwordDemo): ?>
            <p class="alert alert--warn" role="alert">
                Entraste con la contraseña de los datos de prueba. <a href="<?= url('/cuenta') ?>">Cámbiala ahora</a> antes de publicar el sitio.
            </p>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>

    <?php require BASE_PATH . '/app/Views/partials/confirm.php'; ?>

    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if (!empty($tour)): ?>
    <script src="<?= asset('js/recorridos.js') ?>"></script>
    <?php endif; ?>
    <?php foreach ($scripts ?? [] as $script): ?>
    <script src="<?= e($script) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
