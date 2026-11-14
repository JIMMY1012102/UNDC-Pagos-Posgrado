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

</style>
<div class="page-grid cart-view"><div>
<section class="screen visible cart-modern payment-preview" data-screen="cart" aria-labelledby="cartPageTitle">
<nav class="payment-breadcrumb" aria-label="Ruta de compra"><a href="/tienda">Catálogo</a><span aria-hidden="true">/</span><span aria-current="page">Carrito</span></nav>
<div class="payment-preview-heading cart-modern-heading"><div><h1 id="cartPageTitle">Revisa tu carrito.</h1><p>Confirma los conceptos que seleccionaste antes de continuar.</p></div>
<ol class="payment-progress cart-progress" aria-label="Etapas de la compra"><li aria-current="step"><span>01</span>Carrito</li><li><span>02</span>Revisión</li><li><span>03</span>Pago</li></ol></div>
<div class="payment-preview-grid">
<section class="payment-selection-card cart-review-panel" aria-label="Conceptos del carrito">
<div class="payment-selection-heading cart-list-heading" id="cartListHeading" hidden><h2>Tu selección</h2><span id="cartTotalCount">0 conceptos</span></div>
<div id="cartEmptyState" class="cart-empty-state" hidden><svg class="cart-empty-art" viewBox="0 0 210 190" width="210" height="190" aria-hidden="true" >
                  <circle cx="105" cy="92" r="70" fill="#e1f2ea"></circle>
                  <path d="M52 62h20l14 62a8 8 0 0 0 8 6h56a8 8 0 0 0 8-6l10-42H80" fill="#fff" stroke="#0a7a4f" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"></path>
                  <rect x="96" y="88" width="9" height="22" rx="4.5" fill="#0a7a4f" opacity=".85"></rect>
                  <rect x="113" y="88" width="9" height="22" rx="4.5" fill="#0a7a4f" opacity=".6"></rect>
                  <rect x="130" y="88" width="9" height="22" rx="4.5" fill="#0a7a4f" opacity=".35"></rect>
                  <circle cx="98" cy="150" r="8" fill="#062e22"></circle>
                  <circle cx="144" cy="150" r="8" fill="#062e22"></circle>
                </svg><h3>Tu carrito está vacío</h3><p>Selecciona un concepto de nuestros catálogos o trámites para comenzar tu compra.</p><button type="button" class="btn cart-explore-button" data-go="catalog"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14l1 14H4L5 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg><span>Explorar conceptos y agregar al carrito</span><span aria-hidden="true">→</span></button></div>
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
@endsection
