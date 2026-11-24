/*
 * Comparador de antes y despues: la foto de despues se recorta segun
 * la posicion del deslizador. Es un input range real, asi que funciona
 * con teclado (flechas) y lectores de pantalla.
 */
(function () {
    'use strict';

    document.querySelectorAll('[data-compare]').forEach(function (box) {
        var range = box.querySelector('[data-compare-range]');
        if (!range) return;

        function pintar() {
            box.style.setProperty('--pos', range.value + '%');
        }
        range.addEventListener('input', pintar);
        pintar();
    });
})();
