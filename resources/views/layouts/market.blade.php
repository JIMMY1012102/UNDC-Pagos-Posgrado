<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Tienda virtual de conceptos de Posgrado — Universidad Nacional de Cañete">
<title>@yield('title', 'UNDC MARKET · Tienda virtual de pagos')</title>
<link rel="icon" type="image/png" href="{{ asset('Logo_UNDC_OF.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/undc-tienda.css') }}?v={{ filemtime(public_path('css/undc-tienda.css')) }}">
</head><body class="@yield('bodyClass')" data-market-page="@yield('marketPage', 'catalog')">
@include('modules.tienda.partials.header')
@include('modules.tienda.partials.accessibility')
@yield('heroes')
<main class="wrap" id="catalogo">@yield('content')</main>
<footer class="footer">Universidad Nacional de Cañete · Oficina de Tecnologías de la Información · Versión de desarrollo de la tienda de Posgrado</footer>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<script src="{{ asset('js/undc-tienda.js') }}?v={{ filemtime(public_path('js/undc-tienda.js')) }}" defer></script>
<script src="{{ asset('js/undc-hero.js') }}?v={{ filemtime(public_path('js/undc-hero.js')) }}" defer></script>
</body></html>

