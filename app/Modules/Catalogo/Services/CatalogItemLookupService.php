<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Catalogo\Contracts\CatalogItemLookup;
use App\Modules\Sispagos\Queries\PublishedCatalogQuery;

final class CatalogItemLookupService implements CatalogItemLookup
{
    public function __construct(private readonly PublishedCatalogQuery $catalog) {}

    public function isAvailable(int $catalogItemId): bool
    {
        return $this->catalog->isAvailable($catalogItemId);
    }
}
