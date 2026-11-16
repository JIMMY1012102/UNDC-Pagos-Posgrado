@php($formErrors = session('errors') ?? $errors)
@if($formErrors->any() || session('auth_error'))
<div class="warning" role="alert" tabindex="-1">
@if(session('auth_error'))<p>{{ session('auth_error') }}</p>@endif
@if($formErrors->any())<ul>@foreach($formErrors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
</div>
@endif
