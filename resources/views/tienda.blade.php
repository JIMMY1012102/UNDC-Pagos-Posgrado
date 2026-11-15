@extends('layouts.market')
@section('marketPage', 'catalog')
@section('heroes')
@include('modules.tienda.partials.heroes')
@endsection
@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
body[data-market-page="catalog"]{--st-bg:#092f22;--st-card:#104534;--st-ink:#eafff3;--st-muted:#b5d4c3;--st-line:#386b52;--st-green:#66dfaa;background:var(--st-bg)}
body.light-mode[data-market-page="catalog"]{--st-bg:#f3f7f4;--st-card:#fff;--st-ink:#14231b;--st-muted:#3d4f45;--st-line:#dce6e0;--st-green:#0b5d3b;background:var(--st-bg)}
body[data-market-page="catalog"] .site-header{position:sticky;top:0;z-index:30;background:var(--st-card);border-bottom:1px solid var(--st-line);box-shadow:none}
body[data-market-page="catalog"] .site-header .top{height:auto;background:transparent;border:0}
body[data-market-page="catalog"] .top-inner{max-width:1240px;width:calc(100% - 48px);padding:14px 0;gap:16px;height:auto;min-height:76px;display:flex;flex-wrap:wrap}
body[data-market-page="catalog"] .brand{margin:0;gap:12px;flex:0 0 auto}
body[data-market-page="catalog"] .crest{width:48px;height:54px;object-fit:contain;display:block}
body[data-market-page="catalog"] .brand-title{font-size:24px;color:var(--st-green)}
body[data-market-page="catalog"] .brand-title em{color:#0a7f52}
body[data-market-page="catalog"] .top-inner>.header-search{position:relative;display:flex;flex:1 1 290px;max-width:none;min-width:0;inset:auto;margin:0;background:var(--st-bg);border:1.5px solid var(--st-line);border-radius:12px;height:48px;box-shadow:none}
body[data-market-page="catalog"] .header-search input{width:100%;min-width:0;background:transparent;color:var(--st-ink);font-size:14px}
body[data-market-page="catalog"] .nav-actions{gap:12px;margin:0}
body[data-market-page="catalog"] .login-button{border-radius:12px;background:#0b5d3b;color:#fff}
body[data-market-page="catalog"] #headerNavPanel,body[data-market-page="catalog"] .catalog-search-toggle{display:none}
body[data-market-page="catalog"] .social-rail{display:none}
body[data-market-page="catalog"].market-with-social-rail{padding-left:0}
body[data-market-page="catalog"] main.wrap{max-width:1240px;width:calc(100% - 48px);margin:auto;padding:32px 0 56px}
.store-layout{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:32px;align-items:start}
body[data-market-page="catalog"] .catalog-results-heading{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:0 0 20px}
body[data-market-page="catalog"] .catalog-results-heading h3{font-size:24px;color:var(--st-green)}
body[data-market-page="catalog"] #visibleConceptCount{font-size:13px;color:var(--st-muted);white-space:normal}
body[data-market-page="catalog"] .store-category-chips .nav-links{display:flex;flex-wrap:wrap;gap:8px;width:100%;height:auto;margin:0 0 22px;padding:0;overflow:visible}
body[data-market-page="catalog"] .store-category-chips a{display:inline-flex;align-items:center;min-height:44px;padding:8px 16px;border:1px solid var(--st-line);border-radius:999px;background:var(--st-card);color:var(--st-ink);font-size:13px;text-decoration:none;white-space:normal}
body[data-market-page="catalog"] .store-category-chips a.active{background:#0b5d3b;color:#fff;border-color:#0b5d3b}
body[data-market-page="catalog"] .store-category-chips a::after{display:none}
body[data-market-page="catalog"] .tusne-results{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}
body[data-market-page="catalog"] .category-result-group,body[data-market-page="catalog"] .category-result-group>.concept-grid{display:contents}
body[data-market-page="catalog"] .catalog-product{display:flex;flex-direction:column;min-width:0;overflow:hidden;border:1px solid var(--st-line);border-radius:16px;background:var(--st-card);padding:0;box-shadow:none}
body[data-market-page="catalog"] .catalog-product-link{display:flex;flex:1;flex-direction:column;width:100%;padding:0;background:transparent;text-align:left;border:0;border-radius:0}
body[data-market-page="catalog"] .catalog-product-image{width:100%;height:auto;aspect-ratio:2/1;display:block;border-radius:0;overflow:hidden;margin:0}
body[data-market-page="catalog"] .catalog-product-image img{display:block;width:100%;height:100%;object-fit:cover}
body[data-market-page="catalog"] .catalog-product-copy{display:flex;flex:1;flex-direction:column;gap:14px;padding:20px}
.store-card-category{align-self:flex-start;padding:4px 10px;background:#eaf4ee;color:#0b5d3b;border-radius:999px;font-size:12px}
body[data-market-page="catalog"] .catalog-product-name{font-size:16px;line-height:1.35;font-weight:600;min-height:65px;color:var(--st-ink)}
body[data-market-page="catalog"] .catalog-product-price{font-size:25px;font-weight:700;color:var(--st-green);margin-top:auto}
.store-card-add{min-height:48px;margin:0 20px 20px;padding:10px;border:1px solid #0b5d3b;border-radius:12px;background:#0b5d3b;color:#fff;font-size:14px;font-weight:600;cursor:pointer}
.store-card-add[aria-pressed="true"]{background:var(--st-card);color:var(--st-green);border-color:var(--st-green)}
.store-cart{position:sticky;top:110px;background:var(--st-card);border:1px solid var(--st-line);border-radius:16px;padding:24px;color:var(--st-ink)}
.store-cart-heading,.store-cart-total{display:flex;align-items:center;justify-content:space-between;gap:12px}
.store-cart h2{font-size:20px;margin:0;color:var(--st-green)}
#storeCartCount{border-radius:999px;background:#0b5d3b;color:white;min-width:28px;text-align:center;padding:4px 8px}
#storeCartEmpty{font-size:14px;color:var(--st-muted);line-height:1.5}
.store-cart-line{display:flex;justify-content:space-between;gap:12px;padding:14px 0;border-bottom:1px solid var(--st-line)}
.store-cart-line>div{min-width:0}.store-cart-line strong{display:block;font-size:13px;font-weight:500;line-height:1.4;overflow-wrap:anywhere}.store-cart-line span{display:block;margin-top:6px;color:var(--st-green);font-size:15px;font-weight:600}
.store-cart-line button{width:44px;height:44px;flex-shrink:0;border:0;border-radius:10px;background:var(--st-bg);color:var(--st-muted);font-size:24px;cursor:pointer}
.store-cart-total{padding:20px 0}.store-cart-total strong{font-size:26px}
#storeCartContinue{width:100%;min-height:52px;background:#0b5d3b;color:white;border:0;border-radius:12px;font-weight:600;cursor:pointer}
#storeCartContinue:disabled{background:#c9d6ce;color:#2f3f36;cursor:not-allowed}
/* Preserve the full hero photograph and its existing internal content. */
body[data-market-page="catalog"] .market-hero{min-height:0;height:auto;background:#0b5d3b}
body[data-market-page="catalog"] .market-hero-photo{position:relative;inset:auto;width:100%;height:clamp(300px,42vw,560px);object-fit:contain;object-position:center;aspect-ratio:auto}
body[data-market-page="catalog"] .market-hero-content{position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:32px clamp(24px,5vw,80px) 70px}
body[data-market-page="catalog"] .market-hero-shade{pointer-events:none}
@media(max-width:1100px){body[data-market-page="catalog"] .tusne-results{grid-template-columns:repeat(2,minmax(0,1fr))}.store-layout{grid-template-columns:minmax(0,1fr) 280px;gap:22px}}
@media(max-width:800px){.store-layout{grid-template-columns:minmax(0,1fr)}.store-cart{position:static}.store-cart{order:2}body[data-market-page="catalog"] .top-inner{width:calc(100% - 24px)}body[data-market-page="catalog"] main.wrap{width:calc(100% - 24px);padding-top:24px}body[data-market-page="catalog"] .top-inner>.header-search{flex-basis:100%;order:5}body[data-market-page="catalog"] .market-hero-photo{height:auto;min-height:0}body[data-market-page="catalog"] .market-hero-content{position:relative;inset:auto;padding:24px 20px 80px}body[data-market-page="catalog"] .market-hero-shade{display:none}}
@media(max-width:480px){body[data-market-page="catalog"] .tusne-results{grid-template-columns:minmax(0,1fr)}body[data-market-page="catalog"] .catalog-product-name{min-height:0}}

</style>
<div class="store-layout">
@include('modules.tienda.partials.catalog')
<aside class="store-cart" aria-labelledby="storeCartTitle">
<div class="store-cart-heading"><h2 id="storeCartTitle">Tu carrito</h2><span id="storeCartCount" aria-live="polite">0</span></div>
<p id="storeCartEmpty">Aún no agregaste conceptos. Elige uno de la lista para ver el total aquí.</p>
<div id="storeCartLines"></div>
<div class="store-cart-total"><span>Total</span><strong id="storeCartTotal" aria-live="polite">S/ 0.00</strong></div>
<button id="storeCartContinue" type="button" disabled>Revisar carrito →</button>
</aside>
</div>
@endsection
