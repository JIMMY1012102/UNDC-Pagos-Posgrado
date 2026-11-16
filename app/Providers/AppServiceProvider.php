<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Modules\Catalogo\Contracts\CatalogItemLookup::class,
            \App\Modules\Catalogo\Services\CatalogItemLookupService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Named buckets prevent catalogue/cart requests from consuming login attempts.
        RateLimiter::for('market-login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return redirect()->route('tienda.login')
                        ->withErrors(['email' => 'Has realizado varios intentos de acceso. Espera un minuto y vuelve a intentarlo.'])
                        ->withInput($request->only('email'))
                        ->withHeaders($headers);
                });
        });

        foreach (['market-access' => 30, 'market-oauth' => 15,
            'market-registration' => 10, 'market-profile' => 20] as $name => $attempts) {
            RateLimiter::for($name, fn (Request $request) => Limit::perMinute($attempts)
                ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));
        }
    }
}
