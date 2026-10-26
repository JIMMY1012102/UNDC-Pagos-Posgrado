<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Tienda virtual de conceptos de Posgrado — Universidad Nacional de Cañete">
<title>Tienda virtual de pagos · UNDC</title>
<link rel="stylesheet" href="{{ asset('css/undc-tienda.css') }}">
</head><body>
<header class="top"><a href="/tienda" class="brand" aria-label="Universidad Nacional de Cañete — Inicio"><span class="crest" aria-hidden="true">UNDC</span>
<span><b>Universidad Nacional de Cañete</b><small>Tienda virtual de pagos · Posgrado</small></span></a>
<nav class="nav" aria-label="Navegación"><a href="/tienda">Inicio</a><a href="#catalogo">Conceptos</a><button id="cartButton" type="button">🛒 Mi carrito <span id="cartBadge">0</span></button></nav></header>
<section class="hero"><div class="hero-inner"><div><span class="eyebrow">✦ Servicios digitales universitarios</span>
<h1>Tus trámites, mucho más cerca de ti.</h1><p>Encuentra los conceptos de pago de Posgrado, organiza tu carrito y prepara tu solicitud desde un solo lugar. Sin filas y con una experiencia sencilla.</p>
<span class="chip">Versión de precompra · Sin pagos habilitados</span></div>
<div class="hero-art" aria-hidden="true"><div class="hero-symbol">🎓</div></div></div></section>
<main class="wrap" id="catalogo">
<div class="warning"><strong>Plataforma en desarrollo.</strong> La selección de conceptos es informativa y aún no constituye una orden de pago. Los importes y los medios de pago requieren autorización institucional.</div>
<div class="steps" aria-label="Etapas de la compra"><span class="step active"><i>1</i> Catálogo</span><span class="step"><i>2</i> Carrito</span><span class="step"><i>3</i> Identificación</span><span class="step"><i>4</i> Revisión</span></div>
<div class="page-grid"><div>
<section class="screen visible" data-screen="catalog">
<div class="panel"><div class="title-row"><div><h2>Explora nuestros conceptos</h2><p class="subtle">Selecciona los servicios publicados para la oficina de Posgrado.</p></div><span class="chip">Catálogo institucional</span></div>
<div class="field"><label for="office">Identificador de oficina autorizado</label><div style="display:flex;gap:10px"><input type="number" id="office" min="1" placeholder="ID definido por Tesorería" autocomplete="off"><button class="btn" id="load" type="button">Consultar</button></div></div>
<div class="filters"><input id="search" type="search" placeholder="🔎 Buscar concepto o trámite..." aria-label="Buscar conceptos"></div>
<p id="catalogNote" class="subtle">Introduce el identificador oficial de la oficina para consultar el catálogo.</p>
<div class="products" id="catalogCards"><div class="empty">📚<br>Aquí aparecerán tus conceptos disponibles.</div></div></div></section>
<section class="screen" data-screen="cart">
<div class="panel"><button class="back" type="button" data-go="catalog">← Seguir explorando</button><h2>Revisa tu carrito</h2><p class="subtle">Confirma los conceptos que seleccionaste antes de identificarte.</p>
<div id="cartRows"></div><div class="cart-total"><strong id="cartTotalCount">0 conceptos</strong><span>Precio pendiente de aprobación</span></div>
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
<p class="subtle">Organiza tus conceptos antes de continuar.</p><p><strong id="emptyBadge">Aún no has agregado conceptos</strong></p><p id="sideCartSummary" class="subtle">Puedes consultar el detalle en tu carrito.</p>
<div class="info">🔒 Los pagos se habilitarán únicamente después de contar con la autorización de Tesorería.</div>
<button id="sideContinue" class="btn block" style="margin-top:17px" disabled type="button">Ir a comprar →</button></div>
<div class="panel" style="padding:20px"><b style="color:#174978">¿Por qué usar la tienda?</b><p class="subtle" style="margin-bottom:0">✓ Consulta desde tu celular<br>✓ Selecciona varios conceptos<br>✓ Conserva tu perfil para futuras visitas</p></div></aside></div>
</main><footer class="footer">Universidad Nacional de Cañete · Oficina de Tecnologías de la Información · Versión de desarrollo de la tienda de Posgrado</footer>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<script src="{{ asset('js/undc-tienda.js') }}" defer></script>
</body></html>