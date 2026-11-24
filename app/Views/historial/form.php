<header class="page-head">
    <div>
        <?php if ($cita): ?>
        <a class="back-link" href="<?= url("/citas/{$cita['id']}") ?>">Volver a la cita</a>
        <?php else: ?>
        <a class="back-link" href="<?= url("/clientes/{$cliente['id']}") ?>">Volver a la ficha</a>
        <?php endif; ?>
        <h1 class="page-title"><?= e($title) ?></h1>
    </div>
</header>

<form class="form form--wide" method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-historial-form>
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="cliente_id" value="<?= e($cliente['id']) ?>">
    <?php if ($cita): ?>
    <input type="hidden" name="cita_id" value="<?= e($cita['id']) ?>">
    <?php endif; ?>

    <section class="card record-summary" data-tour-step="resumen">
        <a class="client-cell" href="<?= url("/clientes/{$cliente['id']}") ?>">
            <?php if ($cliente['foto']): ?>
            <img class="avatar avatar--lg avatar--photo" src="<?= e(foto_url($cliente['foto'], 'clientes')) ?>" alt="">
            <?php else: ?>
            <span class="avatar avatar--lg"><?= e(iniciales($cliente['nombre'])) ?></span>
            <?php endif; ?>
            <span>
                <strong><?= e($cliente['nombre']) ?></strong>
                <?php if ($cliente['preferencias']): ?><span class="table__sub"><?= e($cliente['preferencias']) ?></span><?php endif; ?>
            </span>
        </a>
        <?php if ($cita): ?>
        <dl class="record-summary__facts">
            <div><dt>Servicio</dt><dd><?= e($cita['servicio']) ?></dd></div>
            <div><dt>Estilista</dt><dd><?= e($cita['empleado']) ?></dd></div>
            <div><dt>Fecha</dt><dd><?= e(fecha_corta($cita['fecha_hora'])) ?>, <?= e(hora($cita['fecha_hora'])) ?></dd></div>
        </dl>
        <?php endif; ?>
    </section>

    <div class="form-cols form-cols--historial">
        <section class="card form">
            <h2 class="card__title">Qué se hizo</h2>

            <?php if (!$cita): ?>
            <div class="<?= field_class($errors, 'servicio_id') ?>">
                <label for="servicio_id">Servicio</label>
                <select id="servicio_id" name="servicio_id" required data-servicio-tipo>
                    <option value="">Elige el servicio</option>
                    <?php foreach ($serviciosPorTipo as $grupo => $items): ?>
                    <optgroup label="<?= e($grupo) ?>">
                        <?php foreach ($items as $s): ?>
                        <option value="<?= e($s['id']) ?>" data-tipo="<?= e($s['tipo']) ?>" <?= $valServicioId === (string) $s['id'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
                <?= field_error($errors, 'servicio_id') ?>
            </div>

            <div class="form-row form-row--2">
                <?php if ($esAdmin): ?>
                <div class="<?= field_class($errors, 'empleado_id') ?>">
                    <label for="empleado_id">Lo realizó</label>
                    <select id="empleado_id" name="empleado_id" required>
                        <option value="">Elige quién</option>
                        <?php foreach ($empleados as $emp): ?>
                        <option value="<?= e($emp['id']) ?>" <?= $valEmpleadoId === (string) $emp['id'] ? 'selected' : '' ?>><?= e($emp['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= field_error($errors, 'empleado_id') ?>
                </div>
                <?php endif; ?>
                <div class="<?= field_class($errors, 'fecha') ?>">
                    <label for="fecha">Fecha</label>
                    <input id="fecha" name="fecha" type="date" max="<?= e($hoy) ?>" value="<?= e($valFecha) ?>" required>
                    <?= field_error($errors, 'fecha') ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="<?= field_class($errors, 'formula_tinte') ?> formula-field <?= $formulaDestacada ? 'is-featured' : '' ?>"
                 data-formula data-tipos="<?= e($tiposConFormula) ?>" data-tour-step="formula">
                <label for="formula_tinte">Fórmula o productos usados</label>
                <textarea id="formula_tinte" name="formula_tinte" rows="4" maxlength="1000"
                          placeholder="Ejemplo: 7.44 + 7.4 a partes iguales, oxidante 20 vol. 1:1.5, 35 min"><?= e($valFormula) ?></textarea>
                <p class="field__help">Tonos, proporciones, volumen del oxidante y tiempo de exposición. La próxima vez partirás de aquí.</p>
                <?= field_error($errors, 'formula_tinte') ?>
            </div>

            <?php if ($ultimaFormula): ?>
            <aside class="last-formula" data-tour-step="ultima">
                <p class="label-like">Última fórmula de color, <?= e(fecha_corta($ultimaFormula['fecha'])) ?> (<?= e($ultimaFormula['servicio']) ?>)</p>
                <p class="last-formula__text" data-ultima-formula><?= e($ultimaFormula['formula_tinte']) ?></p>
                <button class="btn btn--sm btn--ghost" type="button" data-copiar-formula>Usar esta fórmula como base</button>
            </aside>
            <?php endif; ?>

            <div class="<?= field_class($errors, 'observaciones') ?>">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="3" maxlength="1000"
                          placeholder="Cómo llegó el cabello, cómo quedó, qué recomendar"><?= e($valObservaciones) ?></textarea>
                <?= field_error($errors, 'observaciones') ?>
            </div>
        </section>

        <section class="card form" data-tour-step="fotos">
            <h2 class="card__title">Fotos</h2>
            <p class="card__hint">Toma la de antes al llegar y la de después al terminar, con la misma luz y el mismo ángulo para que se puedan comparar.</p>

            <div class="photo-pair">
                <div class="photo-drop <?= field_class($errors, 'foto_antes') ?>" data-photo>
                    <span class="label-like">Antes</span>
                    <img class="photo-drop__preview" src="<?= e($fotoAntesUrl ?? '') ?>" alt="Foto de antes" data-photo-preview <?= $fotoAntesUrl ? '' : 'hidden' ?>>
                    <p class="photo-drop__empty" data-photo-empty <?= $fotoAntesUrl ? 'hidden' : '' ?>>Sin foto</p>
                    <label class="btn btn--ghost btn--sm photo-drop__btn">
                        <?= $fotoAntesUrl ? 'Cambiar' : 'Elegir foto' ?>
                        <input class="sr-only" type="file" name="foto_antes" accept="image/jpeg,image/png,image/webp" capture="environment" data-photo-input>
                    </label>
                    <?php if ($fotoAntesUrl): ?>
                    <label class="check"><input type="checkbox" name="quitar_foto_antes" value="1"><span>Quitar</span></label>
                    <?php endif; ?>
                    <?= field_error($errors, 'foto_antes') ?>
                </div>

                <div class="photo-drop <?= field_class($errors, 'foto_despues') ?>" data-photo>
                    <span class="label-like">Después</span>
                    <img class="photo-drop__preview" src="<?= e($fotoDespuesUrl ?? '') ?>" alt="Foto de después" data-photo-preview <?= $fotoDespuesUrl ? '' : 'hidden' ?>>
                    <p class="photo-drop__empty" data-photo-empty <?= $fotoDespuesUrl ? 'hidden' : '' ?>>Sin foto</p>
                    <label class="btn btn--ghost btn--sm photo-drop__btn">
                        <?= $fotoDespuesUrl ? 'Cambiar' : 'Elegir foto' ?>
                        <input class="sr-only" type="file" name="foto_despues" accept="image/jpeg,image/png,image/webp" capture="environment" data-photo-input>
                    </label>
                    <?php if ($fotoDespuesUrl): ?>
                    <label class="check"><input type="checkbox" name="quitar_foto_despues" value="1"><span>Quitar</span></label>
                    <?php endif; ?>
                    <?= field_error($errors, 'foto_despues') ?>
                </div>
            </div>
        </section>
    </div>

    <div class="form__actions form__actions--bar">
        <?php if ($registroId && $esAdmin): ?>
        <button class="btn btn--danger btn--push-left" type="submit" form="form-eliminar">Eliminar registro</button>
        <?php endif; ?>
        <a class="btn btn--ghost" href="<?= url("/clientes/{$cliente['id']}") ?>"><?= $cita && !$registroId ? 'Hacerlo después' : 'Cancelar' ?></a>
        <button class="btn btn--primary" type="submit">Guardar en el historial</button>
    </div>
</form>

<?php if ($registroId && $esAdmin): ?>
<form id="form-eliminar" method="post" action="<?= url("/historial/{$registroId}/eliminar") ?>"
      data-confirm="¿Eliminar este registro del historial y sus fotos? Esta acción no se puede deshacer.">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
</form>
<?php endif; ?>
