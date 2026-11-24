/*
 * Formulario de historial:
 *  - destaca el campo de formula cuando el servicio es de color,
 *    tratamiento o alaciado,
 *  - copia la ultima formula registrada como punto de partida.
 */
(function () {
    'use strict';

    var form = document.querySelector('[data-historial-form]');
    if (!form) return;

    var formula = form.querySelector('[data-formula]');
    var textarea = form.querySelector('#formula_tinte');
    var servicio = form.querySelector('[data-servicio-tipo]');

    if (servicio && formula) {
        var tipos = formula.dataset.tipos.split(',');
        servicio.addEventListener('change', function () {
            var o = servicio.selectedOptions[0];
            var tipo = o ? o.dataset.tipo : '';
            formula.classList.toggle('is-featured', !tipo || tipos.indexOf(tipo) !== -1);
        });
    }

    var copiar = form.querySelector('[data-copiar-formula]');
    var ultima = form.querySelector('[data-ultima-formula]');
    if (copiar && ultima && textarea) {
        copiar.addEventListener('click', function () {
            if (textarea.value.trim() !== '' &&
                textarea.value.trim() !== ultima.textContent.trim()) {
                textarea.value = textarea.value.trim() + '\n' + ultima.textContent.trim();
            } else {
                textarea.value = ultima.textContent.trim();
            }
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        });
    }
})();
