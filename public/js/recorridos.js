/*
 * Recorrido guiado por seccion.
 *
 * - Se abre solo la primera vez que el usuario entra a una seccion con
 *   captura o consulta de datos (lo decide el servidor: data-tour-pending).
 * - Se puede saltar en cualquier momento (boton o tecla Escape).
 * - Al terminarlo o saltarlo se guarda como visto en el servidor.
 * - El boton "Ver recorrido" de la barra superior lo vuelve a abrir.
 *
 * Cada paso apunta a un selector; si el elemento no existe o no se ve
 * (por ejemplo, una tabla vacia o el menu oculto en celular), el paso
 * se omite.
 */
(function () {
    'use strict';

    var body = document.body;
    var clave = body.dataset.tour;
    if (!clave) return;

    var REPLAY = {
        el: '[data-tour-replay]',
        title: 'Vuelve a verlo cuando quieras',
        text: 'Este recorrido ya no se abrirá solo. Si lo necesitas otra vez, usa este botón.'
    };

    var PASOS = {
        'dashboard': [
            { el: '.stats', title: 'Tu día en números', text: 'Citas de hoy, lo que se ha cobrado en el mes (solo citas atendidas) y cuántos clientes activos hay. Si entras como estilista, solo ves lo tuyo.' },
            { el: '[data-tour-step="agenda"]', title: 'Agenda del día', text: 'Las citas de hoy en orden. El color de la orilla es el del estilista y la etiqueta indica si ya se atendió.' },
            { el: '[data-tour-step="top"]', title: 'Lo que más piden', text: 'Los servicios más solicitados de los últimos 90 días, para saber qué promocionar.' },
            { el: '.nav', title: 'Secciones', text: 'Desde aquí llegas a la agenda, a los clientes y, si eres administrador, a servicios y empleados.' },
            REPLAY
        ],
        'notificaciones': [
            { el: '[data-tour-step="hoy"]', title: 'Para hoy', text: 'Las citas que faltan por atender hoy. Toca una para abrirla.' },
            { el: '[data-tour-step="cerrar"]', title: 'Por cerrar', text: 'Citas cuya hora ya pasó y siguen como programadas. Ábrelas y marca si se atendieron o no asistieron.' },
            { el: '[data-tour-step="historial"]', title: 'Sin registrar', text: 'Servicios atendidos a los que les falta la fórmula o las fotos.' },
            { el: '[data-tour-step="cumple"]', title: 'Cumpleaños', text: 'Quién cumple esta semana. El botón abre WhatsApp con una felicitación lista para enviar.' },
            { el: '[data-tour-step="inactivos"]', title: 'Clientes que no regresan', text: 'Escríbeles para invitarlos de nuevo o agéndales directamente.' },
            { el: '[data-tour-step="correo"]', title: 'Recordatorios por correo', text: 'Se envían solos un día antes de cada cita. Aquí ves la cola, los últimos envíos y puedes mandar una prueba.' },
            { el: '.nav__badge', title: 'Contador', text: 'Este número suma lo que requiere acción: citas por cerrar, servicios sin registrar y cumpleaños de hoy.' },
            REPLAY
        ],
        'agenda': [
            { el: '.page-head .btn--primary', title: 'Agendar', text: 'Abre el formulario de cita. También puedes tocar directamente un espacio libre del calendario.' },
            { el: '[data-tour-step="filtro"]', title: 'Ver por estilista', text: 'Muestra la agenda de todo el equipo o solo la de una persona; las zonas claras son su horario de trabajo.' },
            { el: '.fc-header-toolbar', title: 'Moverte en el calendario', text: 'Cambia de semana con las flechas y elige ver por mes, semana, día o en lista.' },
            { el: '[data-tour-step="calendario"]', title: 'Las citas', text: 'Cada color es un estilista. Toca una cita para ver su detalle o arrástrala a otra hora para reprogramarla; si el horario no está libre, regresa a su lugar.' },
            { el: '.agenda-tools__cancel', title: 'Canceladas', text: 'Por defecto se ocultan para no estorbar. Márcalo para verlas.' },
            REPLAY
        ],
        'cita-form': [
            { el: '[data-tour-step="cliente"]', title: 'Cliente', text: 'Escribe parte del nombre o del teléfono para filtrar la lista y selecciónalo.' },
            { el: '.field:has(#servicio_id)', title: 'Servicio', text: 'La duración del servicio define cuánto tiempo ocupa la cita.' },
            { el: '.field:has(#empleado_id)', title: 'Estilista', text: 'Quién atenderá. Solo se puede agendar dentro de su horario.' },
            { el: '[data-tour-step="horarios"]', title: 'Horarios libres', text: 'Al elegir servicio, estilista y fecha aparecen los horarios en los que cabe el servicio completo. Toca uno para usarlo.' },
            { el: '.field:has(#precio)', title: 'Precio', text: 'Si lo dejas vacío se cobra el precio del servicio. Cámbialo si el cabello es muy largo o hay un descuento.' },
            { el: '.form__actions--bar .btn--primary', title: 'Agendar', text: 'Si el horario ya se ocupó o queda fuera del turno, te dice exactamente con quién choca.' },
            REPLAY
        ],
        'cita-detalle': [
            { el: '[data-tour-step="resumen"]', title: 'La cita', text: 'Fecha, horario, servicio, quién atiende y cuánto se cobra.' },
            { el: '[data-tour-step="estado"]', title: 'Estado', text: 'Cuando llegue la hora márcala como atendida o como no asistió. Cancelarla libera el horario.' },
            { el: '[data-tour-step="resultado"]', title: 'Resultado', text: 'Una vez atendida, registra aquí la fórmula y las fotos para el historial del cliente.' },
            { el: '[data-tour-step="recordatorio"]', title: 'Recordatorio por WhatsApp', text: 'Abre WhatsApp con el mensaje ya escrito. Puedes ajustarlo antes de enviarlo y queda registrado aquí.' },
            { el: '.client-mini', title: 'El cliente', text: 'Sus preferencias y notas a la mano antes de empezar.' },
            REPLAY
        ],
        'clientes': [
            { el: '.page-head .btn--primary', title: 'Registrar un cliente', text: 'Empieza aquí cuando llegue alguien nuevo. Solo necesitas su nombre y un teléfono o correo.' },
            { el: '.filters__search', title: 'Buscar', text: 'Escribe parte del nombre, el teléfono o el correo y presiona Filtrar.' },
            { el: '.field:has(#estado)', title: 'Activos e inactivos', text: 'Los clientes que ya no vienen se pasan a inactivos para no perder su historial. Aquí puedes verlos.' },
            { el: '.table-wrap', title: 'Tu cartera', text: 'Cada fila muestra cuándo vino por última vez y si ya tiene su próxima cita.' },
            { el: '.table .actions .btn', title: 'Ficha del cliente', text: 'Abre la ficha para ver sus preferencias, notas, foto de referencia y todas sus citas.' },
            REPLAY
        ],
        'cliente-form': [
            { el: '.field:has(#nombre)', title: 'Nombre completo', text: 'Como lo quieras encontrar después en el buscador.' },
            { el: '[data-tour-step="contacto"]', title: 'Cómo contactarlo', text: 'Registra al menos uno. El teléfono se usa para el recordatorio por WhatsApp y el correo para el automático.' },
            { el: '.field:has(#fecha_nacimiento)', title: 'Cumpleaños', text: 'Opcional. Con esta fecha el sistema te avisa cuando se acerque su cumpleaños.' },
            { el: '.field:has(#preferencias)', title: 'Preferencias', text: 'Cómo le gusta su corte o color. Cualquier compañero que lo atienda lo verá en su ficha.' },
            { el: '.field:has(#notas)', title: 'Notas internas', text: 'Datos que el cliente no ve, como alergias a productos o pruebas pendientes.' },
            { el: '.photo-card', title: 'Foto de referencia', text: 'El resultado que quiere conservar. Se guarda en privado: solo el personal con sesión puede verla.' },
            { el: '.form__actions .btn--primary', title: 'Guardar', text: 'Si falta algo, el formulario te marca en rojo qué corregir.' },
            REPLAY
        ],
        'cliente-ficha': [
            { el: '.profile__side .facts', title: 'Datos de contacto', text: 'Teléfono, correo, edad y desde cuándo es cliente.' },
            { el: '.stats--compact', title: 'Resumen', text: 'Cuántas veces ha venido, cuánto ha gastado en total y hace cuánto fue su última visita.' },
            { el: '.profile__main .grid-2', title: 'Preferencias y notas', text: 'Revísalas antes de empezar el servicio.' },
            { el: '[data-tour-step="formula"]', title: 'Última fórmula de color', text: 'Para que cualquier colorista parta de lo que ya funcionó, sin preguntarle al cliente qué le pusieron.' },
            { el: '[data-tour-step="historial"]', title: 'Historial', text: 'Cada servicio realizado con su fórmula, observaciones y fotos. Si hay foto de antes y de después, desliza sobre la imagen para compararlas.' },
            { el: '[data-tour-step="citas"]', title: 'Citas', text: 'Todas sus citas, de la más reciente a la más antigua, con quién lo atendió y cuánto pagó.' },
            { el: '.page-actions .btn--primary', title: 'Editar', text: 'Actualiza sus datos o cambia la foto de referencia.' },
            { el: '.profile__actions', title: 'Inactivar', text: 'Si deja de venir, pásalo a inactivos: desaparece de las listas pero su historial se conserva.' },
            REPLAY
        ],
        'historial-form': [
            { el: '[data-tour-step="resumen"]', title: 'A quién y qué', text: 'El cliente y el servicio que se registra. Si viene de una cita, ya está todo lleno.' },
            { el: '[data-tour-step="formula"]', title: 'Fórmula o productos', text: 'Tonos, proporciones, oxidante y tiempo. En color, tratamientos y alaciados es lo más valioso del historial.' },
            { el: '[data-tour-step="ultima"]', title: 'Parte de la anterior', text: 'Si ya tiene una fórmula de color, úsala como base y solo ajusta lo que cambió.' },
            { el: '.field:has(#observaciones)', title: 'Observaciones', text: 'Cómo llegó y cómo quedó el cabello, y qué le recomendaste.' },
            { el: '[data-tour-step="fotos"]', title: 'Antes y después', text: 'En el celular abre la cámara directamente. Con las dos fotos, la ficha muestra un comparador deslizable.' },
            { el: '.form__actions--bar .btn--primary', title: 'Guardar', text: 'Si ahora no hay tiempo, puedes hacerlo después desde la cita.' },
            REPLAY
        ],
        'cuenta': [
            { el: '[data-tour-step="datos"]', title: 'Tus datos', text: 'Tu nombre aparece en la barra superior y, si eres estilista, en la agenda. El correo es con el que inicias sesión.' },
            { el: '[data-tour-step="password"]', title: 'Contraseña', text: 'Cámbiala cuando quieras. Si alguien más la conoce, hazlo de inmediato.' },
            { el: '[data-tour-step="actual"]', title: 'Confirmación', text: 'Para cambiar el correo o la contraseña te pedimos la actual, así nadie puede cambiarla si dejas la sesión abierta.' },
            REPLAY
        ],
        'servicios': [
            { el: '.page-head .btn--primary', title: 'Agregar un servicio', text: 'Todo lo que se puede agendar: cortes, color, tratamientos, alaciados y split ender.' },
            { el: '.filters', title: 'Filtrar', text: 'Busca por nombre o muestra solo un tipo de servicio.' },
            { el: '.table-wrap', title: 'Precio y duración', text: 'La duración se usa para calcular cuánto ocupa cada cita en la agenda. Los precios activos se publican en la página del negocio.' },
            { el: '.table .actions', title: 'Desactivar o eliminar', text: 'Un servicio que ya tiene citas no se puede eliminar; desactívalo para que deje de aparecer al agendar y en la página.' },
            REPLAY
        ],
        'servicio-form': [
            { el: '.field:has(#nombre)', title: 'Nombre', text: 'Así aparecerá en la agenda y en la página pública.' },
            { el: '.form-row', title: 'Tipo, precio y duración', text: 'El tipo agrupa el servicio en la lista de precios. La duración define el espacio que ocupa en la agenda.' },
            { el: '.field:has(#descripcion)', title: 'Descripción', text: 'Qué incluye. Por ejemplo: incluye shampoo, corte y secado.' },
            { el: 'form .check', title: 'Disponible', text: 'Desmárcalo para ocultarlo sin borrarlo.' },
            REPLAY
        ],
        'empleados': [
            { el: '.page-head .btn--primary', title: 'Agregar personal', text: 'Registra a cada estilista con su horario y, si quieres, con su propia cuenta.' },
            { el: '.person', title: 'Tarjeta del estilista', text: 'Su especialidad, cuántos días trabaja, sus próximas citas y el correo con el que entra al sistema.' },
            { el: '.person__actions', title: 'Editar o desactivar', text: 'Al desactivar a alguien deja de aparecer en la agenda y ya no puede iniciar sesión.' },
            REPLAY
        ],
        'empleado-form': [
            { el: '.form-stack > .card:first-child', title: 'Datos', text: 'Nombre, contacto y especialidad.' },
            { el: '.swatches', title: 'Color en la agenda', text: 'Cada estilista tiene su color para distinguir sus citas de un vistazo.' },
            { el: '[data-tour-step="horario"]', title: 'Horario semanal', text: 'Marca los días que trabaja y su hora de entrada y salida. Solo se podrán agendar citas dentro de este horario.' },
            { el: '[data-tour-step="acceso"]', title: 'Acceso al sistema', text: 'Opcional. Con una cuenta, el estilista entra y ve solo su propia agenda.' },
            { el: '.form__actions--bar .btn--primary', title: 'Guardar', text: 'Si algo falta o el horario no cuadra, se marca en rojo qué corregir.' },
            REPLAY
        ]
    };

    var pasos = PASOS[clave];
    if (!pasos) return;

    var spot, pop, titleEl, textEl, countEl, prevBtn, nextBtn;
    var activos = [];
    var indice = 0;
    var abierto = false;
    var focoPrevio = null;

    function visible(el) {
        if (!el) return false;
        var r = el.getBoundingClientRect();
        if (r.width === 0 || r.height === 0) return false;
        if (r.right <= 0 || r.left >= window.innerWidth) return false; // menu lateral oculto en celular
        return getComputedStyle(el).visibility !== 'hidden';
    }

    function construir() {
        spot = document.createElement('div');
        spot.className = 'tour-spot';
        spot.setAttribute('aria-hidden', 'true');

        pop = document.createElement('div');
        pop.className = 'tour-pop';
        pop.setAttribute('role', 'dialog');
        pop.setAttribute('aria-modal', 'true');
        pop.setAttribute('aria-labelledby', 'tour-title');
        pop.setAttribute('aria-describedby', 'tour-text');
        pop.innerHTML =
            '<p class="tour-pop__count" data-count></p>' +
            '<h2 class="tour-pop__title" id="tour-title"></h2>' +
            '<p class="tour-pop__text" id="tour-text"></p>' +
            '<div class="tour-pop__actions">' +
            '  <button type="button" class="tour-pop__skip" data-skip>Saltar recorrido</button>' +
            '  <span class="tour-pop__nav">' +
            '    <button type="button" class="btn btn--sm btn--ghost" data-prev>Anterior</button>' +
            '    <button type="button" class="btn btn--sm btn--primary" data-next>Siguiente</button>' +
            '  </span>' +
            '</div>';

        document.body.appendChild(spot);
        document.body.appendChild(pop);

        titleEl = pop.querySelector('#tour-title');
        textEl = pop.querySelector('#tour-text');
        countEl = pop.querySelector('[data-count]');
        prevBtn = pop.querySelector('[data-prev]');
        nextBtn = pop.querySelector('[data-next]');

        pop.querySelector('[data-skip]').addEventListener('click', function () { cerrar(true); });
        prevBtn.addEventListener('click', function () { ir(indice - 1); });
        nextBtn.addEventListener('click', function () {
            if (indice >= activos.length - 1) cerrar(true);
            else ir(indice + 1);
        });
    }

    function abrir() {
        activos = pasos
            .map(function (p) { return { paso: p, el: document.querySelector(p.el) }; })
            .filter(function (x) { return visible(x.el); });
        if (!activos.length) return;

        if (!pop) construir();
        focoPrevio = document.activeElement;
        abierto = true;
        body.classList.add('tour-open');
        spot.hidden = false;
        pop.hidden = false;
        ir(0);
    }

    function ir(i) {
        indice = Math.max(0, Math.min(i, activos.length - 1));
        var actual = activos[indice];

        titleEl.textContent = actual.paso.title;
        textEl.textContent = actual.paso.text;
        countEl.textContent = 'Paso ' + (indice + 1) + ' de ' + activos.length;
        prevBtn.hidden = indice === 0;
        nextBtn.textContent = indice === activos.length - 1 ? 'Terminar' : 'Siguiente';

        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        actual.el.scrollIntoView({ block: 'center', behavior: reduce ? 'auto' : 'smooth' });

        // Espera a que termine el desplazamiento antes de medir
        setTimeout(posicionar, reduce ? 0 : 320);
        nextBtn.focus({ preventScroll: true });
    }

    function posicionar() {
        if (!abierto) return;
        var el = activos[indice].el;
        var r = el.getBoundingClientRect();
        var pad = 8;

        spot.style.top = (r.top - pad) + 'px';
        spot.style.left = (r.left - pad) + 'px';
        spot.style.width = (r.width + pad * 2) + 'px';
        spot.style.height = (r.height + pad * 2) + 'px';

        // En celular la tarjeta va fija abajo (ver CSS)
        if (window.innerWidth <= 600) {
            pop.style.top = pop.style.left = '';
            return;
        }

        var pw = pop.offsetWidth;
        var ph = pop.offsetHeight;
        var margen = 16;
        var top;

        if (r.bottom + pad + margen + ph <= window.innerHeight) {
            top = r.bottom + pad + margen;              // debajo
        } else if (r.top - pad - margen - ph >= 0) {
            top = r.top - pad - margen - ph;            // arriba
        } else {
            top = window.innerHeight - ph - margen;     // elemento muy alto: abajo de la pantalla
        }

        var left = r.left + r.width / 2 - pw / 2;
        left = Math.max(margen, Math.min(left, window.innerWidth - pw - margen));

        pop.style.top = top + 'px';
        pop.style.left = left + 'px';
    }

    function cerrar(guardar) {
        if (!abierto) return;
        abierto = false;
        body.classList.remove('tour-open');
        spot.hidden = true;
        pop.hidden = true;
        if (focoPrevio && focoPrevio.focus) focoPrevio.focus({ preventScroll: true });

        if (guardar && body.dataset.tourPending === '1') {
            body.dataset.tourPending = '0';
            var datos = new URLSearchParams();
            datos.append('_csrf', body.dataset.csrf);
            fetch(body.dataset.tourUrl, {
                method: 'POST',
                body: datos,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'fetch' }
            }).catch(function () { /* si falla, se volvera a mostrar la proxima vez */ });
        }
    }

    /* Teclado: Escape salta, flechas navegan, Tab se queda dentro de la tarjeta */
    document.addEventListener('keydown', function (ev) {
        if (!abierto) return;
        if (ev.key === 'Escape') { ev.preventDefault(); cerrar(true); }
        else if (ev.key === 'ArrowRight') { nextBtn.click(); }
        else if (ev.key === 'ArrowLeft' && indice > 0) { ir(indice - 1); }
        else if (ev.key === 'Tab') {
            var focos = Array.prototype.filter.call(pop.querySelectorAll('button'), function (b) { return !b.hidden; });
            var primero = focos[0], ultimo = focos[focos.length - 1];
            if (ev.shiftKey && document.activeElement === primero) { ev.preventDefault(); ultimo.focus(); }
            else if (!ev.shiftKey && document.activeElement === ultimo) { ev.preventDefault(); primero.focus(); }
        }
    });

    window.addEventListener('resize', posicionar);
    window.addEventListener('scroll', posicionar, { passive: true });

    var replay = document.querySelector('[data-tour-replay]');
    if (replay) replay.addEventListener('click', abrir);

    if (body.dataset.tourPending === '1') {
        // Deja que la pagina termine de pintarse
        setTimeout(abrir, 450);
    }
})();
