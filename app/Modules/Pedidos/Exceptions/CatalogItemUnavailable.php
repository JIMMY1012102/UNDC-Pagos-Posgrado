<?php

namespace App\Modules\Pedidos\Exceptions;

use RuntimeException;

final class CatalogItemUnavailable extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('CATALOG_ITEM_UNAVAILABLE');
    }
}
