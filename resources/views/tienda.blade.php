@extends('layouts.market')
@section('marketPage', 'catalog')
@section('heroes')
@include('modules.tienda.partials.heroes')
@endsection
@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Source+Serif+4:wght@600;700&display=swap" rel="stylesheet">
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

/* Institutional palette and typography, confined to the storefront. */
body[data-market-page="catalog"]{font-family:Inter,system-ui,sans-serif}
body.light-mode[data-market-page="catalog"]{--st-bg:#f6f5f0;--st-card:#fff;--st-ink:#17211b;--st-muted:#4a564e;--st-line:#e2e0d6;--st-green:#0a4a31}
body[data-market-page="catalog"] .top-inner{padding:12px 0;gap:12px 24px}
body[data-market-page="catalog"] .crest{width:46px;height:46px}
body[data-market-page="catalog"] .brand-title,body[data-market-page="catalog"] .brand-title b,body[data-market-page="catalog"] .brand-title em{font:700 22px 'Source Serif 4',Georgia,serif;letter-spacing:-.01em}
body[data-market-page="catalog"] .brand-title em{color:#8a5f14}
body[data-market-page="catalog"] .top-inner>.header-search{height:44px;border-radius:10px;border-color:#d3d8d0}
body.light-mode[data-market-page="catalog"] .top-inner>.header-search{background:#fafaf7}
body[data-market-page="catalog"] .header-search:focus-within{border-color:#0a5c3b;box-shadow:0 0 0 3px rgb(10 92 59 / 15%)}
body[data-market-page="catalog"] .login-button{min-height:44px;border:1px solid var(--st-line);border-radius:10px;background:var(--st-card);color:var(--st-ink);font:500 14px Inter,system-ui,sans-serif}
.store-account-initials{display:grid;place-items:center;width:28px;height:28px;flex-shrink:0;border-radius:50%;background:#0a4a31;color:white;font-size:12px;font-weight:600}
body[data-market-page="catalog"] .utility-control{border:1px solid var(--st-line);border-radius:10px;min-height:44px}
body[data-market-page="catalog"] main.wrap{padding-top:24px}
body[data-market-page="catalog"] .catalog-results-heading{align-items:baseline;flex-wrap:wrap;margin-bottom:14px}
body[data-market-page="catalog"] .catalog-results-heading h3{font:700 26px 'Source Serif 4',Georgia,serif}
body[data-market-page="catalog"] .store-category-chips{min-width:0}
body[data-market-page="catalog"] .store-category-chips .nav-links{flex-wrap:nowrap;overflow-x:auto;gap:8px;padding:4px;margin:0 0 14px;width:100%}
body[data-market-page="catalog"] .store-category-chips a{flex:0 0 auto;white-space:nowrap;gap:8px;min-height:40px;padding:0 16px;border-color:#d3d8d0;font:500 14px Inter,system-ui,sans-serif;transition:background .15s,color .15s}
body[data-market-page="catalog"] .store-category-chips a.active{background:#0a4a31;border-color:#0a4a31}
.store-chip-count{opacity:.75;font-size:12px}
body[data-market-page="catalog"] .tusne-results{grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
body[data-market-page="catalog"] .catalog-product{padding:10px;border-radius:14px;gap:10px;transition:box-shadow .2s,transform .2s,border-color .2s}
body[data-market-page="catalog"] .catalog-product.is-in-cart{border-color:var(--st-green);box-shadow:0 0 0 1px var(--st-green)}
body[data-market-page="catalog"] .catalog-product-link{gap:10px}
body[data-market-page="catalog"] .catalog-product-image{aspect-ratio:5/2;border-radius:9px}
body[data-market-page="catalog"] .catalog-product-copy{gap:6px;padding:0 6px}
body[data-market-page="catalog"] .store-card-category{font:600 11px Inter,system-ui,sans-serif;letter-spacing:.08em;text-transform:uppercase;color:#8a5f14;background:transparent;border-radius:0;padding:0}
body[data-market-page="catalog"] .catalog-product-name{font:600 15px/1.4 Inter,system-ui,sans-serif;min-height:63px}
.store-card-bottom{padding:10px 6px 4px;border-top:1px solid var(--st-line);display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:auto}
body[data-market-page="catalog"] .catalog-product-price{display:flex;align-items:baseline;gap:4px;font:700 22px Inter,system-ui,sans-serif;letter-spacing:-.01em;font-variant-numeric:tabular-nums;margin:0}
body[data-market-page="catalog"] .catalog-product-price small{font:500 14px Inter,system-ui,sans-serif;color:var(--st-muted)}
body[data-market-page="catalog"] .store-card-add{margin:0;min-height:44px;flex-shrink:0;padding:0 16px;width:auto;border-radius:10px;background:#0a4a31;border-color:#0a4a31;font:600 14px Inter,system-ui,sans-serif}
body[data-market-page="catalog"] .store-card-add[aria-pressed="true"]{background:var(--st-card);color:var(--st-green);border-color:var(--st-green)}
.store-show-all{display:flex;justify-content:center;margin-top:22px}
#storeShowAll{min-height:46px;padding:0 24px;border:1.5px solid var(--st-green);border-radius:10px;background:transparent;color:var(--st-green);font-weight:600;cursor:pointer}
body[data-market-page="catalog"] .store-cart{padding:22px;border-radius:14px;display:flex;flex-direction:column;gap:14px}
body[data-market-page="catalog"] .store-cart h2{font:700 20px 'Source Serif 4',Georgia,serif}
body[data-market-page="catalog"] #storeCartCount{min-width:26px;height:26px;padding:0 8px;line-height:26px;background:#0a4a31;font-size:13px}
body[data-market-page="catalog"] #storeCartEmpty{margin:0}
body[data-market-page="catalog"] .store-cart-total{padding:4px 0 0}
body[data-market-page="catalog"] .store-cart-total strong{font:700 26px Inter,system-ui,sans-serif;font-variant-numeric:tabular-nums}
body[data-market-page="catalog"] .store-cart-line{padding:0 0 12px;margin-bottom:12px}
body[data-market-page="catalog"] .store-cart-line span{font-size:14px;font-variant-numeric:tabular-nums}
body[data-market-page="catalog"] #storeCartContinue{min-height:48px;border-radius:10px;background:#0a4a31}
body[data-market-page="catalog"] #storeCartContinue:disabled{background:#d9ddd6;color:#3b473f}
/* Hero retains full photography and existing carousel composition. */
body[data-market-page="catalog"] .market-hero{background:#0a4a31}
body[data-market-page="catalog"] .market-hero-eyebrow{display:flex;align-items:center;gap:10px;border:0!important;padding:0!important;margin-bottom:10px;color:#e6c36f!important;font:600 12px Inter,system-ui,sans-serif;letter-spacing:.12em}
body[data-market-page="catalog"] .market-hero-eyebrow::before{content:"";width:28px;height:2px;background:#e6c36f}
body[data-market-page="catalog"] .market-hero [data-hero-slide] h1,body[data-market-page="catalog"] .market-hero [data-hero-slide] h2{font-family:'Source Serif 4',Georgia,serif;font-size:clamp(30px,3vw,42px);line-height:1.12}
body[data-market-page="catalog"] .market-hero [data-hero-slide] p{font:400 15px/1.5 Inter,system-ui,sans-serif;color:#d6e8de}
body[data-market-page="catalog"] .market-hero-cta{min-height:44px;padding:0 20px;border-radius:10px;background:#e6c36f;color:#1b2a1f;border-color:#e6c36f;font:600 15px Inter,system-ui,sans-serif}
body[data-market-page="catalog"]>.footer{display:block;background:#0a4a31;color:#d6e8de;border:0;padding:0;margin:0}
.store-footer-inner{max-width:1240px;width:calc(100% - 48px);margin:auto;padding:20px 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;font:500 14px Inter,system-ui,sans-serif}
.store-footer-social{position:static;display:block;width:auto;inset:auto;padding:0;background:transparent;border:0}
.store-footer-social .market-social-links{display:flex;flex-direction:row;gap:8px}
.store-footer-social a{width:44px;height:44px;display:grid;place-items:center;border:1px solid rgb(255 255 255 / 28%);border-radius:10px;background:transparent;color:#fff}
.store-footer-social svg{width:20px;height:20px}
body[data-market-page="catalog"] :is(button,a,input):focus-visible{outline:3px solid #b8832a;outline-offset:2px}
@media(hover:hover){body[data-market-page="catalog"] .catalog-product:hover{box-shadow:0 10px 28px rgb(10 60 40 / 14%);transform:translateY(-2px);border-color:#c9d6cb}}
@media(max-width:1150px){body[data-market-page="catalog"] .tusne-results{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:800px){.store-footer-inner{width:calc(100% - 24px)}body[data-market-page="catalog"] .store-cart{position:static}body[data-market-page="catalog"] .top-inner>.header-search{order:5;flex-basis:100%}}
@media(max-width:520px){body[data-market-page="catalog"] .tusne-results{grid-template-columns:minmax(0,1fr)}}
@media(prefers-reduced-motion:reduce){body[data-market-page="catalog"] .catalog-product,body[data-market-page="catalog"] .store-category-chips a{transition:none}body[data-market-page="catalog"] .catalog-product:hover{transform:none}}


/* Compact desktop banner, matching the reference height without cropping the photograph. */
@media(min-width:801px){
 body[data-market-page="catalog"] .hero.market-hero[data-market-carousel]{min-height:205px;height:auto;position:relative;isolation:isolate}
 body[data-market-page="catalog"] .market-hero-photo{position:absolute;inset:0 0 auto auto;width:50%;height:205px;object-fit:contain;object-position:right center;z-index:-2}
 body[data-market-page="catalog"] .market-hero-shade{display:block;z-index:-1;background:linear-gradient(90deg,#0a4a31 0%,#0a4a31 49%,rgb(10 74 49 / 70%) 59%,transparent 78%)}
 body[data-market-page="catalog"] .market-hero-content{position:relative;inset:auto;width:62%;padding:16px clamp(24px,6.4vw,88px);box-sizing:border-box}
 body[data-market-page="catalog"] .market-hero-eyebrow{margin:0 0 6px}
 body[data-market-page="catalog"] .market-hero [data-hero-slide] h1,body[data-market-page="catalog"] .market-hero [data-hero-slide] h2{font-size:calc(28px * var(--a11y-text-scale,1));line-height:1.08;margin:0 0 6px}
 body[data-market-page="catalog"] .market-hero [data-hero-slide] p{font-size:calc(14px * var(--a11y-text-scale,1));line-height:1.4;margin:0 0 10px}
 body[data-market-page="catalog"] .market-hero-cta{min-height:44px;padding:0 18px}
}


/* Institutional reference header: brand, search, language, account. */
body[data-market-page="catalog"] .site-header .top-inner{display:flex;align-items:center;flex-wrap:nowrap;max-width:1192px;width:calc(100% - 48px);gap:28px;padding:12px 0;min-height:70px}
body[data-market-page="catalog"] .site-header .brand{flex:0 0 auto;gap:12px;margin:0}
body[data-market-page="catalog"] .site-header .brand .crest{display:block;width:46px;height:46px;object-fit:contain}
body[data-market-page="catalog"] .site-header .brand-title{white-space:nowrap}
body[data-market-page="catalog"] .site-header .payment-header-context,
body[data-market-page="catalog"] .site-header .payment-orders-link,
body[data-market-page="catalog"] .site-header .nav-actions>.cart-button,
body[data-market-page="catalog"] .site-header .action-divider{display:none}
body[data-market-page="catalog"] .site-header .top-inner>.header-search{flex:1 1 320px;order:0;min-width:120px;max-width:none;margin:0;height:44px;padding:0 12px;display:flex;align-items:center;gap:10px}
body[data-market-page="catalog"] .site-header .header-search .search-icon{position:static;width:18px;height:18px;flex-shrink:0}
body[data-market-page="catalog"] .site-header .header-search input{padding:0;height:100%;font:400 15px Inter,system-ui,sans-serif}
body[data-market-page="catalog"] .site-header .header-search>button[type="submit"]{display:none}
body[data-market-page="catalog"] .site-header .utility-controls{display:flex;flex:0 0 auto;margin:0}
body[data-market-page="catalog"] .site-header .utility-controls .language-button{height:44px;min-width:44px;padding:0 12px;background:var(--st-card);color:var(--st-ink);border:1px solid var(--st-line);border-radius:10px;gap:0}
body[data-market-page="catalog"] .site-header .utility-controls .language-flag,
body[data-market-page="catalog"] .site-header .utility-controls .chevron{display:none}
body[data-market-page="catalog"] .site-header .utility-controls .language-label{font-size:0}
body[data-market-page="catalog"] .site-header .utility-controls .language-label::after{content:"ES";font:500 14px Inter,system-ui,sans-serif}
html[lang="en"] body[data-market-page="catalog"] .site-header .utility-controls .language-label::after{content:"EN"}
body[data-market-page="catalog"] .site-header .nav-actions{display:flex;flex:0 0 auto;margin:0 0 0 -18px;gap:10px}
body[data-market-page="catalog"] .site-header .nav-actions .login-button{height:44px;min-height:44px;padding:0 16px 0 12px;gap:10px;border:1px solid var(--st-line);border-radius:10px;background:var(--st-card);color:var(--st-ink);font:500 14px Inter,system-ui,sans-serif}
body[data-market-page="catalog"] .site-header .account-name{max-width:170px;font:500 14px Inter,system-ui,sans-serif}
body[data-market-page="catalog"] .site-header .mobile-primary-actions,
body[data-market-page="catalog"] .site-header .menu-toggle{display:none}
@media(max-width:1050px){body[data-market-page="catalog"] .site-header .top-inner{gap:14px;flex-wrap:wrap}body[data-market-page="catalog"] .site-header .top-inner>.header-search{order:5;flex-basis:100%}body[data-market-page="catalog"] .site-header .utility-controls{margin-left:auto}body[data-market-page="catalog"] .site-header .nav-actions{margin-left:0}}
@media(max-width:600px){body[data-market-page="catalog"] .site-header .top-inner{width:calc(100% - 24px);gap:10px}body[data-market-page="catalog"] .site-header .brand-title,body[data-market-page="catalog"] .site-header .brand-title b,body[data-market-page="catalog"] .site-header .brand-title em{font-size:20px}body[data-market-page="catalog"] .site-header .brand .crest{width:38px;height:42px}body[data-market-page="catalog"] .site-header .account-name{display:none}body[data-market-page="catalog"] .site-header .nav-actions .login-button{padding:0 8px;min-width:44px}body[data-market-page="catalog"] .site-header .nav-actions .account-chevron{display:none}}


/* Match the supplied screenshot rather than inherited carousel geometry. */
@media(min-width:1051px){
body[data-market-page="catalog"] .site-header .top-inner{display:grid;grid-template-columns:max-content minmax(0,1fr) 44px max-content;max-width:1236px;gap:28px}
body[data-market-page="catalog"] .site-header .brand{min-width:0;width:max-content;max-width:none}
body[data-market-page="catalog"] .site-header .brand-copy{min-width:0;width:auto}
body[data-market-page="catalog"] .site-header .top-inner>.header-search{grid-column:2;grid-row:1;width:100%;min-width:0;box-sizing:border-box}
body[data-market-page="catalog"] .site-header .utility-controls{grid-column:3;grid-row:1;margin:0}
body[data-market-page="catalog"] .site-header .nav-actions{grid-column:4;grid-row:1;margin:0}
}
body[data-market-page="catalog"] .hero.market-hero.institutional-hero{display:block;position:relative;isolation:isolate;min-height:0;height:auto;margin:0;padding:0;background:#0a4a31;overflow:hidden;border-radius:0}
body[data-market-page="catalog"] .institutional-hero .market-hero-photo{position:absolute;inset:0 0 0 auto;width:48%;height:100%;min-height:0;max-height:none;object-fit:cover;object-position:center 88%;z-index:-1;border-radius:0;mask-image:linear-gradient(to right,transparent,#000 40%);-webkit-mask-image:linear-gradient(to right,transparent,#000 40%)}
body[data-market-page="catalog"] .institutional-hero .market-hero-content{position:relative;inset:auto;display:block;width:100%;max-width:1240px;margin:0 auto;padding:26px 24px;box-sizing:border-box;transform:none}
body[data-market-page="catalog"] .institutional-hero .market-hero-slides{max-width:560px;min-height:0;height:auto}
body[data-market-page="catalog"] .institutional-hero .market-hero-eyebrow{margin:0 0 10px;font-size:12px}
body[data-market-page="catalog"] .institutional-hero [data-hero-slide] h1{font:700 34px/1.12 'Source Serif 4',Georgia,serif;letter-spacing:-.01em;margin:0;color:#fff}
body[data-market-page="catalog"] .institutional-hero [data-hero-slide] p{font:400 15px/1.5 Inter,system-ui,sans-serif;margin:8px 0 0;color:#d6e8de}
body[data-market-page="catalog"] .institutional-hero .market-hero-cta{display:inline-flex;align-items:center;gap:10px;margin-top:16px;min-height:44px;padding:0 20px;font-size:15px;box-shadow:none}
body[data-market-page="catalog"] main.wrap{max-width:1192px;padding-top:24px}
body[data-market-page="catalog"] #visibleConceptCount{font:500 14px Inter,system-ui,sans-serif;color:var(--st-muted);background:transparent;border:0;padding:0;border-radius:0}
body[data-market-page="catalog"] .catalog-results-heading{margin-bottom:18px}
body[data-market-page="catalog"] .store-cart{gap:14px}
body[data-market-page="catalog"] .store-cart-total{padding-top:4px}
body[data-market-page="catalog"] #storeCartContinue{font-size:15px;opacity:1}
@media(max-width:1050px){body[data-market-page="catalog"] .site-header .brand{min-width:0;width:auto}body[data-market-page="catalog"] .institutional-hero .market-hero-slides{max-width:56%}}
@media(max-width:760px){body[data-market-page="catalog"] .institutional-hero .market-hero-photo{display:none}body[data-market-page="catalog"] .institutional-hero .market-hero-slides{max-width:100%}body[data-market-page="catalog"] .institutional-hero .market-hero-content{padding:26px 20px}body[data-market-page="catalog"] .institutional-hero [data-hero-slide] h1{font-size:30px}}


/* Enlarge only the right photo inside the existing hero dimensions. */
@media(min-width:761px){
body[data-market-page="catalog"] .institutional-hero .market-hero-photo{width:58%;object-position:center 88%}
}

body.light-mode[data-market-page="catalog"]{--st-bg:#e8f1ea;--st-line:#d3e2d8}
body.light-mode[data-market-page="catalog"] .site-header{background:#f4f9f5;border-color:#cfe0d5}
body[data-market-page="catalog"] .site-header .top-inner{display:flex;justify-content:space-between;flex-wrap:nowrap;gap:24px}
body[data-market-page="catalog"] .site-header .utility-controls{margin-left:auto}
body[data-market-page="catalog"] .site-header .nav-actions{margin:0;gap:10px}
body[data-market-page="catalog"] .site-header .nav-actions>.cart-button{display:flex;align-items:center;justify-content:center;gap:8px;width:auto;height:44px;padding:0 14px;border:1px solid #c3d5c9;border-radius:10px;background:var(--st-card);color:var(--st-ink)}
body[data-market-page="catalog"] .site-header .cart-button svg{width:18px;height:18px}
body[data-market-page="catalog"] .site-header #cartBadge{position:static;min-width:20px;height:20px;border:0;line-height:20px;padding:0 6px;border-radius:999px;background:#0a4a31;color:#fff;font-size:12px}
body[data-market-page="catalog"] .institutional-hero .market-hero-photo{width:58%;object-position:center 80%;mask-image:linear-gradient(to right,transparent,#000 45%);-webkit-mask-image:linear-gradient(to right,transparent,#000 45%)}
body[data-market-page="catalog"] .institutional-hero .market-hero-content{padding:56px 24px 112px}
body[data-market-page="catalog"] .institutional-hero .market-hero-slides{max-width:640px}
body[data-market-page="catalog"] .institutional-hero [data-hero-slide] h1{font-size:50px;line-height:1.08;letter-spacing:-.015em;margin-top:14px;text-wrap:balance}
body[data-market-page="catalog"] .institutional-hero [data-hero-slide] p{font-size:17px;line-height:1.5;margin-top:14px;max-width:52ch}
#storeHeroSearch{max-width:600px;margin-top:26px}
body[data-market-page="catalog"] .institutional-hero .header-search{position:relative;inset:auto;display:flex;flex-wrap:wrap;align-items:center;width:100%;height:auto;gap:10px;padding:0;margin:0;max-width:none;background:transparent;border:0;box-shadow:none}
body[data-market-page="catalog"] .institutional-hero .header-search input{flex:1 1 280px;width:0;min-width:0;height:56px;box-sizing:border-box;padding:0 16px 0 46px;border:0;border-radius:12px;background:#fff;color:#17211b;font:400 15px Inter,system-ui,sans-serif}
body[data-market-page="catalog"] .institutional-hero .header-search .search-icon{position:absolute;left:16px;top:18px;width:20px;height:20px;color:#4a5c52}
body[data-market-page="catalog"] .institutional-hero .header-search>button[type="submit"]{display:flex;align-items:center;justify-content:center;flex:0 0 auto;width:auto;height:56px;padding:0 26px;border:0;border-radius:12px;background:#e6c36f;color:#1b2a1f;font:600 16px Inter,system-ui,sans-serif}
body[data-market-page="catalog"] .institutional-hero .header-search>button[type="submit"]::after{content:"Buscar"}
body[data-market-page="catalog"] .institutional-hero .header-search>button[type="submit"] svg{display:none}
html[lang="en"] body[data-market-page="catalog"] .institutional-hero .header-search>button[type="submit"]::after{content:"Search"}
.store-quick-links{margin-top:16px;display:flex;flex-wrap:wrap;align-items:center;gap:8px 18px;font:400 14px Inter,system-ui,sans-serif;color:#c7ddd0}
.store-quick-links a{color:#fff;text-decoration:underline;text-underline-offset:3px;text-decoration-color:rgb(255 255 255 / 45%)}
.store-quick-links a:hover{color:#e6c36f}
.store-tiles-wrap{max-width:1240px;width:100%;box-sizing:border-box;margin:-64px auto 0;padding:0 24px;position:relative;z-index:2}
.store-category-tiles{background:var(--st-card);border:1px solid var(--st-line);border-radius:18px;padding:12px;box-shadow:0 14px 36px rgb(10 60 40 / 16%)}
body[data-market-page="catalog"] .store-category-tiles .nav-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(176px,100%),1fr));gap:10px;margin:0;padding:0;width:100%;height:auto;overflow:visible}
body[data-market-page="catalog"] .store-category-tiles a{display:grid;grid-template-columns:40px minmax(0,1fr);gap:2px 12px;align-items:center;min-height:72px;box-sizing:border-box;padding:12px;border-radius:12px;border:1px solid var(--st-line);background:var(--st-bg);color:var(--st-ink);white-space:normal;text-align:left;text-decoration:none;transition:background .15s,box-shadow .15s}
.store-category-tiles a>span:not(.store-tile-icon):not(.store-chip-count){font:600 14px/1.25 Inter,system-ui,sans-serif}
.store-category-tiles .store-chip-count{grid-column:2;font:400 12px Inter,system-ui,sans-serif}
.store-tile-icon{grid-row:1 / span 2;width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:#e9f1ea;color:#0a4a31}
.store-tile-icon svg{width:22px;height:22px}
body[data-market-page="catalog"] .store-category-tiles a.active{background:#0a4a31;color:#fff;border-color:#0a4a31}
body[data-market-page="catalog"] .store-category-tiles a.active .store-tile-icon{background:rgb(255 255 255 / 16%);color:#e6c36f}
body[data-market-page="catalog"] .store-category-tiles a::after{display:none}
body[data-market-page="catalog"] .store-category-tiles a:hover{box-shadow:0 6px 18px rgb(10 60 40 / 12%)}
body[data-market-page="catalog"] .store-category-chips{display:none}
body[data-market-page="catalog"] main.wrap{padding-top:32px}
body[data-market-page="catalog"] .catalog-product-name{display:block;text-transform:lowercase}
body[data-market-page="catalog"] .catalog-product-name::first-letter{text-transform:uppercase}
@media(max-width:760px){body[data-market-page="catalog"] .institutional-hero .market-hero-content{padding:36px 20px 100px}body[data-market-page="catalog"] .institutional-hero [data-hero-slide] h1{font-size:36px}body[data-market-page="catalog"] .institutional-hero [data-hero-slide] p{font-size:16px}.store-tiles-wrap{padding:0 12px}.store-category-tiles{padding:10px}body[data-market-page="catalog"] .site-header .top-inner{flex-wrap:wrap;gap:10px}}
@media(max-width:400px){body[data-market-page="catalog"] .institutional-hero .header-search input{flex-basis:100%}body[data-market-page="catalog"] .institutional-hero .header-search>button[type="submit"]{width:100%}body[data-market-page="catalog"] .store-category-tiles .nav-links{grid-template-columns:minmax(0,1fr)}}
@media(prefers-reduced-motion:reduce){body[data-market-page="catalog"] .store-category-tiles a{transition:none}}


/* Slightly zoom the photo within the fixed hero, retaining its fade and layout. */
@media(min-width:761px){
body[data-market-page="catalog"] .institutional-hero .market-hero-photo{width:64%;object-position:center 80%}
}


/* Anchor the fade to its previous viewport position while retaining the 64% photo. */
@media(min-width:761px){
body[data-market-page="catalog"] .institutional-hero .market-hero-photo{
mask-image:linear-gradient(to right,transparent 9.375%,#000 50.15625%);
-webkit-mask-image:linear-gradient(to right,transparent 9.375%,#000 50.15625%);
}
}


/* Expand photo to 72%, keeping fade at 42%–68.1% of the hero. */
@media(min-width:761px){
body[data-market-page="catalog"] .institutional-hero .market-hero-photo{
width:72%;
mask-image:linear-gradient(to right,transparent 19.444444%,#000 55.694444%);
-webkit-mask-image:linear-gradient(to right,transparent 19.444444%,#000 55.694444%);
}
}


/* Larger hero and category dropdown from the new reference. */
body[data-market-page="catalog"] .hero.market-hero.institutional-hero{display:flex;flex-direction:column;justify-content:center;min-height:540px}
body[data-market-page="catalog"] .institutional-hero .market-hero-content{padding:56px 24px}
body[data-market-page="catalog"] .institutional-hero .market-hero-slides{max-width:700px}
body[data-market-page="catalog"] .institutional-hero [data-hero-slide] h1{font-size:56px;line-height:1.06}
body[data-market-page="catalog"] .site-header .top-inner{gap:24px}
.store-posgrado-menu{position:relative;flex:1 1 auto;margin-left:8px;color:var(--st-ink)}
.store-posgrado-menu>summary{display:flex;align-items:center;gap:10px;width:fit-content;min-height:44px;padding:0 14px;border-radius:10px;cursor:pointer;list-style:none;font:600 15px Inter,system-ui,sans-serif}
.store-posgrado-menu>summary::-webkit-details-marker{display:none}
.store-posgrado-menu>summary::after{content:"⌄";transition:rotate .2s}
.store-posgrado-menu[open]>summary::after{rotate:180deg}
.store-posgrado-menu[open]>summary{background:var(--st-bg)}
#storeHeaderCategories{position:absolute;top:calc(100% + 10px);left:0;z-index:50;width:380px;max-width:calc(100vw - 32px);box-sizing:border-box;background:var(--st-card);border:1px solid var(--st-line);border-radius:16px;box-shadow:0 20px 44px rgb(10 60 40 / 20%);padding:8px}
.store-menu-title{padding:8px 12px 6px;margin:0;font:600 11px Inter,system-ui,sans-serif;letter-spacing:.1em;text-transform:uppercase;color:var(--st-muted)}
body[data-market-page="catalog"] #storeHeaderCategories .nav-links{display:flex;flex-direction:column;align-items:stretch;gap:2px;height:auto;width:100%;margin:0;padding:0;overflow:visible}
body[data-market-page="catalog"] #storeHeaderCategories a{display:grid;grid-template-columns:38px minmax(0,1fr);gap:1px 12px;align-items:center;padding:10px 12px;border:0;border-radius:10px;text-decoration:none;background:transparent;color:var(--st-ink);white-space:normal;min-height:58px}
body[data-market-page="catalog"] #storeHeaderCategories a.active{background:var(--st-bg)}
#storeHeaderCategories a>span:not(.store-tile-icon):not(.store-chip-count){font:600 15px/1.3 Inter,system-ui,sans-serif}
#storeHeaderCategories .store-tile-icon{width:38px;height:38px;border-radius:10px;grid-row:1 / span 2}
#storeHeaderCategories .store-chip-count{grid-column:2;font:400 12px Inter,system-ui,sans-serif;color:var(--st-muted)}
body[data-market-page="catalog"] #storeHeaderCategories a.active .store-tile-icon{background:#0a4a31;color:#e6c36f}
body[data-market-page="catalog"] #storeHeaderCategories a::after{display:none}
body[data-market-page="catalog"] .site-header .utility-controls{margin-left:0}
@media(max-width:800px){body[data-market-page="catalog"] .hero.market-hero.institutional-hero{min-height:480px}body[data-market-page="catalog"] .institutional-hero .market-hero-content{padding:40px 20px}body[data-market-page="catalog"] .institutional-hero [data-hero-slide] h1{font-size:40px}.store-posgrado-menu{order:5;flex-basis:100%;margin-left:0}#storeHeaderCategories{width:min(380px,calc(100vw - 32px))}}
@media(prefers-reduced-motion:reduce){.store-posgrado-menu>summary::after{transition:none}}


/* Plain vertical dropdown matching the supplied second image. */
body[data-market-page="catalog"] #storeHeaderCategories{width:250px;padding:24px 0;border-radius:0;box-shadow:none;border:1px solid var(--st-line);top:calc(100% + 12px);max-height:calc(100dvh - 120px);overflow-y:auto}
body[data-market-page="catalog"] #storeHeaderCategories .store-menu-title,
body[data-market-page="catalog"] #storeHeaderCategories .store-tile-icon,
body[data-market-page="catalog"] #storeHeaderCategories .store-chip-count{display:none}
body[data-market-page="catalog"] #storeHeaderCategories .nav-links{gap:0}
body[data-market-page="catalog"] #storeHeaderCategories a{display:flex;align-items:center;justify-content:flex-start;min-height:44px;padding:12px 24px;border-radius:0;gap:0;text-align:left;background:transparent}
body[data-market-page="catalog"] #storeHeaderCategories a>span:not(.store-tile-icon):not(.store-chip-count){font:400 11px/1.5 Inter,system-ui,sans-serif;letter-spacing:.04em;text-transform:uppercase;text-align:left}
body[data-market-page="catalog"] #storeHeaderCategories a.active{background:transparent;color:var(--st-green)}
body[data-market-page="catalog"] #storeHeaderCategories a:hover{background:var(--st-bg)}


/* Wider photograph; fade remains anchored at 42% and 68.1% of the hero. */
@media(min-width:761px){
body[data-market-page="catalog"] .institutional-hero .market-hero-photo{
width:80%;
mask-image:linear-gradient(to right,transparent 27.5%,#000 60.125%);
-webkit-mask-image:linear-gradient(to right,transparent 27.5%,#000 60.125%);
}
}


/* Immersive header and hero fill the first viewport together. */
body[data-market-page="catalog"] .site-header{background:linear-gradient(110deg,#002d1d,#053f35);border:0;color:#fff}
body[data-market-page="catalog"] .site-header .top-inner{max-width:none;width:calc(100% - 80px);min-height:80px;padding:12px 0;gap:24px}
body[data-market-page="catalog"] .site-header .brand{gap:18px}
body[data-market-page="catalog"] .site-header .brand .crest{width:48px;height:54px}
body[data-market-page="catalog"] .site-header .brand-title,body[data-market-page="catalog"] .site-header .brand-title b,body[data-market-page="catalog"] .site-header .brand-title em{font:800 clamp(21px,2.5vw,38px) Inter,system-ui,sans-serif;color:#fff;letter-spacing:-.025em;text-transform:uppercase}
body[data-market-page="catalog"] .store-posgrado-menu{color:#fff;margin-left:20px}
body[data-market-page="catalog"] .store-posgrado-menu>summary{font-size:18px;color:#fff;padding:10px 12px}
body[data-market-page="catalog"] .store-posgrado-menu[open]>summary{background:rgb(255 255 255 / 10%)}
body[data-market-page="catalog"] .site-header .utility-controls .language-button{background:transparent;color:#fff;border-color:rgb(255 255 255 / 30%)}
body[data-market-page="catalog"] .site-header .nav-actions>.cart-button{background:#fff;color:#061b11;border:0;border-radius:20px;padding:0 18px;min-height:48px}
body[data-market-page="catalog"] .site-header .nav-actions>.cart-button::after{content:"Mi carrito";font:700 15px Inter,system-ui,sans-serif}
body[data-market-page="catalog"] .site-header .nav-actions .login-button{background:transparent;color:#fff;border:0}
body[data-market-page="catalog"] .site-header .account-name{display:none}
body[data-market-page="catalog"] .site-header .store-account-initials{width:44px;height:44px;background:transparent;border:2px solid #bafa46;color:#fff;font-size:16px}
body[data-market-page="catalog"] .hero.market-hero.immersive-hero{height:calc(100svh - var(--market-header-height,80px));min-height:0;display:flex;justify-content:center;background:#003b25}
body[data-market-page="catalog"] .immersive-hero .market-hero-photo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:right 68%;z-index:0;mask-image:linear-gradient(to right,transparent 32%,#000 68%);-webkit-mask-image:linear-gradient(to right,transparent 32%,#000 68%)}
body[data-market-page="catalog"] .immersive-hero .market-hero-content{position:relative;z-index:1;display:flex;flex-direction:column;justify-content:center;width:100%;max-width:none;height:100%;box-sizing:border-box;padding:clamp(18px,4vh,48px) 4%;inset:auto;margin:0}
body[data-market-page="catalog"] .immersive-hero .market-hero-eyebrow{color:#f5fff7!important;font:700 clamp(13px,1.5vw,21px) Inter,system-ui,sans-serif;margin:0 0 clamp(14px,2.5vh,28px);letter-spacing:.02em}
body[data-market-page="catalog"] .immersive-hero .market-hero-eyebrow::before{background:#c5fa42;width:38px;height:4px}
body[data-market-page="catalog"] .immersive-hero .market-hero-slides{max-width:720px;width:54%;height:auto;min-height:0}
body[data-market-page="catalog"] .immersive-hero [data-hero-slide] h1{font:900 clamp(48px,min(7.5vw,12vh),112px)/.96 Inter,system-ui,sans-serif;color:#fff;text-transform:uppercase;letter-spacing:-.055em;margin:0}
body[data-market-page="catalog"] .immersive-hero [data-hero-slide] .immersive-subtitle{font:800 clamp(25px,3.1vw,46px)/1.08 Inter,system-ui,sans-serif;color:#c5fa42;letter-spacing:-.035em;margin:14px 0 0}
body[data-market-page="catalog"] .immersive-hero [data-hero-slide] p{font:500 clamp(14px,1.5vw,20px)/1.45 Inter,system-ui,sans-serif;color:#f0fff5;margin:12px 0 0;max-width:52ch}
body[data-market-page="catalog"] .immersive-hero #storeHeroSearch{margin-top:clamp(16px,3vh,30px);max-width:620px}
body[data-market-page="catalog"] .immersive-hero .header-search{flex-wrap:nowrap;border:2px solid #fff;border-radius:999px;padding:6px;height:66px;box-sizing:border-box;background:rgb(255 255 255 / 9%);gap:6px}
body[data-market-page="catalog"] .immersive-hero .header-search input{flex:1 1 auto;width:0;min-width:0;height:100%;border-radius:999px;background:transparent;color:#fff;padding:0 10px 0 46px;font-size:18px}
body[data-market-page="catalog"] .immersive-hero .header-search input::placeholder{color:#f4fff8;opacity:1}
body[data-market-page="catalog"] .immersive-hero .header-search .search-icon{color:#fff;top:19px;left:20px;width:24px;height:24px}
body[data-market-page="catalog"] .immersive-hero .header-search>button[type="submit"]{height:50px;width:50px;flex:0 0 50px;padding:0;border-radius:50%;background:#c5fa42;color:#071909}
body[data-market-page="catalog"] .immersive-hero .header-search>button[type="submit"]::after{content:"→";font-size:30px}
html[lang="en"] body[data-market-page="catalog"] .immersive-hero .header-search>button[type="submit"]::after{content:"→"}
body[data-market-page="catalog"] .immersive-hero .store-quick-links{gap:12px 28px;margin-top:18px}
body[data-market-page="catalog"] .immersive-hero .store-quick-links a{font:600 clamp(14px,1.5vw,20px) Inter,system-ui,sans-serif;text-decoration-thickness:2px;text-underline-offset:8px}
.hero-scroll-cue{display:flex;align-items:center;gap:18px;width:fit-content;margin-top:clamp(22px,5vh,60px);color:#fff;font:600 16px Inter,system-ui,sans-serif;text-decoration:none}
.hero-scroll-cue span{font-size:28px}
body[data-market-page="catalog"] #catalogResultsTitle{font-family:Inter,system-ui,sans-serif;font-weight:800;letter-spacing:-.03em}
@media(max-height:680px) and (min-width:801px){body[data-market-page="catalog"] .immersive-hero .market-hero-content{padding-top:16px;padding-bottom:16px}body[data-market-page="catalog"] .immersive-hero .market-hero-eyebrow{margin-bottom:12px}body[data-market-page="catalog"] .immersive-hero [data-hero-slide] h1{font-size:64px}body[data-market-page="catalog"] .immersive-hero [data-hero-slide] .immersive-subtitle{font-size:30px}.hero-scroll-cue{margin-top:20px}}
@media(max-width:800px){body[data-market-page="catalog"] .site-header .top-inner{width:calc(100% - 24px);gap:10px}body[data-market-page="catalog"] .store-posgrado-menu{margin:0}body[data-market-page="catalog"] .hero.market-hero.immersive-hero{height:auto;min-height:calc(100svh - var(--market-header-height,80px))}body[data-market-page="catalog"] .immersive-hero .market-hero-content{height:auto;min-height:inherit;padding:32px 20px}body[data-market-page="catalog"] .immersive-hero .market-hero-slides{width:100%;max-width:620px}body[data-market-page="catalog"] .immersive-hero .market-hero-photo{display:block;opacity:.35;mask-image:linear-gradient(to right,transparent,#000);-webkit-mask-image:linear-gradient(to right,transparent,#000)}body[data-market-page="catalog"] .immersive-hero [data-hero-slide] h1{font-size:clamp(48px,10vw,76px)}body[data-market-page="catalog"] .immersive-hero .header-search input{font-size:16px}body[data-market-page="catalog"] .immersive-hero .header-search>button[type="submit"]{width:50px}body[data-market-page="catalog"] .site-header .nav-actions>.cart-button::after{display:none}}
@media(prefers-reduced-motion:reduce){.hero-scroll-cue{scroll-behavior:auto}}


.immersive-orders-link{color:#fff;font:600 17px Inter,system-ui,sans-serif;text-decoration:none;white-space:nowrap}
@media(max-width:1050px){.immersive-orders-link{display:none}}

/* Keep the immersive header readable in the standard light theme. */
body[data-market-page="catalog"]:not([data-accessibility-color]) > header.site-header{
background:linear-gradient(110deg,#002d1d,#053f35);color:#fff;border:0;
}
body[data-market-page="catalog"]:not([data-accessibility-color]) > .site-header .top,
body[data-market-page="catalog"]:not([data-accessibility-color]) > .site-header .top-inner{
background:transparent;border:0;box-shadow:none;
}
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .brand-title,
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .brand-title b,
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .brand-title em,
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .immersive-orders-link,
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .store-posgrado-menu>summary{color:#fff}
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .utility-controls .language-button{background:transparent;color:#fff;border-color:rgb(255 255 255 / 30%)}
body[data-market-page="catalog"] .site-header .brand{order:0}
body[data-market-page="catalog"] .site-header .store-posgrado-menu{order:1;flex:0 1 auto;margin-left:24px}
body[data-market-page="catalog"] .site-header .immersive-orders-link{order:2;margin-right:auto}
body[data-market-page="catalog"] .site-header .utility-controls{order:3;margin-left:0}
body[data-market-page="catalog"] .site-header .nav-actions{order:4}
@media(max-width:800px){body[data-market-page="catalog"] .site-header .store-posgrado-menu{order:5;flex-basis:100%;margin-left:0}}


/* Restore the previous pale-green header with readable dark controls. */
body[data-market-page="catalog"]:not([data-accessibility-color]) > header.site-header{background:#f4f9f5;color:#0a4a31;border-bottom:1px solid #cfe0d5}
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .brand-title,
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .brand-title b,
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .immersive-orders-link,
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .store-posgrado-menu>summary{color:#0a4a31}
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .brand-title em{color:#8a5f14}
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .utility-controls .language-button{background:#fff;color:#17211b;border-color:#c3d5c9}
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .nav-actions .login-button{background:transparent;color:#0a4a31}
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .store-account-initials{background:#0a4a31;color:#fff;border-color:#0a4a31}
body[data-market-page="catalog"]:not([data-accessibility-color]) .site-header .store-posgrado-menu[open]>summary{background:#e3eee6}


/* Return to the previous compact header dimensions. */
body[data-market-page="catalog"] .site-header .top-inner{min-height:70px;padding:8px 0;box-sizing:border-box}
body[data-market-page="catalog"] .site-header .brand .crest{width:40px;height:46px}
body[data-market-page="catalog"] .site-header .brand-title,
body[data-market-page="catalog"] .site-header .brand-title b,
body[data-market-page="catalog"] .site-header .brand-title em{font-size:clamp(20px,2.1vw,28px)}
body[data-market-page="catalog"] .site-header .nav-actions>.cart-button{min-height:44px;height:44px}
body[data-market-page="catalog"] .site-header .store-account-initials{width:38px;height:38px;font-size:14px}
body[data-market-page="catalog"] .site-header .store-posgrado-menu>summary,
body[data-market-page="catalog"] .site-header .immersive-orders-link{font-size:16px}
@media(max-width:800px){body[data-market-page="catalog"] .site-header .top-inner{padding:6px 0;gap:8px}body[data-market-page="catalog"] .site-header .brand-title,body[data-market-page="catalog"] .site-header .brand-title b,body[data-market-page="catalog"] .site-header .brand-title em{font-size:20px}}


/* Scroll motion is progressive: content stays readable without JavaScript. */
body[data-market-page="catalog"] .immersive-hero .kinetic-word{display:inline-block;transform:translate(calc(var(--word-direction,1) * var(--word-spread,0px)),var(--title-travel,0px)) scale(var(--title-scale,1));transform-origin:left center}
body[data-market-page="catalog"] .immersive-hero .market-hero-photo{transform:translateY(var(--hero-travel,0px)) scale(1.08);transform-origin:right center}
html{scroll-behavior:smooth}
@media(prefers-reduced-motion:reduce){
html{scroll-behavior:auto}
body[data-market-page="catalog"] .immersive-hero .kinetic-word,
body[data-market-page="catalog"] .immersive-hero .market-hero-photo{transform:none!important}
}

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
<script src="{{ asset('js/undc-store-motion.js') }}?v={{ filemtime(public_path('js/undc-store-motion.js')) }}" defer></script>
@endsection
