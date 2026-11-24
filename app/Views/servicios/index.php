<header class="page-head">
    <div>
        <h1 class="page-title">Servicios</h1>
        <p class="page-sub">Lo que se puede agendar, con su precio y duración.</p>
    </div>
    <a class="btn btn--primary" href="<?= url('/servicios/nuevo') ?>">Nuevo servicio</a>
</header>

<form class="filters" method="get" action="<?= url('/servicios') ?>" role="search">
    <div class="field filters__search">
        <label for="q">Buscar</label>
        <input id="q" name="q" type="search" value="<?= e($filtros['q']) ?>" placeholder="Nombre o descripción">
    </div>
    <div class="field">
        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo">
            <option value="">Todos</option>
            <?php foreach ($tipos as $valor => $texto): ?>
            <option value="<?= e($valor) ?>" <?= $filtros['tipo'] === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
            <?php endforeach; ?>
        </select>
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

<?php if (!$servicios): ?>
<section class="card empty-state">
    <p>No hay servicios con esos filtros.</p>
    <a class="btn btn--ghost" href="<?= url('/servicios/nuevo') ?>">Registrar un servicio</a>
</section>
<?php else: ?>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Servicio</th>
                <th scope="col">Tipo</th>
                <th scope="col" class="num">Duración</th>
                <th scope="col" class="num">Precio</th>
                <th scope="col" class="num">Citas</th>
                <th scope="col">Estado</th>
                <th scope="col"><span class="sr-only">Acciones</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servicios as $s): ?>
            <tr class="<?= $s['activo'] ? '' : 'is-muted' ?>">
                <td class="table__main">
                    <strong><?= e($s['nombre']) ?></strong>
                    <?php if ($s['descripcion']): ?><span class="table__sub"><?= e($s['descripcion']) ?></span><?php endif; ?>
                </td>
                <td data-label="Tipo"><span class="tag tag--<?= e($s['tipo']) ?>"><?= e($tiposCortos[$s['tipo']]) ?></span></td>
                <td class="num" data-label="Duración"><?= e(duracion($s['duracion_min'])) ?></td>
                <td class="num price" data-label="Precio"><?= e(dinero($s['precio'])) ?></td>
                <td class="num" data-label="Citas"><?= e($s['usos']) ?></td>
                <td data-label="Estado"><?= $s['activo'] ? '<span class="status status--on">Activo</span>' : '<span class="status">Inactivo</span>' ?></td>
                <td class="actions">
                    <a class="btn btn--sm btn--ghost" href="<?= url("/servicios/{$s['id']}/editar") ?>">Editar</a>
                    <form method="post" action="<?= url("/servicios/{$s['id']}/estado") ?>">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <button class="btn btn--sm" type="submit"><?= $s['activo'] ? 'Desactivar' : 'Activar' ?></button>
                    </form>
                    <?php if ((int) $s['usos'] === 0): ?>
                    <form method="post" action="<?= url("/servicios/{$s['id']}/eliminar") ?>"
                          data-confirm="¿Eliminar el servicio &quot;<?= e($s['nombre']) ?>&quot;? Esta acción no se puede deshacer.">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <button class="btn btn--sm btn--danger" type="submit">Eliminar</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
