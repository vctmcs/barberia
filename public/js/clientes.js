/*
 * Formulario de clientes: vista previa de la foto antes de subirla y
 * aviso si excede el tamano permitido.
 */
(function () {
    'use strict';

    var MAX_BYTES = 4 * 1024 * 1024;
    var input = document.querySelector('[data-photo-input]');
    if (!input) return;

    var preview = document.querySelector('[data-photo-preview]');
    var empty = document.querySelector('[data-photo-empty]');
    var quitar = document.querySelector('input[name="quitar_foto"]');

    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) return;

        if (file.size > MAX_BYTES) {
            input.value = '';
            input.setCustomValidity('La imagen pesa más de 4 MB.');
            input.reportValidity();
            input.setCustomValidity('');
            return;
        }

        var reader = new FileReader();
        reader.onload = function (ev) {
            preview.src = ev.target.result;
            preview.hidden = false;
            if (empty) empty.hidden = true;
            if (quitar) quitar.checked = false;
        };
        reader.readAsDataURL(file);
    });
})();
