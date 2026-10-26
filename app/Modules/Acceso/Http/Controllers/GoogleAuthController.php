<?php

namespace App\Modules\Acceso\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Acceso\Services\GoogleIdentityService;
use App\Modules\Acceso\Services\BuyerProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

final class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        abort_unless($this->enabled($request), 404);

        // Stateful OAuth 2.0: Socialite protects the redirect/callback with state.
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, GoogleIdentityService $service, BuyerProfileService $profiles): RedirectResponse
    {
        abort_unless($this->enabled($request), 404);

        if ($request->has('error')) {
            return redirect('/')->with('auth_error', 'No se completó el inicio de sesión.');
        }

        try {
            $google = Socialite::driver('google')->user();
            $user = $service->resolve($google);
        } catch (Throwable $e) {
            report($e);

            // Never expose OAuth access tokens, provider details, or emails.
            return redirect('/')->with('auth_error', 'No se pudo validar la cuenta. Contacta con soporte.');
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        // Public catalog remains available before authentication; the first
        // authenticated visit needs a profile once, subsequent visits do not.
        $destination = $profiles->isComplete((int) $user->id)
            ? '/?registration=complete'
            : '/?registration=pending';

        return redirect($destination)->with('auth_success', 'Sesión iniciada correctamente.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function enabled(Request $request): bool
    {
        return config('services.google.enabled') === true
            && filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'))
            && $request->isSecure()
            && str_starts_with((string) config('services.google.redirect'), 'https://');
    }
}
