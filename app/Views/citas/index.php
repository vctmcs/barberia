<header class="page-head">
    <div>
        <h1 class="page-title">Agenda</h1>
        <p class="page-sub">Toca un espacio libre para agendar o arrastra una cita para cambiarla de hora.</p>
    </div>
    <a class="btn btn--primary" href="<?= url('/citas/nueva') ?>" data-nueva-cita>Nueva cita</a>
</header>

<?php if ($pendientes > 0): ?>
<p class="alert alert--info" role="status">
    Hay <?= e($pendientes) ?> <?= $pendientes === 1 ? 'cita que ya pasó y sigue' : 'citas que ya pasaron y siguen' ?> como programada. Ábrelas para marcar si se atendieron.
</p>
<?php endif; ?>

<div class="agenda-tools">
    <?php if ($puedeFiltrar): ?>
    <div class="agenda-people" role="group" aria-label="Filtrar por estilista" data-tour-step="filtro">
        <a class="chip <?= $empleadoId === null ? 'is-on' : '' ?>" href="<?= url('/citas') ?>" data-filtro-empleado="">Todo el equipo</a>
        <?php foreach ($empleados as $emp): ?>
        <a class="chip <?= $empleadoId === (int) $emp['id'] ? 'is-on' : '' ?>" style="--chip: <?= e($emp['color']) ?>"
           href="<?= url('/citas?empleado=' . $emp['id']) ?>" data-filtro-empleado="<?= e($emp['id']) ?>">
            <span class="chip__dot" aria-hidden="true"></span><?= e($emp['nombre']) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <label class="check agenda-tools__cancel">
        <input type="checkbox" data-ver-canceladas>
        <span>Mostrar canceladas</span>
    </label>
</div>

<section class="card calendar-card" data-tour-step="calendario">
    <div id="calendario" class="calendar" data-config="<?= e($configJson) ?>" aria-label="Calendario de citas"></div>
    <p class="calendar-legend" aria-hidden="true">
        <span class="legend legend--programada">Programada</span>
        <span class="legend legend--atendida">Atendida</span>
        <span class="legend legend--no_asistio">No asistió</span>
        <span class="legend legend--cancelada">Cancelada</span>
    </p>
</section>

<div class="toast" role="status" aria-live="polite" data-toast hidden></div>
