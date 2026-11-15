<section class="hero market-hero institutional-hero immersive-hero" aria-label="Posgrado UNDC">
<img class="market-hero-photo" src="{{ asset('assets/hero-undc/posgrado-comunidad.webp') }}" alt="Comunidad de Posgrado de la Universidad Nacional de Cañete" width="1672" height="941" fetchpriority="high">
<div class="market-hero-content">
<p class="market-hero-eyebrow">POSGRADO / UNDC</p>
<div class="market-hero-slides"><article data-hero-slide>
<h1>Tus metas<br>avanzan.</h1>
<h2 class="immersive-subtitle">Tus trámites también.</h2>
<p>Encuentra y paga tus trámites de posgrado en un solo lugar.</p>
<div id="storeHeroSearch"></div>
<div class="store-quick-links"><a href="/tienda?q=matrícula#catalogo" data-store-quick="matrícula">Matrícula ↗</a><a href="/tienda?q=pensión#catalogo" data-store-quick="pensión">Pensiones ↗</a><a href="/tienda?q=constancia#catalogo" data-store-quick="constancia">Constancias ↗</a></div>
</article></div>
<a class="hero-scroll-cue" href="#catalogo">Explora tu próximo paso <span aria-hidden="true">↓</span></a>
</div>
</section>
<script>
(() => {
 const header=document.querySelector('.site-header');
 if(!header)return;
 const resize=()=>document.documentElement.style.setProperty('--market-header-height',header.getBoundingClientRect().height+'px');
 resize();
 new ResizeObserver(resize).observe(header);
 window.addEventListener('resize',resize,{passive:true});
})();
</script>
