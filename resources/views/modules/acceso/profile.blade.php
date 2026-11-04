@extends('layouts.market')
@section('title', 'COMPLETA TUS DATOS · UNDC MARKET')
@section('marketPage', 'profile')
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
<div class="market-login-card account-data-card">
<form method="POST" action="{{ route('auth.logout') }}" data-logout-form>
@csrf
<input type="hidden" name="return_to" value="login">
<button class="back" type="submit">← Regresar</button>
</form>
<div class="market-login-heading" style="padding-top:56px"><h1>COMPLETA TUS DATOS</h1></div>
@include('modules.acceso.partials.errors')
<form method="post" action="{{ route('tienda.perfil.store') }}">
@csrf
<div class="form-grid">
@include('modules.acceso.partials.profile-fields')
<div class="field"><label for="account_email">Correo de tu cuenta</label><input id="account_email" type="email" value="{{ auth()->user()->email }}" readonly></div></div><button type="submit" class="btn block" style="margin-top:24px">Guardar y continuar →</button>
</form></div></div></section>
@endsection
