/*
 * Pagina publica: menu movil, fondo de la barra al desplazar, seccion
 * activa en el menu, visor de la galeria y barra de WhatsApp.
 */
(function () {
    'use strict';

    var nav = document.querySelector('[data-nav]');
    var toggle = document.querySelector('[data-nav-toggle]');

    /* Menu en celular */
    function setMenu(open) {
        nav.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
    }
    if (toggle) {
        toggle.addEventListener('click', function () {
            setMenu(!nav.classList.contains('is-open'));
        });
    }
    document.querySelectorAll('[data-nav-link]').forEach(function (a) {
        a.addEventListener('click', function () { setMenu(false); });
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && nav.classList.contains('is-open')) {
            setMenu(false);
            toggle.focus();
        }
    });

    /* Fondo de la barra y barra de WhatsApp segun el desplazamiento */
    var hero = document.getElementById('inicio');
    var waBar = document.querySelector('.wa-bar');
    var footer = document.querySelector('.site-footer');

    function onScroll() {
        var y = window.scrollY;
        nav.classList.toggle('is-scrolled', y > 40);

        if (waBar && hero && footer) {
            var enInicio = y < hero.offsetHeight * 0.6;
            var enPie = footer.getBoundingClientRect().top < window.innerHeight;
            waBar.classList.toggle('is-hidden', enInicio || enPie);
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* Marcar en el menu la seccion visible */
    var links = {};
    document.querySelectorAll('[data-nav-link]').forEach(function (a) {
        links[a.getAttribute('href').slice(1)] = a;
    });
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                var link = links[entry.target.id];
                if (link) link.classList.toggle('is-current', entry.isIntersecting);
            });
        }, { rootMargin: '-45% 0px -50% 0px' });
        Object.keys(links).forEach(function (id) {
            var sec = document.getElementById(id);
            if (sec) io.observe(sec);
        });
    }

    /* Visor de la galeria */
    var box = document.querySelector('[data-lightbox]');
    if (!box || typeof box.showModal !== 'function') return;

    var items = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox-open]'));
    var img = box.querySelector('[data-lightbox-img]');
    var caption = box.querySelector('[data-lightbox-caption]');
    var actual = 0;
    var origen = null;

    function mostrar(i) {
        actual = (i + items.length) % items.length;
        var thumb = items[actual].querySelector('img');
        var titulo = items[actual].querySelector('strong').textContent;
        var texto = items[actual].querySelector('.gallery__caption span').textContent;
        img.src = thumb.currentSrc || thumb.src;
        img.alt = thumb.alt;
        caption.textContent = titulo + '. ' + texto + ' (' + (actual + 1) + ' de ' + items.length + ')';
    }

    items.forEach(function (btn, i) {
        btn.addEventListener('click', function () {
            origen = btn;
            mostrar(i);
            box.showModal();
        });
    });

    box.querySelector('[data-lightbox-prev]').addEventListener('click', function () { mostrar(actual - 1); });
    box.querySelector('[data-lightbox-next]').addEventListener('click', function () { mostrar(actual + 1); });
    box.querySelector('[data-lightbox-close]').addEventListener('click', function () { box.close(); });

    box.addEventListener('keydown', function (ev) {
        if (ev.key === 'ArrowLeft') mostrar(actual - 1);
        if (ev.key === 'ArrowRight') mostrar(actual + 1);
    });

    /* Clic fuera de la imagen cierra */
    box.addEventListener('click', function (ev) {
        if (ev.target === box) box.close();
    });

    /* Deslizar con el dedo */
    var x0 = null;
    box.addEventListener('touchstart', function (ev) { x0 = ev.touches[0].clientX; }, { passive: true });
    box.addEventListener('touchend', function (ev) {
        if (x0 === null) return;
        var dx = ev.changedTouches[0].clientX - x0;
        if (Math.abs(dx) > 50) mostrar(actual + (dx < 0 ? 1 : -1));
        x0 = null;
    });

    box.addEventListener('close', function () {
        if (origen) origen.focus();
    });
})();
