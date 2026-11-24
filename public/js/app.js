/*
 * Comportamiento comun del sistema: menu lateral en movil, cierre de
 * avisos, dialogo de confirmacion, campos que se muestran con una
 * casilla y filas de horario.
 */
(function () {
    'use strict';

    /* Menu lateral en pantallas chicas */
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.querySelector('.sidebar-backdrop');
    var menuBtn = document.querySelector('.topbar__menu');

    function setSidebar(open) {
        if (!sidebar) return;
        sidebar.classList.toggle('is-open', open);
        if (backdrop) backdrop.hidden = !open;
        if (menuBtn) menuBtn.setAttribute('aria-expanded', String(open));
    }

    document.querySelectorAll('[data-toggle-sidebar]').forEach(function (el) {
        el.addEventListener('click', function () {
            setSidebar(!sidebar.classList.contains('is-open'));
        });
    });

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') setSidebar(false);
    });

    /* Cerrar avisos */
    document.querySelectorAll('[data-dismiss]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.alert').remove();
        });
    });

    /* Confirmacion antes de enviar formularios con data-confirm */
    var dialog = document.getElementById('confirm-dialog');

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            if (form.dataset.confirmed === '1' || !dialog || typeof dialog.showModal !== 'function') {
                return;
            }
            ev.preventDefault();

            dialog.querySelector('[data-confirm-text]').textContent = form.dataset.confirm;
            dialog.returnValue = '';
            dialog.showModal();
            dialog.querySelector('[data-confirm-ok]').focus();

            dialog.addEventListener('close', function onClose() {
                dialog.removeEventListener('close', onClose);
                if (dialog.returnValue === 'ok') {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        });
    });

    /* Casillas que muestran u ocultan un bloque: data-reveal="#selector" */
    document.querySelectorAll('[data-reveal]').forEach(function (check) {
        var target = document.querySelector(check.dataset.reveal);
        if (!target) return;
        check.addEventListener('change', function () {
            target.hidden = !check.checked;
        });
    });

    /* Horario: deshabilitar horas de los dias que no trabaja */
    document.querySelectorAll('[data-schedule-row]').forEach(function (row) {
        var toggle = row.querySelector('[data-schedule-toggle]');
        var times = row.querySelectorAll('input[type="time"]');

        function sync() {
            times.forEach(function (t) { t.disabled = !toggle.checked; });
            row.classList.toggle('is-off', !toggle.checked);
        }
        toggle.addEventListener('change', sync);
        sync();
    });
})();
