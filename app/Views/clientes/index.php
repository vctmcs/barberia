<header class="page-head">
    <div>
        <h1 class="page-title">Clientes</h1>
        <p class="page-sub"><?= e($total) ?> <?= $total === 1 ? 'cliente' : 'clientes' ?> en esta vista</p>
    </div>
    <a class="btn btn--primary" href="<?= url('/clientes/nuevo') ?>">Nuevo cliente</a>
</header>

<form class="filters" method="get" action="<?= url('/clientes') ?>" role="search">
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

<?php if (!$clientes): ?>
<section class="card empty-state">
    <?php if ($filtros['q'] !== ''): ?>
    <p>Nadie coincide con "<?= e($filtros['q']) ?>". Revisa la ortografía o registra al cliente.</p>
    <?php else: ?>
    <p>Todavía no hay clientes en esta vista.</p>
    <?php endif; ?>
    <a class="btn btn--ghost" href="<?= url('/clientes/nuevo') ?>">Registrar cliente</a>
</section>
<?php else: ?>
<div class="table-wrap">
    <table class="table table--clients">
        <thead>
            <tr>
                <th scope="col">Cliente</th>
                <th scope="col">Contacto</th>
                <th scope="col">Última visita</th>
                <th scope="col">Próxima cita</th>
                <th scope="col"><span class="sr-only">Acciones</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes as $c): ?>
            <tr class="<?= $c['activo'] ? '' : 'is-muted' ?>">
                <td class="table__main">
                    <a class="client-cell" href="<?= url("/clientes/{$c['id']}") ?>">
                        <?php if ($c['foto']): ?>
                        <img class="avatar avatar--photo" src="<?= e(foto_url($c['foto'], 'clientes')) ?>" alt="" loading="lazy">
                        <?php else: ?>
                        <span class="avatar"><?= e(iniciales($c['nombre'])) ?></span>
                        <?php endif; ?>
                        <span>
                            <strong><?= e($c['nombre']) ?></strong>
                            <?php if ($c['preferencias']): ?><span class="table__sub"><?= e($c['preferencias']) ?></span><?php endif; ?>
                        </span>
                    </a>
                </td>
                <td data-label="Contacto">
                    <?php if ($c['telefono']): ?><span class="nowrap"><?= e(telefono($c['telefono'])) ?></span><?php endif; ?>
                    <?php if ($c['email']): ?><span class="table__sub"><?= e($c['email']) ?></span><?php endif; ?>
                </td>
                <td data-label="Última visita"><?= e(hace($c['ultima_visita'])) ?></td>
                <td data-label="Próxima cita">
                    <?php if ($c['proxima_cita']): ?>
                    <span class="nowrap"><?= e(fecha_corta($c['proxima_cita'])) ?>, <?= e(hora($c['proxima_cita'])) ?></span>
                    <?php else: ?>
                    <span class="muted">Sin agendar</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <a class="btn btn--sm btn--ghost" href="<?= url("/clientes/{$c['id']}") ?>">Ver ficha</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($paginas > 1): ?>
<nav class="pager" aria-label="Paginación">
    <?php if ($pagina > 1): ?>
    <a class="btn btn--sm" href="<?= url('/clientes') . e(query($filtros, ['pagina' => $pagina - 1])) ?>">Anterior</a>
    <?php endif; ?>
    <span class="pager__info">Página <?= e($pagina) ?> de <?= e($paginas) ?></span>
    <?php if ($pagina < $paginas): ?>
    <a class="btn btn--sm" href="<?= url('/clientes') . e(query($filtros, ['pagina' => $pagina + 1])) ?>">Siguiente</a>
    <?php endif; ?>
</nav>
<?php endif; ?>
<?php endif; ?>
