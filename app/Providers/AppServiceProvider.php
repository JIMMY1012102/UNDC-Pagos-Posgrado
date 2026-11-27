<?php

namespace App\Providers;

use App\Modules\Asistente\Contracts\AssistantProvider;
use App\Modules\Asistente\Services\AgentAssistantProvider;
use App\Modules\Catalogo\Contracts\CatalogItemLookup;
use App\Modules\Catalogo\Services\CatalogItemLookupService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CatalogItemLookup::class, CatalogItemLookupService::class);
        $this->app->bind(AssistantProvider::class, AgentAssistantProvider::class);
    }

    public function boot(): void
    {
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
