/*
 * Campos de foto: vista previa antes de subir y aviso si excede el
 * tamano permitido. Funciona con varios campos en la misma pagina;
 * cada uno vive dentro de un contenedor [data-photo].
 */
(function () {
    'use strict';

    var MAX_BYTES = 4 * 1024 * 1024;

    document.querySelectorAll('[data-photo]').forEach(function (box) {
        var input = box.querySelector('[data-photo-input]');
        var preview = box.querySelector('[data-photo-preview]');
        var empty = box.querySelector('[data-photo-empty]');
        var quitar = box.querySelector('input[name^="quitar_"]');
        if (!input || !preview) return;

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

        if (quitar) {
            quitar.addEventListener('change', function () {
                preview.hidden = quitar.checked;
                if (empty) empty.hidden = !quitar.checked;
            });
        }
    });
})();
