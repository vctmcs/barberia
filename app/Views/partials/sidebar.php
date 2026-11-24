<aside id="sidebar" class="sidebar">
    <a class="brand" href="<?= url('/dashboard') ?>">
        <span class="brand__script">Hair<span class="brand-plus">+</span></span>
        <span class="brand__sub">Panel de la estética</span>
    </a>

    <nav class="nav" aria-label="Principal">
        <a class="nav__link <?= active('dashboard') ?>" href="<?= url('/dashboard') ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h6V4H4zM14 20h6v-9h-6zM4 20h6v-4H4zM14 4v4h6V4z"/></svg>
            Dashboard
        </a>
        <a class="nav__link <?= active('citas') ?>" href="<?= url('/citas') ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v14H4zM4 10h16M8 3v5M16 3v5"/></svg>
            Agenda
        </a>
        <a class="nav__link <?= active('clientes') ?>" href="<?= url('/clientes') ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
            Clientes
        </a>
        <a class="nav__link <?= active('notificaciones') ?>" href="<?= url('/notificaciones') ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 16V11a6 6 0 1 1 12 0v5l2 2H4zM10 21h4"/></svg>
            Notificaciones
            <?php if (!empty($avisosCount)): ?>
            <span class="nav__badge" aria-label="<?= e($avisosCount) ?> pendientes"><?= e($avisosCount > 99 ? '99+' : $avisosCount) ?></span>
            <?php endif; ?>
        </a>

        <?php if ($isAdmin): ?>
        <p class="nav__group">Administración</p>
        <a class="nav__link <?= active('servicios') ?>" href="<?= url('/servicios') ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6a3 3 0 1 0 0 .01M6 18a3 3 0 1 0 0 .01M8.5 7.5 20 18M8.5 16.5 20 6"/></svg>
            Servicios
        </a>
        <a class="nav__link <?= active('empleados') ?>" href="<?= url('/empleados') ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM2 20c0-3.5 3-5.5 7-5.5s7 2 7 5.5M16 4.5a3.5 3.5 0 0 1 0 6.5M18 14.8c2.4.6 4 2.3 4 5.2"/></svg>
            Empleados
        </a>
        <?php endif; ?>
    </nav>

    <a class="nav__site" href="<?= url('/') ?>">Ver página pública</a>
    <div class="sidebar__sun" aria-hidden="true"></div>
</aside>
<div class="sidebar-backdrop" data-toggle-sidebar hidden></div>
