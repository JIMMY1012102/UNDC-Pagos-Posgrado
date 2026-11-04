<?php

namespace App\Modules\Acceso\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Acceso\Services\BuyerProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class AccountController extends Controller
{
    private function profileRules(): array
    {
        return [
            'first_names' => ['required', 'string', 'max:160'],
            'last_names' => ['required', 'string', 'max:160'],
            'document_type' => ['required', Rule::in(['DNI', 'CE', 'PASAPORTE'])],
            'document_number' => ['required', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function registerForm(Request $request)
    {
        return $request->user() ? redirect('/tienda/perfil') : view('modules.acceso.register', ['profile' => []]);
    }

    public function register(Request $request, BuyerProfileService $profiles)
    {
        if ($request->user()) {
            return redirect('/tienda/perfil');
        }
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate(array_merge($this->profileRules(), [
            'email' => ['required', 'email', 'max:254', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'max:128', 'confirmed', Password::min(12)],
        ]));
        $user = DB::transaction(function () use ($data, $profiles) {
            $user = User::query()->create([
                'name' => mb_substr($data['first_names'].' '.$data['last_names'], 0, 255),
                'email' => $data['email'],
                'password' => $data['password'], // User casts this to a password hash.
            ]);
            $profiles->save((int) $user->id, array_merge($data, ['contact_email' => $data['email']]));
            return $user;
        });
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        return redirect('/tienda/carrito');
    }

    public function login(Request $request, BuyerProfileService $profiles)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:128'],
        ]);
        $data['email'] = mb_strtolower(trim($data['email']));
        if (! Auth::guard('web')->attempt($data)) {
            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos.'])
                ->withInput($request->only('email'));
        }
        $request->session()->regenerate();
        return redirect($profiles->isComplete((int) $request->user()->id) ? '/tienda/carrito' : '/tienda/perfil');
    }

    public function profileForm(Request $request, BuyerProfileService $profiles)
    {
        if (! $request->user()) {
            return redirect('/tienda/login');
        }
        if ($profiles->isComplete((int) $request->user()->id)) {
            return redirect('/tienda/carrito');
        }
        // Only provider-verified server-side session data is used for prefilling.
        $profile = array_merge($request->session()->get('market_google_profile', []),
            array_filter($profiles->find((int) $request->user()->id) ?? [], fn ($value) => filled($value)));
        return response()->view('modules.acceso.profile', ['profile' => $profile])->header('Cache-Control', 'no-store');
    }

    public function saveProfile(Request $request, BuyerProfileService $profiles)
    {
        if (! $request->user()) {
            return redirect('/tienda/login');
        }
        $data = $request->validate($this->profileRules());
        $profiles->save((int) $request->user()->id, array_merge($data, ['contact_email' => $request->user()->email]));
        $request->session()->forget('market_google_profile');
        return redirect('/tienda/carrito');
    }
}
