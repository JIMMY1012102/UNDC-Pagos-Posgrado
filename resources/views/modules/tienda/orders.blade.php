@extends('layouts.market')
@section('title', 'Mis pedidos · UNDC MARKET')
@section('marketPage', 'orders')
@section('bodyClass', 'commerce-view market-cart-view market-orders-view')
@section('content')
<div class="payment-preview orders-workspace">
<nav class="payment-breadcrumb" aria-label="Ruta de compra"><a href="/tienda">Catálogo</a><span aria-hidden="true">/</span><a href="/tienda/carrito">Carrito</a><span aria-hidden="true">/</span><span aria-current="page">Mis pedidos</span></nav>
<section id="ordersListPanel" aria-labelledby="ordersTitle">
<header class="payment-preview-heading orders-heading"><div><h1 id="ordersTitle" tabindex="-1">Tus pedidos, en un solo lugar.</h1><p>Consulta tus conceptos, importes y el estado de cada pedido.</p></div><button type="button" class="payment-text-button" id="refreshOrders">↻ Actualizar estado</button></header>
<p id="ordersMessage" role="status" aria-live="polite"></p>
<div id="ordersList" class="orders-list"></div><button class="payment-text-button" id="moreOrders" type="button" hidden>Ver más pedidos</button>
</section>
<section id="orderDetailPanel" aria-labelledby="orderDialogTitle" hidden>
<header class="payment-preview-heading"><div><h1 id="orderDialogTitle" tabindex="-1">Tu pedido está listo.</h1><p>Consulta el detalle y los siguientes pasos.</p></div><button class="payment-text-button" id="closeOrderDialog" type="button">← Mis pedidos</button></header>
<p id="orderDetailMessage" role="status" aria-live="polite"></p><div id="orderDetail"></div>
</section>
<div class="payment-review-footer orders-footer"><span>Universidad Nacional de Cañete · Oficina de Tecnologías de la Información</span><span>Generar un pedido no realiza un cobro ni inicia un trámite académico.</span></div>
</div>
<script src="{{ asset('js/undc-orders.js') }}?v={{ filemtime(public_path('js/undc-orders.js')) }}" defer></script>
@endsection
