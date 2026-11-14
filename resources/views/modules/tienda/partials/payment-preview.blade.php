<style>

body.market-payment-view{--rv-surface:#073c2d;--rv-row:#0b4836;--rv-ink:#e9fff4;--rv-muted:#b8d8c9;--rv-line:#34735a;--rv-accent:#66e6ae;background:radial-gradient(ellipse at 0 0,#126b48,transparent 60%),#04291e}
body.light-mode.market-payment-view{--rv-surface:rgb(255 255 255 / 88%);--rv-row:#fff;--rv-ink:#062e22;--rv-muted:#3c524b;--rv-line:#d3e5dc;--rv-accent:#0a7a4f;background:radial-gradient(ellipse at 0 0,#d5eee1,transparent 60%),radial-gradient(ellipse at 100% 25%,#dceff1,transparent 55%),#eff7f3}
body.market-payment-view .site-header{padding:16px 24px 0;background:transparent;border:0;box-shadow:none}
body.market-payment-view .site-header .top{height:auto;background:transparent;border:0}
body.market-payment-view .top-inner{width:100%;max-width:1200px;min-height:72px;height:auto;margin:auto;padding:10px 22px;background:var(--rv-surface);border:1px solid var(--rv-line);border-radius:999px;gap:16px}
.market-payment-view main.wrap{max-width:1200px;width:calc(100% - 48px);padding:24px 0 30px;margin:auto}
.market-payment-view .brand-title,.market-payment-view .payment-header-context,.market-payment-view .payment-orders-link{color:var(--rv-ink)}
.market-payment-view .brand-title em{color:var(--rv-accent)}
.market-payment-view .login-button{border-radius:999px;background:var(--rv-row);color:var(--rv-ink)}
.market-payment-view .payment-preview-heading{gap:24px;align-items:flex-end;margin-bottom:24px;animation:rvRise .7s .05s both}
.market-payment-view .payment-preview-heading h1{font:800 clamp(30px,3.4vw,46px)/1.08 Sora,system-ui,sans-serif;letter-spacing:-.035em;color:var(--rv-ink)}
.market-payment-view .payment-preview-heading p,.market-payment-view .payment-breadcrumb{color:var(--rv-muted)}
.market-payment-view .payment-progress{padding:14px 20px;border:1px solid var(--rv-line);border-radius:999px;background:var(--rv-surface);gap:18px;animation:rvRise .7s .15s both}
.market-payment-view .payment-progress li{color:var(--rv-muted)}
.market-payment-view .payment-progress li span{width:34px;height:34px;border-radius:50%}
.market-payment-view .payment-progress li[aria-current] span{animation:rvRing 1.9s infinite}
.market-payment-view .payment-progress li:first-child span{animation:rvPop .6s both}
.market-payment-view .payment-progress li+li::before{transform-origin:left;animation:rvGrow .9s .2s both}
.market-payment-view .payment-progress li:last-child::before{background-image:repeating-linear-gradient(90deg,var(--rv-accent) 0 24px,var(--rv-line) 24px 48px);background-size:48px 100%;animation:rvFlow 1.4s linear infinite}
.market-payment-view .payment-preview-grid{grid-template-columns:minmax(0,1fr) 350px;gap:28px;align-items:start}
.market-payment-view .payment-selection-card,.market-payment-view .payment-preview-summary{background:var(--rv-surface);color:var(--rv-ink);border:1px solid var(--rv-line);border-radius:30px;box-shadow:0 24px 60px -34px rgb(6 46 34 / 30%);padding:26px;animation:rvRise .7s .25s both;transition:translate .35s}
.market-payment-view .payment-summary-column{animation:rvRise .7s .35s both}
.market-payment-view .payment-buyer{gap:16px;padding-bottom:22px;border-color:var(--rv-line)}
.market-payment-view .payment-buyer-avatar{width:60px;height:60px;flex-basis:60px;border-radius:50%;display:grid;place-items:center;animation:rvRing 2.6s infinite}
.market-payment-view .payment-buyer h2{font:700 21px Sora,system-ui,sans-serif;color:var(--rv-ink);overflow-wrap:anywhere}
.market-payment-view .payment-buyer p{color:var(--rv-muted);overflow-wrap:anywhere}
.market-payment-view #editPaymentBuyer{border:1px solid var(--rv-line);border-radius:999px;padding:10px 16px;min-height:44px;color:var(--rv-accent);white-space:nowrap}
.market-payment-view .payment-selection-heading{margin:22px 0 16px}
.market-payment-view .payment-selection-heading h2{font-size:21px;color:var(--rv-ink)}
.market-payment-view #paymentPreviewItems{counter-reset:review-item}
.market-payment-view #paymentPreviewItems .payment-preview-line{counter-increment:review-item;display:grid;grid-template-columns:50px minmax(0,1fr) auto;gap:16px;align-items:center;padding:18px;margin-bottom:12px;background:var(--rv-row);border:1px solid var(--rv-line);border-radius:22px;transition:translate .2s,box-shadow .2s}
.market-payment-view #paymentPreviewItems .payment-preview-line::before{content:counter(review-item,decimal-leading-zero);width:50px;height:50px;display:grid;place-items:center;border-radius:16px;border:1px solid var(--rv-line);color:var(--rv-accent);font-weight:700}
.market-payment-view .payment-preview-line strong{color:var(--rv-ink);font-size:15px}
.market-payment-view .payment-preview-line small{color:var(--rv-muted)}
.market-payment-view .payment-preview-line>span{color:var(--rv-ink);font:700 22px Sora,system-ui,sans-serif;font-variant-numeric:tabular-nums}
.market-payment-view .payment-add-concept{min-height:52px;border:2px dashed var(--rv-line);border-radius:22px;justify-content:center;color:var(--rv-accent)}
.market-payment-view .payment-preview-summary h2{font-size:13px;color:var(--rv-muted)}
.market-payment-view .payment-total-block{border-color:var(--rv-line)}
.market-payment-view .payment-total-block p,.market-payment-view .payment-summary-count,.market-payment-view .payment-gateway-note{color:var(--rv-muted)}
.market-payment-view .payment-preview-total{font:800 42px Sora,system-ui,sans-serif;color:var(--rv-ink)}
.market-payment-view #generateOrder{position:relative;overflow:hidden;min-height:54px;border-radius:999px;background:#0a7a4f;color:#fff;transition:translate .2s,box-shadow .2s}
.market-payment-view #generateOrder:not(:disabled)::after{content:"";position:absolute;inset:0 auto 0 0;width:35%;background:linear-gradient(90deg,transparent,rgb(255 255 255 / 26%),transparent);pointer-events:none;animation:rvShine 3.2s infinite}
.market-payment-view .payment-modify{color:var(--rv-accent)}
.market-payment-view .payment-review-footer{padding:16px 22px;background:var(--rv-surface);border:1px solid var(--rv-line);border-radius:22px;color:var(--rv-muted);animation:rvRise .7s .5s both}
.market-payment-view .payment-review-footer svg path{transform-origin:12px 12px;animation:rvSpin 8s linear infinite}
.market-payment-view>.footer{display:none}
@keyframes rvRise{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:none}}
@keyframes rvPop{0%{scale:.6;opacity:0}70%{scale:1.12;opacity:1}100%{scale:1}}
@keyframes rvRing{from{box-shadow:0 0 0 0 color-mix(in srgb,var(--rv-accent) 35%,transparent)}to{box-shadow:0 0 0 14px transparent}}
@keyframes rvGrow{from{transform:scaleX(0)}to{transform:scaleX(1)}}
@keyframes rvFlow{to{background-position:-48px 0}}
@keyframes rvSpin{to{transform:rotate(360deg)}}
@keyframes rvShine{0%{transform:translateX(-150%) skewX(-18deg)}60%,100%{transform:translateX(400%) skewX(-18deg)}}
@media(hover:hover){.market-payment-view .payment-selection-card:hover,.market-payment-view .payment-preview-summary:hover{translate:0 -4px}.market-payment-view .payment-preview-line:hover,.market-payment-view #generateOrder:not(:disabled):hover{translate:0 -2px}}
@media(max-width:1100px){.market-payment-view .payment-preview-heading{flex-wrap:wrap;align-items:flex-start}.market-payment-view .payment-progress{margin-top:12px}}
@media(max-width:800px){.market-payment-view .payment-preview-grid{grid-template-columns:minmax(0,1fr)}.market-payment-view main.wrap{width:calc(100% - 24px)}.market-payment-view .site-header{padding:12px 12px 0}.market-payment-view .top-inner{border-radius:24px;padding:12px}.market-payment-view .payment-selection-card,.market-payment-view .payment-preview-summary{padding:18px}.market-payment-view .payment-buyer{flex-wrap:wrap}.market-payment-view #paymentPreviewItems .payment-preview-line{grid-template-columns:42px minmax(0,1fr);gap:12px;padding:14px}.market-payment-view #paymentPreviewItems .payment-preview-line::before{width:42px;height:42px}.market-payment-view .payment-preview-line>span{grid-column:2;font-size:20px}.market-payment-view .payment-progress{gap:10px;padding:12px}.market-payment-view .payment-review-footer{flex-wrap:wrap;gap:12px}}
@media(min-width:1101px) and (max-height:700px){.market-payment-view main.wrap{padding-top:14px}.market-payment-view .payment-preview-heading{margin-bottom:16px}.market-payment-view .payment-preview-heading h1{font-size:34px}.market-payment-view .payment-selection-card,.market-payment-view .payment-preview-summary{padding:20px}.market-payment-view .payment-preview-total{font-size:34px}.market-payment-view .payment-review-footer{margin-top:16px;padding:12px 18px}}
@media(prefers-reduced-motion:reduce){body.market-payment-view *,body.market-payment-view *::before,body.market-payment-view *::after{animation:none!important;transition:none!important}}

</style>
<section class="screen payment-preview" data-screen="payment" aria-labelledby="paymentPreviewTitle">
<nav class="payment-breadcrumb" aria-label="Ruta de compra"><a href="/tienda">Catálogo</a><span aria-hidden="true">/</span><button type="button" data-go="cart">Carrito</button><span aria-hidden="true">/</span><span aria-current="page">Revisión</span></nav>
<div class="payment-preview-heading">
<div><h1 id="paymentPreviewTitle" tabindex="-1">Todo listo para revisar.</h1><p>Comprueba tus datos antes de generar tu pedido.</p></div>
<ol class="payment-progress" aria-label="Etapas de la compra"><li><span aria-hidden="true">✓</span>01 Carrito</li><li aria-current="step"><span>02</span>Revisión</li><li class="is-upcoming"><span>03</span>Pago</li></ol>
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
<div class="payment-total-block"><p id="paymentTotalLabel">Total del pedido</p><strong class="payment-preview-total" id="paymentPreviewTotal"></strong><p class="payment-total-note">Importes del catálogo oficial.</p></div>
<p id="orderQuoteMessage" class="payment-quote-message" role="status" aria-live="polite">Verificando las tarifas vigentes…</p>
<button class="btn block" id="generateOrder" type="button" disabled><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>Generar pedido</button>
<button class="payment-text-button payment-modify" type="button" data-go="cart">Modificar selección</button>
</aside><p class="payment-gateway-note"><span aria-hidden="true">ⓘ</span> La pasarela de pago aún no está habilitada.</p></div>
</div>
<div class="payment-review-footer"><p><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/></svg><strong>Vigencia del pedido: 24 horas</strong></p><span>Generar un pedido no realiza un cobro ni inicia un trámite académico.</span></div>
</section>
<script src="{{ asset('js/undc-orders.js') }}?v={{ filemtime(public_path('js/undc-orders.js')) }}" defer></script>
