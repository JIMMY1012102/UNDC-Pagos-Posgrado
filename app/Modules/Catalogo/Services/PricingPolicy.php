<?php

namespace App\Modules\Catalogo\Services;

final class PricingPolicy
{
    public static function status(): array
    {
        return ['pricing_status' => 'official', 'checkout_enabled' => (bool) config('payments.payme_enabled', false)];
    }
}
