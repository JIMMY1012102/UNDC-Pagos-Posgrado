<section class="screen payment-preview" data-screen="payment" aria-labelledby="paymentPreviewTitle">
<nav class="payment-breadcrumb" aria-label="Ruta de compra"><a href="/tienda">Catálogo</a><span aria-hidden="true">/</span><button type="button" data-go="cart">Carrito</button><span aria-hidden="true">/</span><span aria-current="page">Revisión</span></nav>
<div class="payment-preview-heading">
<div><h1 id="paymentPreviewTitle" tabindex="-1">Todo listo para revisar.</h1><p>Comprueba tus datos antes de generar tu pedido.</p></div>
<ol class="payment-progress" aria-label="Etapas de la compra"><li><span aria-hidden="true">✓</span>01 Carrito</li><li aria-current="step"><span>02</span>Revisión</li></ol>
</div>
<div class="payment-preview-grid">
<section class="payment-selection-card" aria-label="Comprador y conceptos seleccionados">
<div class="payment-buyer"><span class="payment-buyer-avatar" id="paymentBuyerInitials" aria-hidden="true"></span><div class="payment-buyer-copy"><h2 id="paymentBuyerName"></h2><p><span id="paymentBuyerDocument"></span><span class="payment-contact-separator" aria-hidden="true"> · </span><span id="paymentBuyerEmail"></span></p></div><button class="payment-text-button" id="editPaymentBuyer" type="button">Editar<span class="sr-only"> datos del comprador</span></button></div>
<div class="payment-selection-heading"><h2>Tu selección</h2><span id="paymentSelectionCount"></span></div>
<div id="paymentPreviewItems"></div>
<a class="payment-add-concept" href="/tienda"><span aria-hidden="true">＋</span> Agregar otro concepto</a>
</section>
<div class="payment-summary-column"><aside class="payment-preview-summary" aria-labelledby="paymentSummaryTitle">
<h2 id="paymentSummaryTitle">Resumen del pedido</h2><p class="payment-summary-count"><strong id="paymentPreviewCount"></strong> conceptos seleccionados</p>
<div class="payment-total-block"><p id="paymentTotalLabel">Total referencial</p><strong class="payment-preview-total" id="paymentPreviewTotal"></strong><p class="payment-total-note">Sujeto a validación institucional.</p></div>
<p id="orderQuoteMessage" class="payment-quote-message" role="status" aria-live="polite">Verificando las tarifas vigentes…</p>
<button class="btn block" id="generateOrder" type="button" disabled><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>Generar pedido</button>
<button class="payment-text-button payment-modify" type="button" data-go="cart">Modificar selección</button>
</aside><p class="payment-gateway-note"><span aria-hidden="true">ⓘ</span> La pasarela de pago aún no está habilitada.</p></div>
</div>
<div class="payment-review-footer"><p><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/></svg><strong>Vigencia del pedido: 24 horas</strong></p><span>Generar un pedido no realiza un cobro ni inicia un trámite académico.</span></div>
</section>
<script src="{{ asset('js/undc-orders.js') }}?v={{ filemtime(public_path('js/undc-orders.js')) }}" defer></script>
