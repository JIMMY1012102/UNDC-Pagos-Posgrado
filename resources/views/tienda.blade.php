@extends('layouts.market')
@section('marketPage', 'catalog')
@section('heroes')
@include('modules.tienda.partials.heroes')
@endsection
@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:wght@600;700&display=swap" rel="stylesheet">
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
