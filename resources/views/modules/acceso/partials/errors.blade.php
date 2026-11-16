@if($errors->any() || session('auth_error'))
<div class="warning" role="alert" tabindex="-1">
@if(session('auth_error'))<p>{{ session('auth_error') }}</p>@endif
@if($errors->any())<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
</div>
@endif
