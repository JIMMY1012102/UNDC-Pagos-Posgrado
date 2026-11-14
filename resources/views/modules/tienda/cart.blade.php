@extends('layouts.market')
@section('title', 'Carrito · UNDC MARKET')
@section('marketPage', 'cart')
@section('bodyClass', 'commerce-view market-cart-view market-cart-stage')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<style>

body.market-cart-stage{--cart-surface:#073c2d;--cart-row:#0b4836;--cart-ink:#e9fff4;--cart-muted:#b8d8c9;--cart-line:#34735a;--cart-accent:#66e6ae;background:radial-gradient(ellipse at 0 0,#126b48,transparent 60%),#04291e}
body.light-mode.market-cart-stage{--cart-surface:rgb(255 255 255 / 88%);--cart-row:#fff;--cart-ink:#062e22;--cart-muted:#3c524b;--cart-line:#d3e5dc;--cart-accent:#0a7a4f;background:radial-gradient(ellipse at 0 0,#d5eee1,transparent 60%),radial-gradient(ellipse at 100% 25%,#dceff1,transparent 55%),#eff7f3}
.market-cart-stage .site-header{padding:16px 24px 0;background:transparent;border:0;box-shadow:none}
.market-cart-stage .top{background:transparent;border:0;height:auto}
.market-cart-stage .top-inner{width:100%;max-width:1200px;min-height:72px;height:auto;margin:auto;padding:10px 22px;background:var(--cart-surface);border:1px solid var(--cart-line);border-radius:999px;box-shadow:0 10px 40px -20px rgb(6 46 34 / 25%);gap:16px}
.market-cart-stage .brand-title,.market-cart-stage .payment-header-context,.market-cart-stage .payment-orders-link{color:var(--cart-ink)}
.market-cart-stage .brand-title em{color:var(--cart-accent)}
.market-cart-stage .login-button{border-radius:999px;background:var(--cart-row);color:var(--cart-ink)}
.market-cart-stage main.wrap{max-width:1200px;width:calc(100% - 48px);padding:30px 0 36px;margin:auto}
.market-cart-stage .page-grid.cart-view{display:block;margin:0}
.market-cart-stage .cart-modern{background:transparent;padding:0;border:0;box-shadow:none}
.market-cart-stage .payment-breadcrumb{margin-bottom:14px;color:var(--cart-muted)}
.market-cart-stage .payment-breadcrumb a{color:var(--cart-muted)}
.market-cart-stage .cart-modern-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:28px}
.market-cart-stage .cart-modern-heading h1{font-family:Sora,system-ui,sans-serif;font-size:clamp(30px,3.6vw,48px);font-weight:800;letter-spacing:-.035em;line-height:1.08;color:var(--cart-ink);margin:0 0 12px}
.market-cart-stage .cart-modern-heading p{color:var(--cart-muted);font-size:15px;margin:0}
.market-cart-stage .cart-progress{padding:14px 20px;border:1px solid var(--cart-line);border-radius:999px;background:var(--cart-surface);gap:20px;flex-shrink:0}
.market-cart-stage .cart-progress li{color:var(--cart-muted);font-size:13px;gap:8px}
.market-cart-stage .cart-progress li span{width:34px;height:34px;border-radius:50%;background:var(--cart-row);color:var(--cart-muted);border:1px solid var(--cart-line)}
.market-cart-stage .cart-progress li[aria-current] {color:var(--cart-accent)}
.market-cart-stage .cart-progress li[aria-current] span{background:#0a7a4f;color:#fff;border-color:#0a7a4f}
.market-cart-stage .payment-preview-grid{display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:28px;align-items:start}
.market-cart-stage .cart-review-panel,.market-cart-stage .cart-modern-summary{background:var(--cart-surface);color:var(--cart-ink);border:1px solid var(--cart-line);border-radius:30px;box-shadow:0 24px 60px -34px rgb(6 46 34 / 30%)}
.market-cart-stage .cart-review-panel{padding:24px;min-height:400px}
.market-cart-stage .cart-review-panel::after{display:none}
.market-cart-stage .cart-list-heading{margin:0 0 20px;padding:0 0 14px;border-bottom:1px solid var(--cart-line)}
.market-cart-stage .cart-list-heading h2{font-size:20px;color:var(--cart-ink)}
.market-cart-stage #cartTotalCount{color:var(--cart-accent)}
.market-cart-stage .cart-service-row{display:grid;grid-template-columns:50px minmax(0,1fr) auto 40px;gap:16px;align-items:center;padding:18px;margin-bottom:12px;background:var(--cart-row);border:1px solid var(--cart-line);border-radius:22px}
.market-cart-stage .cart-service-icon{width:50px;height:50px;border-radius:16px;background:#e1f2ea;color:#0a7a4f}
.market-cart-stage .cart-service-info b{color:var(--cart-ink);font-size:15px;line-height:1.4}
.market-cart-stage .cart-service-info small{color:var(--cart-muted);margin-top:6px}
.market-cart-stage .cart-service-price{color:var(--cart-ink);font-size:17px;white-space:nowrap}
.market-cart-stage .cart-remove{width:40px;height:40px;border-radius:50%;background:transparent;color:var(--cart-muted);border:1px solid var(--cart-line)}
.market-cart-stage .cart-remove:hover{background:#fde8e4;color:#9b2a15}
.market-cart-stage .payment-add-concept{border:1px dashed var(--cart-line);border-radius:18px;padding:16px;color:var(--cart-accent);background:transparent}
.market-cart-stage .cart-empty-state{min-height:350px;padding:12px 20px 24px;gap:14px}
.market-cart-stage .cart-empty-art{width:190px;height:170px;animation:cartPreviewFloat 4.6s ease-in-out infinite}
.market-cart-stage .cart-empty-state h3{font-size:25px;font-family:Sora,system-ui,sans-serif;color:var(--cart-ink)}
.market-cart-stage .cart-empty-state p{max-width:360px;color:var(--cart-muted);line-height:1.55}
.market-cart-stage .cart-explore-button,.market-cart-stage #sideContinue{background:#0a7a4f;color:#fff;border:0;border-radius:999px;min-height:52px;font-weight:700}
.market-cart-stage #sideContinue:disabled{background:var(--cart-line);color:var(--cart-muted);cursor:not-allowed;box-shadow:none}
.market-cart-stage .cart-modern-summary{padding:26px}
.market-cart-stage .cart-modern-summary h2{font-size:21px;color:var(--cart-ink);margin:0 0 24px}
.market-cart-stage .payment-total-block{border-color:var(--cart-line);padding:20px 0}
.market-cart-stage .payment-total-block p,.market-cart-stage .payment-quote-message,.market-cart-stage .payment-gateway-note{color:var(--cart-muted)}
.market-cart-stage .payment-preview-total{color:var(--cart-ink);font:800 38px Sora,system-ui,sans-serif}
.market-cart-stage .cart-modern-count{color:var(--cart-muted);border-color:var(--cart-line)}
.market-cart-stage .cart-modern-count strong{color:var(--cart-accent)}
.market-cart-stage .payment-text-button{color:var(--cart-accent)}
.market-cart-stage .cart-modern-footer{border-color:var(--cart-line);color:var(--cart-muted);margin-top:28px}
.market-cart-stage>.footer{display:none}
@keyframes cartPreviewFloat{0%,100%{transform:translateY(0) rotate(-2deg)}50%{transform:translateY(-10px) rotate(2deg)}}
@media(max-width:1100px){.market-cart-stage .cart-modern-heading{align-items:flex-start;flex-direction:column}.market-cart-stage .cart-progress{align-self:flex-start}}
@media(max-width:800px){.market-cart-stage .payment-preview-grid{grid-template-columns:minmax(0,1fr)}.market-cart-stage .site-header{padding:12px 12px 0}.market-cart-stage .top-inner{border-radius:24px;padding:12px}.market-cart-stage main.wrap{width:calc(100% - 24px);padding-top:24px}.market-cart-stage .cart-review-panel{padding:18px}.market-cart-stage .cart-progress{gap:14px;padding:12px}.market-cart-stage .cart-progress li{font-size:12px}.market-cart-stage .cart-service-row{grid-template-columns:42px minmax(0,1fr) 36px;gap:10px;padding:14px}.market-cart-stage .cart-service-icon{width:42px;height:42px}.market-cart-stage .cart-service-price{grid-column:2;grid-row:2}.market-cart-stage .cart-remove{grid-column:3;grid-row:1 / span 2}.market-cart-stage .cart-explore-button{white-space:normal;padding:12px 20px}.market-cart-stage .cart-modern-footer{flex-wrap:wrap;gap:10px}}
@media(prefers-reduced-motion:reduce){.market-cart-stage .cart-empty-art{animation:none}}


/* Only the pill paints the header; its outer strip stays transparent. */
body.market-cart-stage .site-header,
body.market-cart-stage .site-header .top{background:transparent;border:0;box-shadow:none}
/* Compact the empty state without locking scrolling or clipping content. */
@media(min-width:801px){
body.market-cart-stage:has(#cartEmptyState:not([hidden])) main.wrap{padding-top:14px;padding-bottom:14px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .payment-breadcrumb{margin-bottom:8px;font-size:12px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-modern-heading{margin-bottom:18px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-modern-heading h1{font-size:36px;margin-bottom:8px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-review-panel{min-height:0;padding:18px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-empty-state{min-height:0;padding:8px 16px 12px;gap:10px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-empty-art{width:145px;height:125px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-empty-state h3{font-size:23px;margin:0}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-empty-state>p{margin:0 0 6px;font-size:13px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .payment-add-concept{display:none}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-modern-summary{padding:20px 24px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-modern-summary h2{font-size:17px;margin:0 0 12px;letter-spacing:.06em}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .payment-total-block{margin-top:10px;padding:12px 0}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .payment-preview-total{font-size:32px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .payment-quote-message{margin:10px 0;padding:8px 10px;font-size:12px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) #sideContinue{min-height:46px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .payment-modify{min-height:30px;margin-top:8px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .payment-gateway-note{margin:8px 0 0;font-size:11px}
body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-modern-footer{margin-top:14px;padding-top:10px;font-size:11px}
}


/* Product-card composition only; keep the current palette and empty state. */
.market-cart-stage .cart-has-items .cart-review-panel{padding:26px}
.market-cart-stage .cart-products-title{display:flex;align-items:baseline;flex-wrap:wrap;gap:10px}
.market-cart-stage .cart-products-title h2{margin:0}
.market-cart-stage .cart-clear-all{min-height:44px;padding:0 18px;border:1px solid var(--cart-line);border-radius:999px;background:transparent;color:var(--cart-ink);font:600 14px inherit;cursor:pointer}
.market-cart-stage .cart-clear-all:disabled{opacity:.6;cursor:wait}
.market-cart-stage .cart-has-items .cart-service-row{grid-template-columns:56px minmax(0,1fr) minmax(110px,auto) 44px;gap:18px;padding:20px;margin-bottom:14px;border-radius:22px}
.market-cart-stage .cart-has-items .cart-service-icon{width:56px;height:56px;border-radius:18px;font:700 17px Sora,system-ui,sans-serif}
.market-cart-stage .cart-has-items .cart-service-info b{font-size:16px}
.market-cart-stage .cart-service-amount{display:flex;flex-direction:column;align-items:flex-end;gap:6px}
.market-cart-stage .cart-has-items .cart-service-price{font:700 22px Sora,system-ui,sans-serif;letter-spacing:-.02em;font-variant-numeric:tabular-nums}
.market-cart-stage .cart-official-rate{font-size:12px;font-weight:600;border:1px solid var(--cart-line);border-radius:999px;padding:3px 10px;color:var(--cart-accent)}
.market-cart-stage .cart-has-items .cart-remove{width:44px;height:44px}
.market-cart-stage .cart-has-items .payment-add-concept{min-height:56px;display:flex;justify-content:center;align-items:center;gap:10px;border-width:2px;border-radius:22px;margin-top:4px}
@media(max-width:800px){
.market-cart-stage .cart-has-items .cart-review-panel{padding:16px}
.market-cart-stage .cart-list-heading{flex-wrap:wrap;gap:12px}
.market-cart-stage .cart-has-items .cart-service-row{grid-template-columns:44px minmax(0,1fr) 44px;gap:12px;padding:16px}
.market-cart-stage .cart-has-items .cart-service-icon{width:44px;height:44px}
.market-cart-stage .cart-service-amount{grid-column:2;grid-row:2;align-items:flex-start}
.market-cart-stage .cart-has-items .cart-service-price{font-size:20px}
}


/* Override legacy compact rows so the number fits its own grid track. */
body.market-cart-stage .cart-modern .cart-review-panel #cartRows .cart-service-row{
grid-template-columns:56px minmax(0,1fr) minmax(110px,auto) 44px;gap:18px;padding:20px;margin:0 0 14px;border:1px solid var(--cart-line);border-radius:22px;background:var(--cart-row);
}
body.market-cart-stage .cart-modern #cartRows .cart-service-icon{
display:grid;place-items:center;align-self:center;justify-self:center;box-sizing:border-box;width:56px;height:56px;padding:0;margin:0;line-height:1;text-align:center;flex-shrink:0;
}
body.market-cart-stage .cart-modern #cartRows .cart-service-price::after{content:none}
@media(max-width:800px){
body.market-cart-stage .cart-modern .cart-review-panel #cartRows .cart-service-row{grid-template-columns:44px minmax(0,1fr) 44px;gap:12px;padding:16px}
body.market-cart-stage .cart-modern #cartRows .cart-service-icon{width:44px;height:44px}
}


/* Motion from the supplied prototype, using the existing palette. */
@keyframes cmRise{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:none}}
@keyframes cmItem{from{opacity:0;transform:translateX(-24px) scale(.98)}to{opacity:1;transform:none}}
@keyframes cmLeave{to{opacity:0;transform:translateX(30px) scale(.97)}}
@keyframes cmDrift{50%{transform:translate(60px,40px) scale(1.12)}}
@keyframes cmOrbit{to{transform:rotate(360deg)}}
@keyframes cmTwinkle{0%,100%{opacity:.25;scale:.7}50%{opacity:1;scale:1.15}}
@keyframes cmShadow{50%{scale:.72 1;opacity:.14}}
@keyframes cmRing{from{box-shadow:0 0 0 0 color-mix(in srgb,var(--cart-accent) 40%,transparent)}to{box-shadow:0 0 0 14px transparent}}
@keyframes cmFlow{to{background-position:-48px 0}}
@keyframes cmShine{0%{transform:translateX(-150%) skewX(-18deg)}60%,100%{transform:translateX(400%) skewX(-18deg)}}
.market-cart-stage .cart-modern-heading{animation:cmRise .7s .05s both}
.market-cart-stage .cart-review-panel{animation:cmRise .7s .25s both}
.market-cart-stage .payment-summary-column{animation:cmRise .7s .35s both}
.market-cart-stage .cart-progress{animation:cmRise .7s .15s both}
.market-cart-stage .cart-progress li[aria-current] span{animation:cmRing 1.9s infinite}
.market-cart-stage .cart-progress li+li::before{background-image:repeating-linear-gradient(90deg,var(--cart-accent) 0 24px,var(--cart-line) 24px 48px);background-size:48px 100%;animation:cmFlow 1.4s linear infinite}
.market-cart-stage .cart-service-row{animation:cmItem .5s both;animation-delay:calc(var(--cart-item-index,0) * 60ms);transition:transform .2s,box-shadow .2s}
.market-cart-stage .cart-service-row.cm-leaving{animation:cmLeave .28s both}
.market-cart-stage .cart-review-panel,.market-cart-stage .cart-modern-summary{transition:translate .35s,box-shadow .35s}
@media(hover:hover){.market-cart-stage .cart-review-panel:hover,.market-cart-stage .cart-modern-summary:hover{translate:0 -4px}.market-cart-stage .cart-service-row:hover{translate:0 -2px;box-shadow:0 12px 28px -14px rgb(6 46 34 / 28%)}}
.market-cart-stage :is(.cart-remove,.cart-clear-all,.payment-add-concept,#cartButton,.cart-explore-button,#sideContinue){transition:translate .2s,scale .2s,box-shadow .2s}
.market-cart-stage :is(.cart-remove,.cart-clear-all,.payment-add-concept,#cartButton):hover{translate:0 -2px}
.market-cart-stage .cart-remove:active{scale:.92}
.market-cart-stage :is(.cart-explore-button,#sideContinue){position:relative;overflow:hidden}
.market-cart-stage :is(.cart-explore-button,#sideContinue:not(:disabled))::after{content:"";position:absolute;inset:0 auto 0 0;width:35%;background:linear-gradient(90deg,transparent,rgb(255 255 255 / 26%),transparent);pointer-events:none;animation:cmShine 3.2s infinite}
.market-cart-stage :is(.cart-explore-button,#sideContinue):not(:disabled):hover{translate:0 -2px;box-shadow:0 18px 34px -12px rgb(10 122 79 / 35%)}
.market-cart-stage .cart-motion-art{position:relative;display:grid;place-items:center;width:190px;height:170px;flex-shrink:0}
.market-cart-stage .cart-motion-orbit{position:absolute;inset:0;animation:cmOrbit 14s linear infinite;pointer-events:none}
.market-cart-stage .cart-motion-orbit::after{content:"";position:absolute;top:8px;left:50%;width:9px;height:9px;border-radius:50%;background:var(--cart-accent)}
.market-cart-stage .cart-motion-orbit.second{inset:18px;animation-duration:22s;animation-direction:reverse}
.market-cart-stage .cart-motion-spark{position:absolute;right:10px;top:20px;color:var(--cart-accent);animation:cmTwinkle 2.6s infinite}
.market-cart-stage .cart-motion-shadow{position:absolute;bottom:0;width:100px;height:10px;border-radius:50%;background:var(--cart-accent);opacity:.28;filter:blur(6px);animation:cmShadow 4.6s infinite}
.market-cart-stage .cart-motion-glow{position:fixed;z-index:0;pointer-events:none;border-radius:50%;width:45vw;height:45vw;filter:blur(90px);background:var(--cart-accent);opacity:.07;animation:cmDrift 16s infinite;top:-20vw;left:-15vw}
.market-cart-stage .cart-motion-glow.second{left:auto;right:-20vw;top:15vh;animation-duration:20s;animation-direction:reverse}
body[data-accessibility-color] .cart-motion-glow{display:none}
@media(min-width:801px){body.market-cart-stage:has(#cartEmptyState:not([hidden])) .cart-motion-art{width:145px;height:125px}}
@media(prefers-reduced-motion:reduce){body.market-cart-stage *,body.market-cart-stage *::before,body.market-cart-stage *::after{animation:none!important;transition:none!important}}

.market-cart-stage main.wrap,.market-cart-stage .site-header{position:relative;z-index:1}
</style>
<div class="cart-motion-glow" aria-hidden="true"></div><div class="cart-motion-glow second" aria-hidden="true"></div>
<div class="page-grid cart-view"><div>
<section class="screen visible cart-modern payment-preview" data-screen="cart" aria-labelledby="cartPageTitle">
<nav class="payment-breadcrumb" aria-label="Ruta de compra"><a href="/tienda">Catálogo</a><span aria-hidden="true">/</span><span aria-current="page">Carrito</span></nav>
<div class="payment-preview-heading cart-modern-heading"><div><h1 id="cartPageTitle">Revisa tu carrito.</h1><p>Confirma los conceptos que seleccionaste antes de continuar.</p></div>
<ol class="payment-progress cart-progress" aria-label="Etapas de la compra"><li aria-current="step"><span>01</span>Carrito</li><li><span>02</span>Revisión</li><li><span>03</span>Pago</li></ol></div>
<div class="payment-preview-grid">
<section class="payment-selection-card cart-review-panel" aria-label="Conceptos del carrito">
<div class="payment-selection-heading cart-list-heading" id="cartListHeading" hidden><div class="cart-products-title"><h2>Tus conceptos</h2><span id="cartTotalCount">0 conceptos</span></div><button type="button" id="cartClearAll" class="cart-clear-all">Vaciar carrito</button></div>
<div id="cartEmptyState" class="cart-empty-state" hidden><div class="cart-motion-art"><span class="cart-motion-orbit" aria-hidden="true"></span><span class="cart-motion-orbit second" aria-hidden="true"></span><span class="cart-motion-spark" aria-hidden="true">✦</span><span class="cart-motion-shadow" aria-hidden="true"></span><svg class="cart-empty-art" viewBox="0 0 210 190" width="210" height="190" aria-hidden="true" >
                  <circle cx="105" cy="92" r="70" fill="#e1f2ea"></circle>
                  <path d="M52 62h20l14 62a8 8 0 0 0 8 6h56a8 8 0 0 0 8-6l10-42H80" fill="#fff" stroke="#0a7a4f" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"></path>
                  <rect x="96" y="88" width="9" height="22" rx="4.5" fill="#0a7a4f" opacity=".85"></rect>
                  <rect x="113" y="88" width="9" height="22" rx="4.5" fill="#0a7a4f" opacity=".6"></rect>
                  <rect x="130" y="88" width="9" height="22" rx="4.5" fill="#0a7a4f" opacity=".35"></rect>
                  <circle cx="98" cy="150" r="8" fill="#062e22"></circle>
                  <circle cx="144" cy="150" r="8" fill="#062e22"></circle>
                </svg></div><h3>Tu carrito está vacío</h3><p>Selecciona un concepto de nuestros catálogos o trámites para comenzar tu compra.</p><button type="button" class="btn cart-explore-button" data-go="catalog"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14l1 14H4L5 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg><span>Explorar conceptos y agregar al carrito</span><span aria-hidden="true">→</span></button></div>
<div id="cartRows"></div>
<a class="payment-add-concept" href="/tienda"><span aria-hidden="true">＋</span> Agregar otro concepto</a>
</section>
@include('modules.tienda.partials.sidebar')
</div>
<div class="payment-review-footer cart-modern-footer"><span>Universidad Nacional de Cañete · Oficina de Tecnologías de la Información</span><span>Versión de desarrollo · Posgrado</span></div>
</section>
@include('modules.tienda.partials.checkout')
@include('modules.tienda.partials.payment-preview')
</div></div>

<script>
(() => {
 const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
 const list = document.getElementById('cartRows');
 function stagger() { list.querySelectorAll('.cart-service-row').forEach((row,index) => row.style.setProperty('--cart-item-index',Math.min(index,8))); }
 new MutationObserver(stagger).observe(list,{childList:true});
 list.addEventListener('click',event => {
  const button=event.target.closest('.cart-remove');
  if(button && !reduced.matches) {
   const row=button.closest('.cart-service-row');row.classList.add('cm-leaving');
   // Restore the row if the server rejects removal.
   window.setTimeout(()=>row.classList.remove('cm-leaving'),300);
  }
 });
 function pulseOnChange(id,keyframes) {
  const element=document.getElementById(id);if(!element)return;
  let previous=element.textContent;
  new MutationObserver(()=>{
   const next=element.textContent;if(next===previous)return;previous=next;
   if(!reduced.matches && document.body.classList.contains('market-cart-stage'))element.animate(keyframes,{duration:450,easing:'cubic-bezier(.3,1.6,.5,1)'});
  }).observe(element,{childList:true,subtree:true,characterData:true});
 }
 pulseOnChange('cartBadge',[{transform:'scale(1)'},{transform:'scale(1.55)'},{transform:'scale(1)'}]);
 pulseOnChange('cartSummaryEstimate',[{opacity:.4,transform:'translateY(6px)'},{opacity:1,transform:'none'}]);
 pulseOnChange('sideCartSummary',[{opacity:0,transform:'translateY(8px)'},{opacity:1,transform:'none'}]);
 stagger();
})();
</script>

@endsection
