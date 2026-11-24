<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a0b2e">
    <meta name="description" content="<?= e(App\Core\Config::get('app.name')) ?>: <?= e($sitio['eslogan'] ?? '') ?>">
    <title><?= e(App\Core\Config::get('app.name')) ?> | <?= e($title ?? '') ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0' stop-color='%23ffd23f'/%3E%3Cstop offset='1' stop-color='%23ff3d9a'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='32' height='32' rx='6' fill='%231a0b2e'/%3E%3Cpath d='M4 22a12 12 0 0 1 24 0z' fill='url(%23g)'/%3E%3Cpath d='M4 17h24M4 20h24' stroke='%231a0b2e' stroke-width='1.5'/%3E%3C/svg%3E">
    <link rel="preload" href="<?= asset('fonts/mr-dafoe-latin-400-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/sitio.css') ?>">
</head>
<body class="layout-site">
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <?= $content ?>
    <script src="<?= asset('js/sitio.js') ?>"></script>
</body>
</html>
