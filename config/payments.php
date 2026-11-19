<?php

$environment = env('APP_ENV', 'production');

return [
    // Se conserva la clave existente para PricingPolicy.
    'payme_enabled' => (bool) env('PAYME_ENABLED', false),

    // En local/testing se usa fake; fuera de esos entornos se exige configurar el driver.
    'driver' => env(
        'PAYMENTS_DRIVER',
        in_array($environment, ['local', 'testing'], true) ? 'fake' : 'payme'
    ),

    // Configuración genérica del proveedor. No presupone el contrato de Pay-me/Alignet.
    'base_url' => env('PAYMENTS_BASE_URL', ''),
    'merchant' => env('PAYMENTS_MERCHANT', ''),
    'acquirer' => env('PAYMENTS_ACQUIRER', ''),
    'key' => env('PAYMENTS_KEY', ''),
    'webhook_secret' => env('PAYMENTS_WEBHOOK_SECRET', ''),

    // El pedido vence en 24 horas (OrderService); el intento debe vencer antes.
    'attempt_ttl_minutes' => (int) env('PAYMENTS_ATTEMPT_TTL_MINUTES', 15),

    'connect_timeout_seconds' => (int) env('PAYMENTS_CONNECT_TIMEOUT', 5),
    'timeout_seconds' => (int) env('PAYMENTS_TIMEOUT', 15),

    'currency' => env('PAYMENTS_CURRENCY', 'PEN'),
    'amount_unit' => 'cents',
];
