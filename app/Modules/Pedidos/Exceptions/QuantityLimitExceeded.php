<?php

namespace App\Modules\Pedidos\Exceptions;

use RuntimeException;

final class QuantityLimitExceeded extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('QUANTITY_LIMIT');
    }
}
