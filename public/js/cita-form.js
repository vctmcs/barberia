/*
 * Formulario de citas:
 *  - filtra la lista de clientes mientras se escribe,
 *  - muestra el precio del servicio como referencia,
 *  - consulta y muestra los horarios libres del estilista,
 *  - calcula la hora aproximada de termino.
 */
(function () {
    'use strict';

    var form = document.querySelector('[data-cita-form]');
    if (!form) return;

    var servicio = form.querySelector('[data-servicio]');
    var empleado = form.querySelector('[data-empleado]');
    var fecha = form.querySelector('[data-fecha]');
    var hora = form.querySelector('[data-hora]');
    var precio = form.querySelector('[data-precio-input]');
    var termina = form.querySelector('[data-termina]');
    var slots = form.querySelector('[data-slots]');
    var slotsMsg = form.querySelector('[data-slots-msg]');
    var url = form.dataset.disponibilidad;
    var excepto = form.dataset.excepto;

    /* Filtro de clientes */
    form.querySelectorAll('[data-filtrar]').forEach(function (input) {
        var select = form.querySelector(input.dataset.filtrar);
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase()
                .normalize('NFD').replace(/[̀-ͯ]/g, '');
            var primero = null;
            Array.prototype.forEach.call(select.options, function (o) {
                var texto = o.textContent.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
                var ver = q === '' || texto.indexOf(q) !== -1;
                o.hidden = !ver;
                if (ver && !primero) primero = o;
            });
            if (primero && select.selectedOptions.length && select.selectedOptions[0].hidden) {
                primero.selected = true;
            }
        });
    });

    function servicioActual() {
        var o = servicio.selectedOptions[0];
        return o && o.value ? { id: o.value, precio: o.dataset.precio, duracion: parseInt(o.dataset.duracion, 10) } : null;
    }

    function actualizarPrecio() {
        var s = servicioActual();
        precio.placeholder = s ? s.precio : '';
    }

    function actualizarTermino() {
        var s = servicioActual();
        if (!s || !hora.value) { termina.textContent = 'Sin calcular'; return; }
        var p = hora.value.split(':');
        var total = parseInt(p[0], 10) * 60 + parseInt(p[1], 10) + s.duracion;
        var h = Math.floor(total / 60), m = total % 60;
        termina.textContent = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
    }

    function marcarSlot() {
        slots.querySelectorAll('.slot').forEach(function (b) {
            var on = b.dataset.hora === hora.value;
            b.classList.toggle('is-on', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    var peticion = 0;
    function cargarSlots() {
        var s = servicioActual();
        slots.innerHTML = '';

        if (!s || !empleado.value || !fecha.value) {
            slotsMsg.textContent = 'Elige servicio' + (empleado.type === 'hidden' ? '' : ', estilista') + ' y fecha para ver los horarios libres.';
            return;
        }

        slotsMsg.textContent = 'Buscando horarios...';
        var q = new URLSearchParams({ empleado: empleado.value, fecha: fecha.value, servicio: s.id });
        if (excepto) q.append('excepto', excepto);
        var mia = ++peticion;

        fetch(url + '?' + q.toString(), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (mia !== peticion) return; // llego una respuesta vieja
                if (!d.ok) { slotsMsg.textContent = 'No se pudieron consultar los horarios.'; return; }
                if (!d.trabaja) { slotsMsg.textContent = 'Ese día no trabaja. Elige otra fecha.'; return; }
                if (!d.libres.length) { slotsMsg.textContent = 'Ya no hay espacio para este servicio ese día.'; return; }

                slotsMsg.textContent = d.libres.length + ' horarios disponibles entre ' +
                    d.horario.hora_inicio + ' y ' + d.horario.hora_fin + '.';
                d.libres.forEach(function (h) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'slot';
                    b.textContent = h;
                    b.dataset.hora = h;
                    b.addEventListener('click', function () {
                        hora.value = h;
                        marcarSlot();
                        actualizarTermino();
                    });
                    slots.appendChild(b);
                });
                marcarSlot();
            })
            .catch(function () {
                if (mia === peticion) slotsMsg.textContent = 'No se pudieron consultar los horarios.';
            });
    }

    servicio.addEventListener('change', function () { actualizarPrecio(); actualizarTermino(); cargarSlots(); });
    if (empleado.tagName === 'SELECT') empleado.addEventListener('change', cargarSlots);
    fecha.addEventListener('change', cargarSlots);
    hora.addEventListener('input', function () { marcarSlot(); actualizarTermino(); });

    actualizarPrecio();
    actualizarTermino();
    cargarSlots();
})();
