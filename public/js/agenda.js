/*
 * Calendario de citas con FullCalendar.
 *  - Vista segun el ancho: semana en escritorio, dia en tablet y lista en celular.
 *  - Tocar un espacio libre abre el formulario con la fecha y hora elegidas.
 *  - Arrastrar una cita programada la reprograma (el servidor valida horario y empalmes).
 */
(function () {
    'use strict';

    var el = document.getElementById('calendario');
    if (!el || !window.FullCalendar) return;

    var cfg = JSON.parse(el.dataset.config);
    var verCanceladas = document.querySelector('[data-ver-canceladas]');
    var toastEl = document.querySelector('[data-toast]');
    var toastTimer = null;

    function toast(texto, tipo) {
        if (!toastEl) return;
        toastEl.textContent = texto;
        toastEl.className = 'toast toast--' + (tipo || 'ok');
        toastEl.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.hidden = true; }, 4500);
    }

    function vistaPorAncho() {
        var w = window.innerWidth;
        if (w < 600) return 'listWeek';
        if (w < 1024) return 'timeGridDay';
        return 'timeGridWeek';
    }

    function barra() {
        if (window.innerWidth < 600) {
            return {
                header: { left: 'prev,next', center: 'title', right: 'today' },
                footer: { center: 'timeGridDay,listWeek,dayGridMonth' }
            };
        }
        return {
            header: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
            footer: false
        };
    }

    function dosDigitos(n) { return (n < 10 ? '0' : '') + n; }
    function isoLocal(d) {
        return d.getFullYear() + '-' + dosDigitos(d.getMonth() + 1) + '-' + dosDigitos(d.getDate()) +
               'T' + dosDigitos(d.getHours()) + ':' + dosDigitos(d.getMinutes());
    }

    var vistaGuardada = null;
    try { vistaGuardada = localStorage.getItem('agenda-vista'); } catch (e) { /* sin almacenamiento */ }

    var b = barra();
    var calendar = new FullCalendar.Calendar(el, {
        locale: 'es',
        initialView: vistaGuardada && window.innerWidth >= 600 ? vistaGuardada : vistaPorAncho(),
        initialDate: cfg.fecha,
        headerToolbar: b.header,
        footerToolbar: b.footer,
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista' },
        firstDay: 1,
        allDaySlot: false,
        nowIndicator: true,
        slotMinTime: cfg.desde,
        slotMaxTime: cfg.hasta,
        slotDuration: '00:15:00',
        slotLabelInterval: '01:00',
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        businessHours: cfg.horario.length ? cfg.horario : false,
        expandRows: true,
        height: 'auto',
        dayMaxEvents: 3,
        eventDurationEditable: false,
        eventStartEditable: true,
        noEventsContent: 'No hay citas en estos días.',

        events: function (info, ok, fallo) {
            var q = new URLSearchParams({ start: info.startStr, end: info.endStr });
            if (cfg.empleado) q.append('empleado', cfg.empleado);
            if (verCanceladas && verCanceladas.checked) q.append('canceladas', '1');

            fetch(cfg.eventos + '?' + q.toString(), { credentials: 'same-origin' })
                .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
                .then(ok)
                .catch(function (e) { fallo(e); toast('No se pudieron cargar las citas. Recarga la página.', 'error'); });
        },

        eventContent: function (arg) {
            var p = arg.event.extendedProps;
            var wrap = document.createElement('div');
            wrap.className = 'ev__body';

            var t = document.createElement('span');
            t.className = 'ev__time';
            t.textContent = arg.timeText;

            var c = document.createElement('strong');
            c.className = 'ev__client';
            c.textContent = arg.event.title;

            var s = document.createElement('span');
            s.className = 'ev__service';
            s.textContent = p.servicio + (cfg.empleado ? '' : ', ' + p.empleado);

            if (arg.view.type.indexOf('list') === 0) {
                var st = document.createElement('span');
                st.className = 'badge badge--' + p.estado;
                st.textContent = p.estadoTexto;
                wrap.appendChild(c); wrap.appendChild(s); wrap.appendChild(st);
            } else {
                wrap.appendChild(t); wrap.appendChild(c); wrap.appendChild(s);
            }
            return { domNodes: [wrap] };
        },

        eventDidMount: function (arg) {
            var p = arg.event.extendedProps;
            arg.el.setAttribute('aria-label',
                arg.event.title + ', ' + p.servicio + ' con ' + p.empleado + ', ' + p.estadoTexto);
            arg.el.title = arg.event.title + ' · ' + p.servicio + ' (' + p.estadoTexto + ')';
        },

        eventClick: function (info) {
            info.jsEvent.preventDefault();
            window.location.href = cfg.detalle + info.event.id;
        },

        dateClick: function (info) {
            var fecha = info.date;
            // En el mes se elige el dia; la hora se escoge en el formulario
            var soloDia = info.view.type === 'dayGridMonth';
            var limite = new Date();
            if (soloDia) limite.setHours(0, 0, 0, 0);

            if (fecha < limite) {
                toast('Elige un horario que no haya pasado.', 'error');
                return;
            }
            var q = new URLSearchParams({ inicio: soloDia ? isoLocal(fecha).slice(0, 10) : isoLocal(fecha) });
            if (cfg.empleado) q.append('empleado', cfg.empleado);
            window.location.href = cfg.nueva + '?' + q.toString();
        },

        eventDrop: function (info) {
            var datos = new URLSearchParams();
            datos.append('_csrf', cfg.csrf);
            datos.append('inicio', isoLocal(info.event.start));

            fetch(cfg.mover + info.event.id + '/mover', { method: 'POST', body: datos, credentials: 'same-origin' })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                .then(function (res) {
                    if (res.ok && res.j.ok) {
                        toast(res.j.mensaje, 'ok');
                    } else {
                        info.revert();
                        toast(res.j.error || 'No se pudo mover la cita.', 'error');
                    }
                })
                .catch(function () {
                    info.revert();
                    toast('No se pudo mover la cita. Revisa tu conexión.', 'error');
                });
        },

        viewDidMount: function (arg) {
            try { localStorage.setItem('agenda-vista', arg.view.type); } catch (e) { /* sin almacenamiento */ }
        },

        windowResize: function () {
            var nb = barra();
            calendar.setOption('headerToolbar', nb.header);
            calendar.setOption('footerToolbar', nb.footer);
            if (window.innerWidth < 600 && calendar.view.type === 'timeGridWeek') {
                calendar.changeView('listWeek');
            }
        }
    });

    calendar.render();

    if (verCanceladas) {
        verCanceladas.addEventListener('change', function () { calendar.refetchEvents(); });
    }
})();
