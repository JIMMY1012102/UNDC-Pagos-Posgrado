<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Catalogo\Contracts\CatalogItemLookup;
use App\Modules\Sispagos\Queries\PublishedCatalogQuery;

final class CatalogItemLookupService implements CatalogItemLookup
{
    public function __construct(private readonly PublishedCatalogQuery $catalog, private readonly OrderQuoteService $prices) {}

    public function isAvailable(int $catalogItemId): bool
    {
        return $this->prices->line($catalogItemId, 1) !== null;
    }
}
