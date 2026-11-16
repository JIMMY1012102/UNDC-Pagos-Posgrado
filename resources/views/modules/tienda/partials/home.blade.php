<div id="marketHome" class="market-home">
<div class="home-bands" aria-hidden="true"><div>Matrícula / Pensiones / Constancias / Certificados / Matrícula / Pensiones / Constancias / Certificados /</div><div>Grados / Títulos / Mis pedidos / Ayuda / Grados / Títulos / Mis pedidos / Ayuda /</div></div>
<section id="homeStory" class="home-story" aria-labelledby="homeStoryTitle">
<div class="home-story-stage">
<div class="home-story-copy"><p class="home-eyebrow">Así funciona <span id="homeStoryCounter" aria-hidden="true">01 / 03</span></p><h2 id="homeStoryTitle" class="home-sr-only">Elige, revisa y confirma tu pedido</h2>
<ol><li><h3>Elige.</h3><p>Encuentra el concepto que necesitas: matrícula, pensiones, constancias y más.</p></li><li><h3>Revisa.</h3><p>Organiza tus conceptos en el carrito y revisa el resumen antes de continuar.</p></li><li><h3>Confirma.</h3><p>Genera tu pedido y consulta su seguimiento desde Mis pedidos.</p></li></ol></div>
<div class="home-story-photo"><img src="{{ asset('assets/hero-undc/posgrado-comunidad.webp') }}" alt="Comunidad de Posgrado reunida en el campus UNDC" loading="lazy" width="1672" height="941"><a id="homeStoryLink" href="/tienda/catalogo">Explorar trámites ↗</a></div>
</div></section>
<section class="home-discover" aria-labelledby="homeForYouTitle"><div class="home-section-heading"><div><p class="home-eyebrow">Para ti</p><h2 id="homeForYouTitle">Tu próximo<br>paso empieza aquí.</h2></div><a href="/tienda/catalogo">Ver catálogo ↗</a></div>
<div class="home-category-grid">
@foreach([
['Admisión', 'admision', 'Da el primer paso hacia tu posgrado.'],
['Matrícula y pensiones', 'matricula_pensiones', 'Encuentra los conceptos de tu formación.'],
['Trámites académicos', 'tramites', 'Gestiona tus solicitudes académicas.'],
['Certificados y constancias', 'certificados_constancias', 'Consulta los documentos que necesitas.'],
['Tesis y grados', 'tesis_grados', 'Avanza hacia tu siguiente logro.'],
['Servicios administrativos', 'servicios_administrativos', 'Encuentra otros servicios de Posgrado.']
] as [$label, $category, $description])
<a class="home-category-card" href="{{ url('/tienda/catalogo') }}?category={{ $category }}"><span>POSGRADO / UNDC</span><h3>{{ $label }}</h3><p>{{ $description }}</p><strong>Explorar ↗</strong></a>
@endforeach
</div>
<div class="home-section-heading home-featured-heading"><h2>Conceptos destacados</h2><a href="/tienda/catalogo">Todos los conceptos ↗</a></div><div id="homeFeaturedCards" class="home-featured-grid" aria-live="polite"><p>Consulta los conceptos disponibles en el catálogo.</p></div>
</section>
<section class="home-news home-facebook-news" aria-labelledby="homeNewsTitle">
<div class="home-news-inner">
<div class="home-news-copy">
<p class="home-eyebrow">Conecta con Posgrado</p>
<span class="home-news-label"><span aria-hidden="true">f</span> Comunidad UNDC</span>
<h2 id="homeNewsTitle">Lo que pasa<br>en <em>Posgrado.</em></h2>
<p class="home-news-description">Convocatorias, actividades y novedades de nuestra comunidad. Encuentra las publicaciones de Posgrado UNDC en un solo lugar.</p>
<div class="home-news-topics" aria-label="Temas de la comunidad"><span>Convocatorias</span><span>Vida académica</span><span>Actividades</span></div>
<a class="home-news-cta" href="https://www.facebook.com/posgradoundc/" target="_blank" rel="noopener noreferrer">Visitar Facebook de Posgrado <span aria-hidden="true">↗</span></a>
<p class="home-news-account">@posgradoundc · Universidad Nacional de Cañete</p>
</div>
<div class="home-facebook-card">
<div class="home-facebook-heading"><span class="home-facebook-logo" aria-hidden="true">f</span><div><h3>Posgrado UNDC</h3><p>Publicaciones en Facebook</p></div><a href="https://www.facebook.com/posgradoundc/" target="_blank" rel="noopener noreferrer" aria-label="Abrir Facebook de Posgrado en otra pestaña">↗</a></div>
<div id="homeFacebookFeed" class="home-facebook-feed" aria-label="Publicaciones de Facebook de Posgrado UNDC">
<iframe id="homeFacebookFrame" title="Publicaciones de Facebook de Posgrado UNDC" width="500" height="580" loading="lazy" src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Fposgradoundc%2F&amp;tabs=timeline&amp;width=500&amp;height=580&amp;small_header=true&amp;adapt_container_width=true&amp;hide_cover=false&amp;show_facepile=false" allow="encrypted-media; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin"></iframe>
</div>
<p class="home-facebook-fallback">¿No se muestran las publicaciones? <a href="https://www.facebook.com/posgradoundc/" target="_blank" rel="noopener noreferrer">Ábrelas en Facebook ↗</a></p>
</div>
</div>
</section>
<section class="home-ending"><span class="home-ending-orb" aria-hidden="true"></span><h2>Tus metas<br>avanzan.</h2><p>Tus trámites también.</p><div><a href="/tienda/catalogo">Explorar trámites →</a><a href="#hero">Volver al inicio ↑</a></div></section>
</div>
