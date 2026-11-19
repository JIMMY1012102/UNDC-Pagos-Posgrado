<?php

namespace Tests\Feature\Modules\Pagos;

use App\Providers\AppServiceProvider;
use LogicException;
use Tests\TestCase;

class PaymentConfigurationTest extends TestCase
{
    public function test_payment_configuration_has_safe_defaults_and_uses_existing_payme_flag(): void
    {
        $this->assertSame('fake', config('payments.driver'));
        $this->assertFalse(config('payments.payme_enabled'));
        $this->assertSame('', config('payments.base_url'));
        $this->assertSame('', config('payments.merchant'));
        $this->assertSame('', config('payments.acquirer'));
        $this->assertSame('', config('payments.key'));
        $this->assertSame('', config('payments.webhook_secret'));
        $this->assertSame(15, config('payments.attempt_ttl_minutes'));
        $this->assertSame(5, config('payments.connect_timeout_seconds'));
        $this->assertSame(15, config('payments.response_timeout_seconds'));
        $this->assertSame('PEN', config('payments.currency'));
        $this->assertSame('cents', config('payments.amount_unit'));
    }

    public function test_fake_driver_is_rejected_in_production(): void
    {
        config(['app.env' => 'production', 'payments.driver' => 'fake']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('fake payment driver is forbidden in production');

        (new AppServiceProvider($this->app))->boot();
    }

    public function test_unknown_driver_is_rejected(): void
    {
        config(['app.env' => 'testing', 'payments.driver' => 'unknown']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unsupported payment driver [unknown]');

        (new AppServiceProvider($this->app))->boot();
    }

    public function test_attempt_ttl_is_less_than_the_current_order_ttl(): void
    {
        $orderTtlMinutes = 24 * 60; // OrderService: addHours(24).

        $this->assertGreaterThan(0, config('payments.attempt_ttl_minutes'));
        $this->assertLessThan($orderTtlMinutes, config('payments.attempt_ttl_minutes'));
    }

    public function test_env_example_documents_all_generic_payment_variables(): void
    {
        $example = file_get_contents(base_path('.env.example'));

        foreach ([
            'PAYMENTS_DRIVER=',
            'PAYME_ENABLED=',
            'PAYMENTS_BASE_URL=',
            'PAYMENTS_MERCHANT=',
            'PAYMENTS_ACQUIRER=',
            'PAYMENTS_KEY=',
            'PAYMENTS_WEBHOOK_SECRET=',
            'PAYMENTS_ATTEMPT_TTL_MINUTES=',
            'PAYMENTS_CONNECT_TIMEOUT=',
            'PAYMENTS_RESPONSE_TIMEOUT=',
            'PAYMENTS_CURRENCY=',
        ] as $key) {
            $this->assertStringContainsString($key, $example);
        }

        $this->assertStringNotContainsString('PAYMENTS_MERCHANT=real', $example);
        $this->assertStringNotContainsString('PAYMENTS_KEY=real', $example);
    }
}
