<header class="site-nav" data-nav>
    <div class="site-nav__inner">
        <a class="site-nav__brand" href="#inicio" aria-label="<?= e(App\Core\Config::get('app.name')) ?>, inicio">
            <span class="site-nav__hair">Hair</span><span class="site-nav__plus">+</span>
        </a>

        <button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="site-menu" data-nav-toggle>
            <span class="sr-only">Abrir menú</span>
            <span class="site-nav__bars" aria-hidden="true"></span>
        </button>

        <nav class="site-nav__menu" id="site-menu" aria-label="Secciones">
            <a href="#servicios" data-nav-link>Servicios</a>
            <a href="#trabajos" data-nav-link>Trabajos</a>
            <a href="#acerca" data-nav-link>Acerca de mí</a>
            <a href="#visitanos" data-nav-link>Visítanos</a>
            <?php if ($logueado): ?>
            <a class="btn btn--ghost btn--sm site-nav__staff" href="<?= url('/dashboard') ?>">Ir al panel</a>
            <?php else: ?>
            <a class="btn btn--ghost btn--sm site-nav__staff" href="<?= url('/login') ?>">Acceso del personal</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main id="contenido">

    <section class="hero" id="inicio" aria-labelledby="hero-title">
        <div class="hero__scene" aria-hidden="true">
            <div class="hero__stars"></div>
            <div class="hero__sun"></div>
            <div class="hero__sea"></div>
            <div class="hero__grid"></div>
            <svg class="hero__palms" viewBox="0 0 1200 400" preserveAspectRatio="xMidYMax slice">
                <path d="M120 400 C128 300 136 230 160 160 M160 160 C120 140 70 150 30 185 M160 160 C130 120 90 105 50 110 M160 160 C170 120 200 95 245 90 M160 160 C200 140 250 145 290 175 M160 160 C150 125 155 95 175 70"/>
                <path d="M250 400 C252 330 258 285 275 240 M275 240 C245 225 210 232 185 255 M275 240 C255 210 225 200 200 205 M275 240 C285 210 310 195 340 195 M275 240 C305 228 340 235 365 255"/>
                <path d="M1060 400 C1050 290 1040 220 1010 150 M1010 150 C1050 130 1100 140 1145 175 M1010 150 C1040 110 1080 95 1120 100 M1010 150 C1000 110 970 85 925 80 M1010 150 C970 130 920 135 880 165 M1010 150 C1020 115 1015 85 995 60"/>
            </svg>
        </div>

        <div class="hero__sky">
            <h1 class="hero__sign" id="hero-title">
                <span class="hero__hair">Hair</span><span class="hero__plus" aria-label="más">+</span>
            </h1>
        </div>

        <div class="hero__floor">
            <p class="hero__lead"><?= e($sitio['eslogan'] ?? '') ?></p>
            <div class="hero__actions">
                <a class="btn btn--primary btn--lg" href="<?= e($whatsapp) ?>" target="_blank" rel="noopener">Agenda por WhatsApp</a>
                <a class="btn btn--ghost btn--lg btn--glass" href="#servicios">Ver servicios y precios</a>
            </div>
            <p class="hero__strip">
                <?php foreach ($catalogo as $tipo => $items): ?>
                <a href="#cat-<?= e($tipo) ?>"><?= e($categorias[$tipo]) ?></a>
                <?php endforeach; ?>
            </p>
        </div>
    </section>

    <section class="section menu" id="servicios" aria-labelledby="servicios-title">
        <div class="section__inner">
            <header class="section__head">
                <h2 class="section-title" id="servicios-title">Servicios</h2>
                <p class="section__lead">Para dama y caballero. Los precios son base y pueden variar según el largo y la cantidad de cabello; te confirmamos el total antes de empezar.</p>
            </header>

            <?php if (!$catalogo): ?>
            <p class="menu__empty">Pronto publicaremos nuestra lista de servicios. Escríbenos por WhatsApp para cotizar.</p>
            <?php else: ?>
            <div class="menu__board">
                <?php foreach ($catalogo as $tipo => $items): ?>
                <article class="menu-cat menu-cat--<?= e($tipo) ?>" id="cat-<?= e($tipo) ?>">
                    <h3 class="menu-cat__title"><?= e($categorias[$tipo]) ?></h3>
                    <?php if (!empty($sitio['categorias'][$tipo])): ?>
                    <p class="menu-cat__note"><?= e($sitio['categorias'][$tipo]) ?></p>
                    <?php endif; ?>
                    <ul class="menu-cat__list">
                        <?php foreach ($items as $item): ?>
                        <li class="menu-item">
                            <span class="menu-item__name"><?= e($item['nombre']) ?></span>
                            <span class="menu-item__dots" aria-hidden="true"></span>
                            <span class="menu-item__price"><?= e(precio($item['precio'])) ?></span>
                            <?php if ($item['descripcion']): ?>
                            <span class="menu-item__desc"><?= e($item['descripcion']) ?> <span class="menu-item__time"><?= e(duracion($item['duracion_min'])) ?> aprox.</span></span>
                            <?php else: ?>
                            <span class="menu-item__desc"><span class="menu-item__time"><?= e(duracion($item['duracion_min'])) ?> aprox.</span></span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($galeria): ?>
    <section class="section gallery" id="trabajos" aria-labelledby="trabajos-title">
        <div class="section__inner">
            <header class="section__head">
                <h2 class="section-title" id="trabajos-title">Trabajos</h2>
                <p class="section__lead">Referencias de lo que hacemos. Trae la tuya y la adaptamos a tu tipo de cabello.</p>
            </header>

            <ul class="gallery__grid">
                <?php foreach ($galeria as $i => $foto): ?>
                <li class="gallery__item">
                    <button class="gallery__btn" type="button" data-lightbox-open="<?= e($i) ?>"
                            aria-label="Ampliar: <?= e($foto['titulo']) ?>">
                        <img src="<?= asset('img/galeria/' . $foto['archivo']) ?>" alt="<?= e($foto['titulo']) ?>: <?= e($foto['texto']) ?>"
                             loading="lazy" width="800" height="1000">
                        <span class="gallery__caption">
                            <strong><?= e($foto['titulo']) ?></strong>
                            <span><?= e($foto['texto']) ?></span>
                        </span>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <dialog class="lightbox" aria-label="Galería ampliada" data-lightbox>
            <figure class="lightbox__figure">
                <img class="lightbox__img" src="" alt="" data-lightbox-img>
                <figcaption class="lightbox__caption" data-lightbox-caption></figcaption>
            </figure>
            <button class="lightbox__nav lightbox__nav--prev" type="button" data-lightbox-prev aria-label="Imagen anterior">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
            </button>
            <button class="lightbox__nav lightbox__nav--next" type="button" data-lightbox-next aria-label="Imagen siguiente">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
            </button>
            <button class="lightbox__close" type="button" data-lightbox-close aria-label="Cerrar galería">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </dialog>
    </section>
    <?php endif; ?>

    <section class="section about" id="acerca" aria-labelledby="acerca-title">
        <div class="section__inner about__inner">
            <figure class="about__polaroid">
                <?php if ($fotoAcerca): ?>
                <img class="about__photo" src="<?= e($fotoAcerca) ?>" alt="<?= e($acerca['nombre'] ?? '') ?>">
                <?php else: ?>
                <div class="about__photo about__photo--mono" aria-hidden="true"><span><?= e(iniciales($acerca['nombre'] ?? 'H')) ?></span></div>
                <?php endif; ?>
                <figcaption class="about__caption"><?= e($acerca['nombre'] ?? '') ?></figcaption>
            </figure>

            <div class="about__text">
                <h2 class="section-title" id="acerca-title">Acerca de mí</h2>
                <p class="about__role"><?= e($acerca['puesto'] ?? '') ?></p>
                <?php foreach ($acerca['parrafos'] ?? [] as $parrafo): ?>
                <p class="about__p"><?= e($parrafo) ?></p>
                <?php endforeach; ?>

                <?php if (!empty($acerca['puntos'])): ?>
                <ul class="about__points">
                    <?php foreach ($acerca['puntos'] as $punto): ?>
                    <li><?= e($punto) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <p class="about__sign"><?= e($firma) ?></p>
            </div>
        </div>
    </section>

    <section class="section visit" id="visitanos" aria-labelledby="visitanos-title">
        <div class="visit__floor" aria-hidden="true"></div>
        <div class="section__inner">
            <header class="section__head section__head--center">
                <h2 class="section-title" id="visitanos-title">Visítanos</h2>
            </header>

            <div class="visit__grid">
                <article class="visit-card">
                    <h3 class="visit-card__title">Horario</h3>
                    <dl class="hours">
                        <?php foreach ($sitio['horario'] ?? [] as $h): ?>
                        <div class="hours__row">
                            <dt><?= e($h['dias']) ?></dt>
                            <dd><?= e($h['horas']) ?></dd>
                        </div>
                        <?php endforeach; ?>
                    </dl>
                </article>

                <article class="visit-card">
                    <h3 class="visit-card__title">Dónde estamos</h3>
                    <p class="visit-card__big"><?= e($sitio['direccion'] ?? '') ?></p>
                    <?php if (!empty($sitio['referencia'])): ?>
                    <p class="visit-card__muted"><?= e($sitio['referencia']) ?></p>
                    <?php endif; ?>
                    <a class="btn btn--ghost" href="<?= e($mapa) ?>" target="_blank" rel="noopener">Cómo llegar</a>
                </article>

                <article class="visit-card visit-card--cta">
                    <h3 class="visit-card__title">Agenda tu cita</h3>
                    <p class="visit-card__muted">Escríbenos qué te quieres hacer y te damos el horario más cercano.</p>
                    <a class="btn btn--primary" href="<?= e($whatsapp) ?>" target="_blank" rel="noopener">Escribir por WhatsApp</a>
                    <?php if (!empty($sitio['telefono'])): ?>
                    <a class="visit-card__tel" href="tel:<?= e(preg_replace('/\D/', '', $sitio['telefono'])) ?>">Llamar al <?= e(telefono($sitio['telefono'])) ?></a>
                    <?php endif; ?>
                    <?php if (!empty($sitio['instagram']) || !empty($sitio['facebook'])): ?>
                    <p class="visit-card__social">
                        <?php if (!empty($sitio['instagram'])): ?><a href="<?= e($sitio['instagram']) ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
                        <?php if (!empty($sitio['facebook'])): ?><a href="<?= e($sitio['facebook']) ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
                    </p>
                    <?php endif; ?>
                </article>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="site-footer__inner">
        <p class="site-footer__brand"><span class="site-nav__hair">Hair</span><span class="site-nav__plus">+</span></p>
        <p class="site-footer__copy"><?= e($anio) ?> <?= e(App\Core\Config::get('app.name')) ?>. Estética para dama y caballero.</p>
        <a class="site-footer__staff" href="<?= url($logueado ? '/dashboard' : '/login') ?>"><?= $logueado ? 'Ir al panel' : 'Acceso del personal' ?></a>
    </div>
</footer>

<a class="wa-bar" href="<?= e($whatsapp) ?>" target="_blank" rel="noopener">Agenda por WhatsApp</a>
