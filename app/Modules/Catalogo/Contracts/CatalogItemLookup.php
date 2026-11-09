<?php

namespace App\Modules\Catalogo\Contracts;

/** Public read-only capability: Pedidos should not join legacy tables itself. */
interface CatalogItemLookup
{
    public function isAvailable(int $catalogItemId): bool;
}
