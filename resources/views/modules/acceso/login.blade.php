@extends('layouts.market')
@section('title', 'Inicia sesión · UNDC MARKET')
@section('marketPage', 'login')
@section('bodyClass', 'commerce-view market-login-view')
@section('content')
<section class="screen visible market-login-screen" data-screen="login" aria-labelledby="marketLoginTitle">
<div class="market-login-toolbar"><button class="market-login-accessibility" type="button" data-accessibility-toggle aria-expanded="false" aria-controls="accessibilityPanel"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="4" r="2"/><path d="M4 8h16m-8 0v6m0 0-5 7m5-7 5 7"/></svg><span>Accesibilidad</span><span aria-hidden="true">⌄</span></button></div>
<div class="market-login-layout">
<div class="market-login-intro">
<a href="/tienda" class="market-login-brand"><img src="{{ asset('Logo_UNDC_OF.png') }}" alt="Escudo UNDC"><span><strong>UNDC <em>MARKET</em></strong><small>Tienda virtual de pagos de la UNDC</small></span></a>
<img class="market-login-mascot" src="{{ asset('assets/login/robot-undc.png') }}" alt="Asistente universitario UNDC con birrete y computadora" width="1024" height="1024">
<div class="market-login-benefits"><span><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20m-15 5h4"/></svg><b>Organiza tus trámites</b></span><span><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/><path d="m8 12 3 3 5-6"/></svg><b>Acceso a tu perfil</b></span><span><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><b>Consulta en línea</b></span></div>
</div>
<div class="market-login-card">
<button class="back" type="button" data-go="catalog">← Regresar</button>
<div class="market-login-heading"><span class="market-login-symbol"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></span><div><h1 id="marketLoginTitle" tabindex="-1">INICIA SESIÓN</h1></div></div>
@include('modules.acceso.partials.errors')
<form class="market-login-password" method="post" action="{{ route('tienda.login.store') }}">
@csrf<label for="marketLoginEmail">Correo electrónico</label><div class="market-login-input"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg><input id="marketLoginEmail" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required placeholder="Correo electrónico"></div>
<label for="marketLoginPassword">Contraseña</label><div class="market-login-input"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg><input id="marketLoginPassword" type="password" name="password" autocomplete="current-password" required placeholder="Contraseña"></div>
<button type="button" class="market-login-forgot" disabled>¿Olvidaste tu contraseña?</button>
<button type="submit" class="btn market-login-submit">Iniciar sesión</button>
</form>
<div class="market-login-divider"><span>o</span></div>
@php($marketGoogleReady = config('services.google.enabled') === true && filled(config('services.google.client_id')) && filled(config('services.google.client_secret')) && filled(config('services.google.redirect')) && request()->isSecure() && str_starts_with((string) config('services.google.redirect'), 'https://'))
@if($marketGoogleReady)
<a id="marketLoginGoogle" class="market-login-google" href="/auth/google/redirect"><svg class="market-google-icon" aria-hidden="true" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65Z"/><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.87.92 7.52 2.56 10.78l7.97-6.19Z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.15 1.45-4.92 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/></svg>Continuar con Google</a>
@else
<button id="marketLoginGoogle" class="market-login-google" type="button" disabled><svg class="market-google-icon" aria-hidden="true" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65Z"/><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.87.92 7.52 2.56 10.78l7.97-6.19Z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.15 1.45-4.92 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/></svg>Continuar con Google</button>

@endif
<div class="market-login-register"><span>¿Aún no tienes una cuenta?</span><a href="{{ route('tienda.registro') }}" style="color:#168aff;font-weight:700">Regístrate</a></div>
</div>
</div>
</section>


@endsection
