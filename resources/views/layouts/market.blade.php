@php($isJourneyHome = str_contains(trim($__env->yieldContent('bodyClass')), 'market-journey-page'))
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Tienda virtual de conceptos de Posgrado — Universidad Nacional de Cañete">
<style>:root{--container-max:1440px}@media(min-width:1920px){:root{--container-max:1680px}}</style>
<title>@yield('title', 'UNDC MARKET · Tienda virtual de pagos')</title>
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('logo-undc-64.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@if($isJourneyHome)
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700;800&family=Inter+Tight:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/undc-scroll-journey.css') }}?v={{ filemtime(public_path('css/undc-scroll-journey.css')) }}">
@else
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/undc-tienda.css') }}?v={{ filemtime(public_path('css/undc-tienda.css')) }}">
<link rel="stylesheet" href="{{ asset('css/undc-orders.css') }}?v={{ filemtime(public_path('css/undc-orders.css')) }}">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700;800&family=Inter+Tight:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/undc-hero-claro.css') }}?v={{ filemtime(public_path('css/undc-hero-claro.css')) }}">
@endif
</head>
@php($showSocialRail = !$isJourneyHome && trim($__env->yieldContent('marketPage', 'catalog')) === 'catalog')
<body class="{{ $showSocialRail ? 'market-with-social-rail' : '' }} @yield('bodyClass')" data-market-page="@yield('marketPage', 'catalog')">
@if($showSocialRail)
@include('modules.tienda.partials.social-rail')
@endif
@unless($isJourneyHome)
@include('modules.tienda.partials.header')
@include('modules.tienda.partials.accessibility')
@endunless
@yield('heroes')
@unless($isJourneyHome)
<main class="wrap" id="catalogo">@yield('content')</main>
<footer class="footer">Universidad Nacional de Cañete · Oficina de Tecnologías de la Información · Versión de desarrollo de la tienda de Posgrado</footer>
@endunless
<div id="toast" class="toast" role="status" aria-live="polite"></div>
@if($isJourneyHome)
<script src="{{ asset('js/undc-scroll-journey.js') }}?v={{ filemtime(public_path('js/undc-scroll-journey.js')) }}" defer></script>
@else
<script src="{{ asset('js/undc-tienda.js') }}?v={{ filemtime(public_path('js/undc-tienda.js')) }}" defer></script>
<script src="{{ asset('js/undc-hero.js') }}?v={{ filemtime(public_path('js/undc-hero.js')) }}" defer></script>
<script src="{{ asset('js/undc-hero-claro.js') }}?v={{ filemtime(public_path('js/undc-hero-claro.js')) }}" defer></script>
@endif
</body></html>



