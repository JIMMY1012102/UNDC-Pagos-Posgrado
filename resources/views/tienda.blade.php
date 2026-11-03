<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Tienda virtual de conceptos de Posgrado — Universidad Nacional de Cañete">
<title>Tienda virtual de pagos · UNDC</title>
<link rel="icon" type="image/png" href="{{ asset('Logo_UNDC_OF.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/undc-tienda.css') }}">
</head><body>
<header class="site-header">
<div class="top"><div class="top-inner">
<a href="/tienda" class="brand" aria-label="UNDC Postgrado — Inicio"><img class="crest" src="{{ asset('Logo_UNDC_OF.png') }}" alt="Escudo de la Universidad Nacional de Cañete"><span class="brand-copy"><span class="brand-title"><b>UNDC</b> <em>Postgrado</em></span></span></a>
<div class="utility-controls" aria-label="Configuraciones">
<div class="language-picker" data-language-picker data-no-translate><button class="utility-control language-button" type="button" aria-haspopup="true" aria-expanded="false"><span class="language-flag peru-flag" aria-hidden="true"><i></i></span><span class="language-label">ESPAÑOL</span><svg class="chevron" aria-hidden="true" viewBox="0 0 24 24"><path d="m8 10 4 4 4-4"/></svg></button><div class="language-menu" hidden><button type="button" data-language="es"><span class="peru-flag" aria-hidden="true"><i></i></span>Español</button><button type="button" data-language="en"><span class="usa-flag" aria-hidden="true"><i></i></span>English</button></div></div>
<button class="utility-control theme-control" type="button" data-theme-toggle aria-pressed="true"><span class="moon-pill"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z"/></svg></span><span class="theme-label">Modo oscuro</span><span class="theme-switch" aria-hidden="true"><i></i></span></button>
</div>
<div class="nav-actions"><button id="cartButton" class="cart-button" type="button" aria-label="Abrir carrito"><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M3 4h2l2.5 11h11l2-8H6"/></svg><span id="cartBadge">0</span></button><span class="action-divider" aria-hidden="true"></span><a class="login-button" href="/auth/google/redirect"><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><span>Inicia sesión</span></a></div>
<div class="mobile-primary-actions"><button id="mobileCartButton" class="mobile-cart" type="button" aria-label="Abrir carrito"><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M3 4h2l2.5 11h11l2-8H6"/></svg><span id="mobileCartBadge">0</span></button><a class="mobile-login" href="/auth/google/redirect" aria-label="Inicia sesión"><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></a></div>
<button id="headerMenuButton" class="menu-toggle" type="button" aria-controls="headerNavPanel" aria-expanded="false"><span></span><span></span><span></span><span class="sr-only">Abrir menú</span></button>
</div></div>
<div class="subheader" id="headerNavPanel"><nav class="nav" aria-label="Navegación principal">
<div class="nav-links"><a href="/tienda"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m3 11 9-8 9 8v10h-6v-6H9v6H3Z"/></svg><span>Para ti</span></a><a class="active" href="#catalogo" data-main-view-link="procedures" aria-current="page"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 2h8l4 4v16H6Z"/><path d="M14 2v5h5M9 12h6m-6 4h6"/></svg><span>Mis trámites</span></a><a href="#catalogo" data-main-view-link="catalogs"><svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Catalogos</span></a><a href="#catalogo" data-main-view-link="categories"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m2 9 10-5 10 5-10 5Z"/><path d="M6 11v5c3 3 9 3 12 0v-5"/></svg><span>Categorías</span></a></div>
<form class="header-search" role="search" action="#catalogo"><svg class="search-icon" aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input id="search" type="search" placeholder="Buscar maestrías, trámites, servicios..." aria-label="Buscar conceptos"><button type="submit" aria-label="Buscar"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></button></form>
<div class="mobile-settings" aria-label="Configuraciones secundarias"><div class="language-picker" data-language-picker data-no-translate><button class="language-button" type="button" aria-haspopup="true" aria-expanded="false"><span class="language-flag peru-flag" aria-hidden="true"><i></i></span><span class="language-label">ESPAÑOL</span></button><div class="language-menu" hidden><button type="button" data-language="es"><span class="peru-flag" aria-hidden="true"><i></i></span>Español</button><button type="button" data-language="en"><span class="usa-flag" aria-hidden="true"><i></i></span>English</button></div></div><button type="button" data-theme-toggle aria-pressed="true"><span class="theme-label">Modo oscuro</span> <span class="theme-switch" aria-hidden="true"><i></i></span></button></div>
</nav></div>
</header>
<section class="hero" data-main-view="procedures"><div class="hero-inner"><div class="hero-copy"><div class="hero-message"><h1>Tus trámites, mucho más cerca de ti.</h1><p>Realiza tus pagos académicos de posgrado de manera rápida, sencilla y segura</p><a class="hero-tramites-button" href="#catalogo">Nuestros trámites <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 5v14m-6-6 6 6 6-6"/></svg></a></div>
</div>
<div class="hero-art"><img class="hero-decor-layer hero-halo" src="{{ asset('assets/hero-undc/svg/01_halo_azul.svg') }}" alt=""><img class="hero-decor-layer hero-ribbon" src="{{ asset('assets/hero-undc/svg/03_cinta_azul_inferior.svg') }}" alt=""><img class="hero-decor-layer hero-arc" src="{{ asset('assets/hero-undc/svg/02_arco_superior.svg') }}" alt=""><img class="hero-decor-layer hero-dots-top" src="{{ asset('assets/hero-undc/svg/04_puntos_superiores.svg') }}" alt=""><img class="hero-decor-layer hero-dots-bottom" src="{{ asset('assets/hero-undc/svg/05_puntos_inferiores.svg') }}" alt=""><img class="hero-campus-image" src="{{ asset('tramite.png') }}" data-theme-light-src="{{ asset('tramite.png') }}" data-theme-dark-src="{{ asset('tramiteN.png') }}" alt="Comunidad de Posgrado de la Universidad Nacional de Cañete"></div></div></section>
<section class="hero" data-main-view="catalogs" hidden><div class="hero-inner"><div class="hero-copy"><div class="hero-message"><h1>Tus trámites, mucho más cerca de ti.</h1><p>Realiza tus pagos académicos de posgrado de manera rápida, sencilla y segura</p><a class="hero-tramites-button" href="#catalogo">Nuestros trámites <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 5v14m-6-6 6 6 6-6"/></svg></a></div>
</div>
<div class="hero-art"><img class="hero-decor-layer hero-halo" src="{{ asset('assets/hero-undc/svg/01_halo_azul.svg') }}" alt=""><img class="hero-decor-layer hero-ribbon" src="{{ asset('assets/hero-undc/svg/03_cinta_azul_inferior.svg') }}" alt=""><img class="hero-decor-layer hero-arc" src="{{ asset('assets/hero-undc/svg/02_arco_superior.svg') }}" alt=""><img class="hero-decor-layer hero-dots-top" src="{{ asset('assets/hero-undc/svg/04_puntos_superiores.svg') }}" alt=""><img class="hero-decor-layer hero-dots-bottom" src="{{ asset('assets/hero-undc/svg/05_puntos_inferiores.svg') }}" alt=""><img class="hero-campus-image" src="{{ asset('Fondo_2_of-transparente.png') }}" data-theme-light-src="{{ asset('Fondo_2_of-transparente.png') }}" data-theme-dark-src="{{ asset('Catalogo_Noche.png') }}" alt="Campus y comunidad de la Universidad Nacional de Cañete"></div></div></section>
<section class="hero" data-main-view="categories" hidden><div class="hero-inner"><div class="hero-copy"><div class="hero-message"><h1>Tus trámites, mucho más cerca de ti.</h1><p>Realiza tus pagos académicos de posgrado de manera rápida, sencilla y segura</p><a class="hero-tramites-button" href="#catalogo">Nuestros trámites <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 5v14m-6-6 6 6 6-6"/></svg></a></div>
</div>
<div class="hero-art"><img class="hero-campus-image" src="{{ asset('Fondo_Categorias.png') }}" data-theme-light-src="{{ asset('Fondo_Categorias.png') }}" data-theme-dark-src="{{ asset('ORI.png') }}" alt="Comunidad universitaria en la sección de categorías"></div></div></section>
<main class="wrap" id="catalogo">
<section class="tusne-catalog" id="tusneCatalog" aria-labelledby="tusneTitle">
<div class="tusne-heading"><div><h2 id="tusneTitle">Nuestros trámites</h2></div></div>
<div class="catalog-filters" id="catalogFilters" aria-label="Filtrar trámites por categoría"></div>
<div class="catalog-results-heading"><div><h3 id="catalogResultsTitle">Todos los conceptos</h3></div><strong id="visibleConceptCount" aria-live="polite">29 opciones visibles</strong></div>
<div class="tusne-results" id="tusneResults"></div>
<div class="catalog-empty" id="tusneEmpty" hidden><span>⌕</span><h3>No encontramos trámites</h3><p>Prueba otra búsqueda o vuelve a mostrar todas las categorías.</p><button class="btn" id="clearCatalogFilters" type="button">Limpiar filtros</button></div>
</section>
<dialog class="concept-dialog" id="conceptDialog" aria-labelledby="conceptDialogTitle"><button class="dialog-close" id="conceptDialogClose" type="button" aria-label="Cerrar detalle">×</button><div id="conceptDialogContent"></div></dialog>
<div class="page-grid"><div>
<section class="screen visible" data-screen="catalog">
<div class="panel official-catalog"><div class="title-row"><div><h2>Catálogo oficial publicado</h2><p class="subtle">Consulta únicamente conceptos vinculados y autorizados mediante los servicios institucionales existentes.</p></div><span class="chip">SISPAGOS · Catálogo institucional</span></div>
<div class="field"><label for="office">Identificador de oficina autorizado</label><div style="display:flex;gap:10px"><input type="number" id="office" min="1" placeholder="ID definido por Tesorería" autocomplete="off"><button class="btn" id="load" type="button">Consultar</button></div></div>
<p id="catalogNote" class="subtle">Introduce el identificador oficial de la oficina para consultar el catálogo.</p>
<div class="products" id="catalogCards"><div class="empty">📚<br>Aquí aparecerán tus conceptos disponibles.</div></div></div></section>
<section class="screen" data-screen="cart">
<div class="panel"><button class="back" type="button" data-go="catalog">← Seguir explorando</button><h2>Revisa tu carrito</h2><p class="subtle">Confirma los conceptos que seleccionaste antes de identificarte.</p>
<div id="cartRows"></div><div class="cart-total"><strong id="cartTotalCount">0 conceptos</strong></div>
<button class="btn block" id="cartContinue" type="button" disabled>Continuar con mi compra →</button>
<p class="smallprint">Solo al continuar te pediremos iniciar sesión. No se realizará ningún cobro.</p>
</div></section>
<section class="screen" data-screen="identity">
<div class="panel"><button class="back" type="button" data-go="cart">← Volver al carrito</button><h2>¡Ya casi terminamos!</h2><p class="subtle">Identifícate de forma segura para continuar con tus conceptos seleccionados.</p>
<div id="loginBox"><div class="login-note"><span class="google">G</span><div><b>Ingresar con tu cuenta Google</b><p class="smallprint" style="margin:4px 0">Puedes utilizar una cuenta Google personal o institucional. No necesitas registrarte antes de elegir conceptos.</p></div></div>
<button id="googleButton" class="btn block" type="button">Continuar con Google →</button><p class="smallprint">La autenticación real estará disponible cuando OTI configure las credenciales y HTTPS.</p></div>
<div id="profileBox" hidden><p class="info">Solo te solicitamos estos datos en tu primer ingreso. Se conservarán en tu perfil para próximos trámites.</p>
<form id="profileForm"><div class="form-grid">
<div class="field"><label for="firstNames">Nombres *</label><input id="firstNames" name="first_names" required maxlength="160" autocomplete="given-name"></div>
<div class="field"><label for="lastNames">Apellidos *</label><input id="lastNames" name="last_names" required maxlength="160" autocomplete="family-name"></div>
<div class="field"><label for="docType">Tipo de documento *</label><select id="docType" name="document_type" required><option value="DNI">DNI</option><option value="CE">Carné de extranjería</option><option value="PASAPORTE">Pasaporte</option></select></div>
<div class="field"><label for="docNumber">Número de documento *</label><input id="docNumber" name="document_number" required maxlength="32"></div>
<div class="field"><label for="phone">Teléfono</label><input id="phone" name="phone" autocomplete="tel" maxlength="30"></div>
<div class="field"><label for="email">Correo de contacto</label><input id="email" name="contact_email" type="email" autocomplete="email" maxlength="254"></div>
</div><button type="submit" class="btn block" style="margin-top:24px">Guardar y continuar →</button></form></div></div>
</section>
<section class="screen" data-screen="review"><div class="panel"><button class="back" type="button" id="reviewBack">← Volver</button><span class="chip">Último paso de precompra</span><h2 style="margin-top:15px">Revisa tu información</h2><p class="subtle">Estos datos y conceptos se usarían para el futuro pedido cuando se habilite el pago.</p>
<div class="summary-block"><h3>👤 Datos del comprador</h3><p><strong id="buyerName"></strong><br><span id="buyerDocument"></span><br><span id="buyerContact"></span></p></div>
<div class="summary-block"><h3>🧾 Conceptos seleccionados</h3><div id="reviewList"></div><p><b id="reviewCount">0 conceptos</b></p></div>
<div class="warning">Los precios aún no están aprobados. Por seguridad, el sistema no genera órdenes ni procesa pagos.</div>
<button class="btn block" type="button" disabled>Confirmar y pagar · Próximamente</button>
</div></section></div>
<aside class="side"><div class="panel"><div class="title-row"><h3>🛍️ Tu selección</h3><span class="chip">Precompra</span></div>
<p class="subtle" id="sideCartSummary">Organiza tus conceptos antes de continuar.</p><p><strong id="emptyBadge">Aún no has agregado conceptos</strong></p><div id="sideCartItems" class="side-cart-items"></div>
<button id="sideContinue" class="btn block" style="margin-top:17px" disabled type="button">Ir a comprar →</button></div>
<div class="panel" style="padding:20px"><b class="side-heading">¿Por qué usar la tienda?</b><p class="subtle" style="margin-bottom:0">✓ Consulta desde tu celular<br>✓ Selecciona varios conceptos<br>✓ Conserva tu perfil para futuras visitas</p></div></aside></div>
</main><footer class="footer">Universidad Nacional de Cañete · Oficina de Tecnologías de la Información · Versión de desarrollo de la tienda de Posgrado</footer>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<script src="{{ asset('js/undc-tienda.js') }}" defer></script>
</body></html>
