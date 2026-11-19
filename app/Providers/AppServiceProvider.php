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
        $this->validatePaymentConfiguration();

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

    /**
     * Reject unsafe or unsupported payment configuration at application startup.
     */
    private function validatePaymentConfiguration(): void
    {
        $driver = (string) config('payments.driver');
        $environment = (string) config('app.env');

        if (! in_array($driver, ['fake', 'payme'], true)) {
            throw new \LogicException("Unsupported payment driver [{$driver}]. Allowed drivers: fake, payme.");
        }

        if ($environment === 'production' && $driver === 'fake') {
            throw new \LogicException('The fake payment driver is forbidden in production. Set PAYMENTS_DRIVER=payme.');
        }

        $attemptTtl = (int) config('payments.attempt_ttl_minutes');
        $orderTtl = 24 * 60; // OrderService currently expires orders after addHours(24).

        if ($attemptTtl < 1 || $attemptTtl >= $orderTtl) {
            throw new \LogicException("PAYMENTS_ATTEMPT_TTL_MINUTES must be between 1 and ".($orderTtl - 1)." minutes; the current order TTL is {$orderTtl} minutes.");
        }

        foreach (['connect_timeout_seconds', 'timeout_seconds'] as $key) {
            if ((int) config("payments.{$key}") < 1) {
                throw new \LogicException("Payment configuration [payments.{$key}] must be greater than zero.");
            }
        }
    }
}
