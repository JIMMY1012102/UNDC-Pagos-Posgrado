@extends('layouts.market')
@section('title', 'Carrito · UNDC MARKET')
@section('marketPage', 'cart')
@section('bodyClass', 'commerce-view')
@section('content')
<div class="page-grid cart-view"><div>
<section class="screen visible" data-screen="cart">
<div class="panel cart-review-panel"><button class="back" type="button" data-go="catalog">← Seguir explorando</button>
<div class="cart-review-heading"><span class="cart-heading-icon"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h2l3 12h11l2-9H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></span><div><h2>Revisa tu carrito</h2><p class="subtle">Confirma los conceptos que seleccionaste antes de identificarte.</p></div></div>
<div id="cartEmptyState" class="cart-empty-state" hidden><svg class="cart-empty-art" viewBox="0 0 300 190" aria-hidden="true" focusable="false"><defs><linearGradient id="emptyCartBlue" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#51b4ff"/><stop offset="1" stop-color="#0063ee"/></linearGradient></defs><circle cx="156" cy="92" r="77" fill="#168aff" opacity=".09"/><path d="M42 73h36a13 13 0 0 0-25-4 9 9 0 0 0-11 4Zm180 45h38a12 12 0 0 0-22-7 10 10 0 0 0-16 7Z" fill="#168aff" opacity=".12"/><ellipse cx="156" cy="163" rx="70" ry="8" fill="#0764ce" opacity=".12"/><path d="M108 78h100l-13 55h-73Z" fill="url(#emptyCartBlue)"/><path d="M95 61h16l17 78h67" fill="none" stroke="#0755bd" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/><path d="M143 94v10m17-10v10m17-10v10" stroke="#fff" stroke-width="6" stroke-linecap="round"/><circle cx="136" cy="153" r="8" fill="#24466a"/><circle cx="187" cy="153" r="8" fill="#24466a"/><path d="m214 39 3-14m8 24 12-10m-7 23 15-3" stroke="#348dff" stroke-width="5" stroke-linecap="round"/></svg><h3>Tu carrito está vacío</h3><p>Selecciona un concepto de nuestros catálogos o trámites para comenzar tu compra.</p><button type="button" class="btn cart-explore-button" data-go="catalog"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14l1 14H4L5 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg><span>Explorar conceptos y agregar al carrito</span><span aria-hidden="true">→</span></button></div>
<div class="cart-list-heading" id="cartListHeading" hidden><span aria-hidden="true">🛒</span><strong id="cartTotalCount">0 conceptos</strong></div><div id="cartRows"></div><div class="cart-total" id="cartEstimateRow" hidden><strong>Total referencial:</strong><span id="cartEstimate"></span></div>
<button class="btn block" id="cartContinue" type="button" disabled>Continuar con mi compra →</button>
<p class="smallprint cart-reassurance"><span aria-hidden="true">ⓘ</span> <span>Solo al continuar te pediremos iniciar sesión. No se realizará ningún cobro en esta etapa.</span></p>
</div></section>


@include('modules.tienda.partials.checkout')
</div>
@include('modules.tienda.partials.sidebar')
</div>
@endsection
