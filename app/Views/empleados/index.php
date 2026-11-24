<header class="page-head">
    <div>
        <h1 class="page-title">Empleados</h1>
        <p class="page-sub">Barberos y estilistas, su horario y su acceso al sistema.</p>
    </div>
    <a class="btn btn--primary" href="<?= url('/empleados/nuevo') ?>">Nuevo empleado</a>
</header>

<form class="filters" method="get" action="<?= url('/empleados') ?>" role="search">
    <div class="field filters__search">
        <label for="q">Buscar</label>
        <input id="q" name="q" type="search" value="<?= e($filtros['q']) ?>" placeholder="Nombre, teléfono o correo">
    </div>
    <div class="field">
        <label for="estado">Estado</label>
        <select id="estado" name="estado">
            <option value="activos" <?= $filtros['estado'] === 'activos' ? 'selected' : '' ?>>Activos</option>
            <option value="inactivos" <?= $filtros['estado'] === 'inactivos' ? 'selected' : '' ?>>Inactivos</option>
            <option value="todos" <?= $filtros['estado'] === 'todos' ? 'selected' : '' ?>>Todos</option>
        </select>
    </div>
    <button class="btn" type="submit">Filtrar</button>
</form>

<?php if (!$empleados): ?>
<section class="card empty-state">
    <p>No hay empleados con esos filtros.</p>
    <a class="btn btn--ghost" href="<?= url('/empleados/nuevo') ?>">Registrar un empleado</a>
</section>
<?php else: ?>
<div class="people">
    <?php foreach ($empleados as $emp): ?>
    <article class="person <?= $emp['activo'] ? '' : 'is-muted' ?>" style="--emp-color: <?= e($emp['color']) ?>">
        <header class="person__head">
            <span class="avatar avatar--lg" style="--avatar-color: <?= e($emp['color']) ?>"><?= e(iniciales($emp['nombre'])) ?></span>
            <div>
                <h2 class="person__name"><?= e($emp['nombre']) ?></h2>
                <p class="person__role"><?= e($especialidades[$emp['especialidad']] ?? $emp['especialidad']) ?></p>
            </div>
            <?php if (!$emp['activo']): ?><span class="status person__status">Inactivo</span><?php endif; ?>
        </header>

        <dl class="person__data">
            <div><dt>Teléfono</dt><dd><?= $emp['telefono'] ? e(telefono($emp['telefono'])) : 'Sin registrar' ?></dd></div>
            <div><dt>Trabaja</dt><dd><?= e($emp['dias']) ?> <?= (int) $emp['dias'] === 1 ? 'día' : 'días' ?> a la semana</dd></div>
            <div><dt>Próximas citas</dt><dd><?= e($emp['proximas']) ?></dd></div>
            <div><dt>Acceso</dt><dd><?= $emp['usuario_email'] ? e($emp['usuario_email']) : 'Sin cuenta' ?></dd></div>
        </dl>

        <footer class="person__actions">
            <a class="btn btn--sm btn--ghost" href="<?= url("/empleados/{$emp['id']}/editar") ?>">Editar</a>
            <form method="post" action="<?= url("/empleados/{$emp['id']}/estado") ?>"
                  <?= $emp['activo'] ? 'data-confirm="' . e($emp['nombre']) . ' dejará de aparecer en la agenda y no podrá iniciar sesión. ¿Continuar?"' : '' ?>>
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn btn--sm" type="submit"><?= $emp['activo'] ? 'Desactivar' : 'Activar' ?></button>
            </form>
        </footer>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
