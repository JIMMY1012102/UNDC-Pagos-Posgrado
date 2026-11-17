<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Catalogo\Contracts\CatalogItemLookup;

final class CatalogItemLookupService implements CatalogItemLookup
{
    public function __construct(private readonly OrderQuoteService $prices) {}

    public function isAvailable(int $catalogItemId): bool
    {
        return $this->prices->line($catalogItemId, 1) !== null;
    }
}
