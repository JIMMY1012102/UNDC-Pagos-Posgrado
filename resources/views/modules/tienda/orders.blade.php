@extends('layouts.market')
@section('title', 'Mis pedidos · UNDC MARKET')
@section('marketPage', 'orders')
@section('bodyClass', 'commerce-view')
@section('content')
<link rel="stylesheet" href="{{ asset('css/undc-orders.css') }}?v={{ filemtime(public_path('css/undc-orders.css')) }}">
<section class="panel orders-panel" aria-labelledby="ordersTitle">
<a class="back" href="/tienda">← Seguir explorando</a>
<header class="orders-heading"><div><h1 id="ordersTitle" tabindex="-1">Mis pedidos</h1><p>Consulta tus conceptos, vencimientos y estados de pago.</p></div><button type="button" class="btn" id="refreshOrders">Actualizar estado</button></header>
<p id="ordersMessage" role="status" aria-live="polite"></p>
<div id="ordersList"></div><button class="back" id="moreOrders" type="button" hidden>Ver más pedidos</button>
</section>
<dialog class="order-dialog" id="orderDialog" aria-labelledby="orderDialogTitle"><button class="back" id="closeOrderDialog" type="button">Cerrar ×</button><h2 id="orderDialogTitle">Detalle del pedido</h2><div id="orderDetail"></div></dialog>
<script src="{{ asset('js/undc-orders.js') }}?v={{ filemtime(public_path('js/undc-orders.js')) }}" defer></script>
@endsection
