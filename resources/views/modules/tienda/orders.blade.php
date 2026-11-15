@extends('layouts.market')
@section('title', 'Mis pedidos · UNDC MARKET')
@section('marketPage', 'orders')
@section('bodyClass', 'commerce-view market-cart-view market-orders-view')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<style>

body.market-orders-view{--od-surface:#073c2d;--od-row:#0b4836;--od-ink:#e9fff4;--od-muted:#b8d8c9;--od-line:#34735a;--od-accent:#66e6ae;background:radial-gradient(ellipse at 0 0,#126b48,transparent 60%),#04291e}
body.light-mode.market-orders-view{--od-surface:rgb(255 255 255 / 88%);--od-row:#fff;--od-ink:#062e22;--od-muted:#3c524b;--od-line:#d3e5dc;--od-accent:#0a7a4f;background:radial-gradient(ellipse at 0 0,#d5eee1,transparent 60%),radial-gradient(ellipse at 100% 25%,#dceff1,transparent 55%),#eff7f3}
body.market-orders-view .site-header{padding:16px 24px 0;background:transparent;border:0;box-shadow:none}
body.market-orders-view .site-header .top{height:auto;background:transparent;border:0}
.market-orders-view .top-inner{width:100%;max-width:1200px;min-height:72px;height:auto;margin:auto;padding:10px 22px;background:var(--od-surface);border:1px solid var(--od-line);border-radius:999px;gap:16px}
.market-orders-view .brand-title,.market-orders-view .payment-header-context,.market-orders-view .payment-orders-link{color:var(--od-ink)}
.market-orders-view .brand-title em{color:var(--od-accent)}
.market-orders-view .login-button{border-radius:999px;background:var(--od-row);color:var(--od-ink)}
.market-orders-view main.wrap{width:calc(100% - 48px);max-width:1200px;padding:28px 0 36px;margin:auto}
.market-orders-view .payment-preview-heading{gap:24px;align-items:center;margin-bottom:26px;animation:odRise .7s .05s both}
.market-orders-view .payment-preview-heading h1{font:800 clamp(30px,3.3vw,46px)/1.08 Sora,system-ui,sans-serif;color:var(--od-ink);letter-spacing:-.035em}
.market-orders-view .payment-preview-heading p,.market-orders-view .payment-breadcrumb{color:var(--od-muted)}
.order-ready-heading{display:flex;align-items:center;gap:20px;min-width:0}
.order-ready-mark{display:none;position:relative;width:66px;height:66px;flex:0 0 66px;border-radius:50%;background:#0a7a4f;color:white;place-items:center;animation:odPop .8s .1s both}
#orderDetailPanel[data-order-status="pending"] .order-ready-mark{display:grid}
.order-ready-mark::before{content:"";position:absolute;inset:0;border:3px solid var(--od-accent);border-radius:50%;animation:odBurst 1.6s .5s 2 both}
.order-ready-mark svg{width:34px;height:34px}
.order-ready-mark path{stroke-dasharray:30;stroke-dashoffset:30;animation:odDraw .7s .6s forwards}
.market-orders-view .payment-progress{padding:14px 20px;border:1px solid var(--od-line);border-radius:999px;background:var(--od-surface);gap:16px}
.market-orders-view .payment-progress li{color:var(--od-muted)}
.market-orders-view .payment-progress li span{width:34px;height:34px;border-radius:50%}
.market-orders-view .payment-progress li:not([aria-current]) span{animation:odPop .6s both}
.market-orders-view .payment-progress li[aria-current] span{animation:odRing 1.9s infinite}
.market-orders-view .payment-progress li+li::before{transform-origin:left;animation:odGrow .9s .2s both}
.market-orders-view .order-detail-grid{display:grid;grid-template-columns:minmax(0,1fr) 370px;gap:28px;align-items:start}
.market-orders-view .order-selection,.market-orders-view #orderDetail .payment-preview-summary{background:var(--od-surface);color:var(--od-ink);border:1px solid var(--od-line);border-radius:30px;padding:26px;box-shadow:0 24px 60px -34px rgb(6 46 34 / 30%);animation:odRise .7s .25s both;transition:translate .35s}
.market-orders-view #orderDetail .payment-summary-column{animation:odRise .7s .35s both}
.market-orders-view .payment-buyer{border-color:var(--od-line);padding-bottom:22px;gap:16px}
.market-orders-view .payment-buyer-avatar{width:60px;height:60px;flex-basis:60px;border-radius:50%;display:grid;place-items:center}
.market-orders-view .payment-buyer h2{font:700 21px Sora,system-ui,sans-serif;color:var(--od-ink);overflow-wrap:anywhere}
.market-orders-view .payment-buyer p,.market-orders-view .order-dates{color:var(--od-muted);overflow-wrap:anywhere}
.market-orders-view .payment-selection-heading h2{font-size:21px;color:var(--od-ink)}
.market-orders-view .order-concepts{counter-reset:order-item}
.market-orders-view .order-concepts .payment-preview-line{counter-increment:order-item;display:grid;grid-template-columns:50px minmax(0,1fr) auto;gap:16px;align-items:center;padding:18px;border:1px solid var(--od-line);border-radius:22px;background:var(--od-row);margin-bottom:12px;transition:translate .2s}
.market-orders-view .order-concepts .payment-preview-line::before{content:counter(order-item,decimal-leading-zero);width:50px;height:50px;display:grid;place-items:center;border-radius:16px;border:1px solid var(--od-line);color:var(--od-accent);font-weight:700}
.market-orders-view .payment-preview-line strong{color:var(--od-ink);font-size:15px}
.market-orders-view .payment-preview-line small{color:var(--od-muted)}
.market-orders-view .payment-preview-line>span{color:var(--od-ink);font:700 21px Sora,system-ui,sans-serif;white-space:nowrap}
.market-orders-view .order-dates{display:flex;flex-wrap:wrap;gap:8px 20px;padding:14px 0;font-size:13px;border-bottom:1px solid var(--od-line)}
.market-orders-view .order-tracking{padding-top:18px;border:0;background:transparent}
.market-orders-view .order-tracking summary{cursor:pointer;color:var(--od-ink);font-weight:700;min-height:40px}
.market-orders-view .order-timeline{list-style:none;padding:10px 0 0 12px;margin:0}
.market-orders-view .order-timeline li{position:relative;padding:0 0 22px 24px;border-left:2px solid var(--od-line);color:var(--od-muted);font-size:13px;line-height:1.6;animation:odSlide .55s both;animation-delay:calc(var(--order-history-index,0) * 120ms)}
.market-orders-view .order-timeline li::before{content:"";position:absolute;left:-7px;top:4px;width:12px;height:12px;border-radius:50%;background:var(--od-accent);border:2px solid var(--od-surface)}
.market-orders-view .order-timeline li:last-child{border-left-color:transparent;padding-bottom:4px}
.market-orders-view .order-code-box{display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid var(--od-line);border-radius:16px;margin:14px 0}
.market-orders-view .order-code{min-width:0;flex:1;overflow-wrap:anywhere;color:var(--od-ink);font-size:12px;margin:0}
.market-orders-view .order-copy-code{border:1px solid var(--od-line);border-radius:12px;padding:8px 10px;min-height:40px;background:var(--od-row);color:var(--od-accent);font-weight:600;cursor:pointer}
.market-orders-view .order-status{border-radius:999px}
#orderDetailPanel[data-order-status="pending"] .order-status::before{content:"";display:inline-block;width:8px;height:8px;margin-right:8px;background:currentColor;border-radius:50%;animation:odRing 1.6s infinite}
.market-orders-view .payment-preview-total{font:800 42px Sora,system-ui,sans-serif;color:var(--od-ink)}
.market-orders-view .payment-total-block,.market-orders-view .order-payment-options{border-color:var(--od-line)}
.market-orders-view .payment-total-block p,.market-orders-view .payment-gateway-note{color:var(--od-muted)}
.market-orders-view .order-payment-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.market-orders-view .order-payment-options h3{font:700 18px Sora,system-ui,sans-serif;color:var(--od-ink)}
.market-orders-view .order-payment-method{position:relative;display:flex;align-items:center;gap:10px;min-height:78px;padding:14px 10px;border:1px solid var(--od-line);border-radius:18px;background:var(--od-row);color:var(--od-ink);text-align:left;cursor:pointer;transition:translate .2s,box-shadow .2s}
.market-orders-view .order-payment-method[aria-pressed="true"]{border-color:var(--od-accent);box-shadow:inset 0 0 0 1px var(--od-accent)}
.market-orders-view .order-payment-method[aria-pressed="true"]::after{content:"✓";position:absolute;right:6px;top:4px;color:var(--od-accent);animation:odPop .3s both}
.market-orders-view .order-payment-method strong{font-size:14px;color:var(--od-ink)}
.market-orders-view .order-payment-method div>span{color:var(--od-muted);font-size:12px}
.market-orders-view .order-pay-button{min-height:54px;border-radius:999px}
.market-orders-view .payment-modify{color:var(--od-accent)}
.market-orders-view .orders-footer{margin-top:26px;border-color:var(--od-line);color:var(--od-muted)}
.market-orders-view>.footer{display:none}
@keyframes odRise{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:none}}
@keyframes odPop{0%{scale:.5;opacity:0}70%{scale:1.14;opacity:1}100%{scale:1;opacity:1}}
@keyframes odBurst{from{scale:.6;opacity:.7}to{scale:2.1;opacity:0}}
@keyframes odRing{from{box-shadow:0 0 0 0 color-mix(in srgb,var(--od-accent) 35%,transparent)}to{box-shadow:0 0 0 14px transparent}}
@keyframes odDraw{to{stroke-dashoffset:0}}
@keyframes odGrow{from{transform:scaleX(0)}to{transform:scaleX(1)}}
@keyframes odSlide{from{opacity:0;transform:translateX(-14px)}to{opacity:1;transform:none}}
@media(hover:hover){.market-orders-view .order-selection:hover,.market-orders-view #orderDetail .payment-preview-summary:hover{translate:0 -4px}.market-orders-view .order-payment-method:hover,.market-orders-view .payment-preview-line:hover{translate:0 -2px}}
@media(max-width:1100px){.market-orders-view #orderDetailPanel>.payment-preview-heading{flex-wrap:wrap;align-items:flex-start}}
@media(max-width:800px){.market-orders-view .order-detail-grid{grid-template-columns:minmax(0,1fr)}.market-orders-view main.wrap{width:calc(100% - 24px)}.market-orders-view .site-header{padding:12px 12px 0}.market-orders-view .top-inner{padding:12px;border-radius:24px}.market-orders-view .order-selection,.market-orders-view #orderDetail .payment-preview-summary{padding:18px}.order-ready-heading{gap:14px}.order-ready-mark{width:48px;height:48px;flex-basis:48px}.market-orders-view .order-concepts .payment-preview-line{grid-template-columns:42px minmax(0,1fr);padding:14px;gap:12px}.market-orders-view .order-concepts .payment-preview-line::before{width:42px;height:42px}.market-orders-view .payment-preview-line>span{grid-column:2}.market-orders-view .payment-progress{padding:12px;gap:10px}.market-orders-view .orders-footer{flex-wrap:wrap;gap:12px}}
@media(max-width:380px){.market-orders-view .order-payment-grid{grid-template-columns:minmax(0,1fr)}}
@media(prefers-reduced-motion:reduce){body.market-orders-view *,body.market-orders-view *::before,body.market-orders-view *::after{animation:none!important;transition:none!important}.order-ready-mark path{stroke-dashoffset:0}.order-ready-mark::before{display:none}}


/* Complete the reference details without replacing real order state. */
.market-orders-view .order-dates p{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;margin:0;border-radius:999px;border:1px solid var(--od-line);background:var(--od-row)}
.market-orders-view .order-dates p::before{content:"▦";font-size:18px;color:var(--od-accent)}
.market-orders-view .order-dates p:last-child::before{content:"◷"}
.market-orders-view .order-concepts .payment-preview-line>span::after{content:"Tarifa oficial";display:block;width:fit-content;margin:6px 0 0 auto;padding:3px 10px;border:1px solid var(--od-line);border-radius:999px;color:var(--od-accent);font:600 12px system-ui,sans-serif}
.market-orders-view .order-tracking summary{list-style:none;display:flex;align-items:center;gap:10px}
.market-orders-view .order-tracking summary::-webkit-details-marker{display:none}
.market-orders-view .order-tracking summary::before{content:"›";font-size:24px;transition:rotate .25s}
.market-orders-view .order-tracking[open] summary::before{rotate:90deg}
.market-orders-view .order-tracking .order-timeline{padding:12px 0 8px 16px}
.market-orders-view .order-tracking .order-timeline li{min-height:58px;padding-left:30px;padding-bottom:24px}
.market-orders-view .order-tracking .order-timeline li::before{content:"✓";display:grid;place-items:center;left:-15px;top:0;width:28px;height:28px;font-size:13px;font-weight:800;background:#0a7a4f;color:#fff}
.market-orders-view .order-timeline strong,.market-orders-view .order-timeline time,.market-orders-view .order-timeline small{display:block}
.market-orders-view .order-timeline strong{font-size:15px;color:var(--od-ink)}
.market-orders-view .order-timeline time,.market-orders-view .order-timeline small{font-size:12px;color:var(--od-muted)}
.market-orders-view .order-tracking .order-timeline li[aria-current]::before{content:"";background:var(--od-row);border:3px solid var(--od-accent);animation:odRing 1.6s infinite}
.market-orders-view .order-tracking .order-timeline li.is-future::before{content:"";background:var(--od-row);border:3px solid var(--od-line)}
.market-orders-view .order-tracking .order-timeline li.is-future strong{color:var(--od-muted)}
.market-orders-view .order-ready-glow{position:fixed;pointer-events:none;z-index:0;top:-180px;left:-160px;width:520px;height:520px;border-radius:50%;background:var(--od-accent);opacity:.08;filter:blur(90px);animation:odDrift 16s infinite}
.market-orders-view .order-ready-glow.second{left:auto;right:-180px;top:120px;animation-duration:20s;animation-direction:reverse}
.market-orders-view main.wrap,.market-orders-view .site-header{position:relative;z-index:1}
@keyframes odDrift{50%{transform:translate(60px,40px) scale(1.12)}}
body[data-accessibility-color] .order-ready-glow{display:none}
@media(max-width:800px){.market-orders-view .order-concepts .payment-preview-line>span::after{margin-left:0}}
@media(prefers-reduced-motion:reduce){.market-orders-view .order-ready-glow,.market-orders-view .order-tracking .order-timeline li[aria-current]::before{animation:none!important}.market-orders-view .order-tracking summary::before{transition:none}}


/* Compact short orders naturally; long selections can extend the document. */
.market-orders-view #orderDetailMessage:empty{display:none}
@media(min-width:1001px) and (max-height:850px){
body.market-orders-view:has(#orderDetailPanel:not([hidden])) main.wrap{padding-top:12px;padding-bottom:12px}
.market-orders-view #orderDetailPanel>.payment-preview-heading{margin-bottom:14px;gap:16px}
.market-orders-view #orderDetailPanel .payment-preview-heading h1{font-size:34px}
.market-orders-view .order-ready-mark{width:48px;height:48px;flex-basis:48px}
.market-orders-view #orderDetail .order-selection,.market-orders-view #orderDetail .payment-preview-summary{padding:18px 20px}
.market-orders-view #orderDetail .payment-buyer{padding-bottom:12px;gap:12px}
.market-orders-view #orderDetail .payment-buyer-avatar{width:44px;height:44px;flex-basis:44px}
.market-orders-view #orderDetail .payment-buyer h2{font-size:18px}
.market-orders-view #orderDetail .payment-selection-heading{margin:14px 0 10px}
.market-orders-view #orderDetail .payment-selection-heading h2{font-size:18px}
.market-orders-view #orderDetail .order-concepts .payment-preview-line{padding:12px;gap:12px;margin-bottom:8px}
.market-orders-view #orderDetail .order-concepts .payment-preview-line::before{width:42px;height:42px}
.market-orders-view #orderDetail .order-dates{padding:8px 0;gap:6px}
.market-orders-view #orderDetail .order-dates p{font-size:11px;padding:6px 10px}
.market-orders-view #orderDetail .order-code-box{margin:10px 0;padding:5px 8px}
.market-orders-view #orderDetail .payment-preview-summary h2{margin:0 0 6px}
.market-orders-view #orderDetail .payment-total-block{margin-top:10px;padding:10px 0}
.market-orders-view #orderDetail .payment-preview-total{font-size:32px}
.market-orders-view #orderDetail .order-payment-options{margin-top:10px}
.market-orders-view #orderDetail .order-payment-options h3{font-size:16px;margin-bottom:8px}
.market-orders-view #orderDetail .order-payment-method{min-height:60px;padding:10px}
.market-orders-view #orderDetail .order-pay-button{min-height:44px;margin-top:10px}
.market-orders-view #orderDetail .payment-modify{min-height:30px;margin-top:6px}
.market-orders-view #orderDetail .payment-gateway-note{font-size:11px;margin-top:8px}
body.market-orders-view:has(#orderDetailPanel:not([hidden])) .orders-footer{margin-top:12px;padding-top:10px;font-size:11px}
.market-orders-view #orderDetail:not(:has(.order-concepts>.payment-preview-line:nth-child(3))) .order-tracking{margin-top:8px;padding-top:6px}
.market-orders-view #orderDetail:not(:has(.order-concepts>.payment-preview-line:nth-child(3))) .order-timeline{display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:14px;padding:10px 0 0}
.market-orders-view #orderDetail:not(:has(.order-concepts>.payment-preview-line:nth-child(3))) .order-timeline li{border-left:0;border-top:2px solid var(--od-line);padding:22px 0 0;min-height:0}
.market-orders-view #orderDetail:not(:has(.order-concepts>.payment-preview-line:nth-child(3))) .order-timeline li::before{left:0;top:-13px;width:24px;height:24px}
.market-orders-view #orderDetail:not(:has(.order-concepts>.payment-preview-line:nth-child(3))) .order-timeline strong{font-size:12px}
.market-orders-view #orderDetail:not(:has(.order-concepts>.payment-preview-line:nth-child(3))) .order-timeline time,.market-orders-view #orderDetail:not(:has(.order-concepts>.payment-preview-line:nth-child(3))) .order-timeline small{font-size:11px;line-height:1.4}
}

</style>
<div class="order-ready-glow" aria-hidden="true"></div><div class="order-ready-glow second" aria-hidden="true"></div>
<div class="payment-preview orders-workspace">
<nav class="payment-breadcrumb" aria-label="Ruta de compra"><a href="/tienda">Catálogo</a><span aria-hidden="true">/</span><a href="/tienda/carrito">Carrito</a><span aria-hidden="true">/</span><span aria-current="page">Mis pedidos</span></nav>
<section id="ordersListPanel" aria-labelledby="ordersTitle">
<header class="payment-preview-heading orders-heading"><div><h1 id="ordersTitle" tabindex="-1">Tus pedidos, en un solo lugar.</h1><p>Consulta tus conceptos, importes y el estado de cada pedido.</p></div><button type="button" class="payment-text-button" id="refreshOrders">↻ Actualizar estado</button></header>
<p id="ordersMessage" role="status" aria-live="polite"></p>
<div id="ordersList" class="orders-list"></div><button class="payment-text-button" id="moreOrders" type="button" hidden>Ver más pedidos</button>
</section>
<section id="orderDetailPanel" aria-labelledby="orderDialogTitle" hidden>
<header class="payment-preview-heading"><div class="order-ready-heading"><span class="order-ready-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg></span><div><h1 id="orderDialogTitle" tabindex="-1">Tu pedido está listo.</h1><p>Consulta el detalle y los siguientes pasos.</p></div></div><div class="order-progress-actions"><ol class="payment-progress" aria-label="Etapas de la compra"><li><span aria-hidden="true">✓</span>01 Carrito</li><li><span aria-hidden="true">✓</span>02 Revisión</li><li aria-current="step"><span>03</span>Pago</li></ol></div></header>
<p id="orderDetailMessage" role="status" aria-live="polite"></p><div id="orderDetail"></div>
</section>
<div class="payment-review-footer orders-footer"><span>Universidad Nacional de Cañete · Oficina de Tecnologías de la Información</span><span>Generar un pedido no realiza un cobro ni inicia un trámite académico.</span></div>
</div>
<script src="{{ asset('js/undc-orders.js') }}?v={{ filemtime(public_path('js/undc-orders.js')) }}" defer></script>
@endsection
