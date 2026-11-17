(() => {
    'use strict';
    const hero = document.querySelector('[data-market-carousel]');
    if (!hero) return;
    const slides = [...hero.querySelectorAll('[data-hero-slide]')];
    const dots = [...hero.querySelectorAll('[data-hero-go]')];
    const pause = hero.querySelector('[data-hero-pause]');
    const region = hero.querySelector('.market-hero-slides');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let current = 0, paused = reducedMotion.matches, hovered = false, timer;
    const english = () => document.documentElement.lang === 'en';
    function schedule() {
        window.clearInterval(timer);
        if (paused || hovered || document.hidden || hero.hidden) return;
        timer = window.setInterval(() => show(current + 1, false), 7000);
    }
    function show(index, manual = true) {
        current = (index + slides.length) % slides.length;
        region.setAttribute('aria-live', manual ? 'polite' : 'off');
        slides.forEach((slide, i) => { slide.hidden = i !== current; });
        dots.forEach((dot, i) => {
            if (i === current) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
        hero.querySelector('[data-hero-count]').textContent = `${String(current + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
        schedule();
    }
    function updatePause() {
        pause.setAttribute('aria-pressed', String(paused));
        pause.setAttribute('aria-label', paused ? (english() ? 'Resume carousel' : 'Reanudar carrusel') : (english() ? 'Pause carousel' : 'Pausar carrusel'));
        pause.firstElementChild.textContent = paused ? '▶' : 'Ⅱ';
        schedule();
    }
    hero.querySelector('[data-hero-prev]').addEventListener('click', () => show(current - 1));
    hero.querySelector('[data-hero-next]').addEventListener('click', () => show(current + 1));
    dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));
    pause.addEventListener('click', () => { paused = !paused; updatePause(); });
    // Keyboard interaction stops rotation until the visitor explicitly resumes it.
    hero.addEventListener('focusin', event => { if (event.target.matches(':focus-visible')) { paused = true; updatePause(); } });
    hero.addEventListener('mouseenter', () => { hovered = true; schedule(); });
    hero.addEventListener('mouseleave', () => { hovered = false; schedule(); });
    document.addEventListener('visibilitychange', schedule);
    reducedMotion.addEventListener('change', () => { paused = reducedMotion.matches; updatePause(); });
    new MutationObserver(schedule).observe(hero, { attributes: true, attributeFilter: ['hidden'] });
    new MutationObserver(updatePause).observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
    hero.querySelector('.market-hero-controls').hidden = false;
    updatePause();
})();
