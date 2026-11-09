<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Sispagos\Queries\PublishedCatalogQuery;
use Illuminate\Support\Collection;

final class CatalogService
{
    public function __construct(private readonly PublishedCatalogQuery $catalog) {}

    public function offices(): Collection
    {
        return $this->catalog->offices()->map(static fn (object $office): array => [
            'id' => (int) $office->id,
            'name' => (string) $office->name,
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function forOffice(int $officeId): Collection
    {
        return $this->catalog->forOffice($officeId)->map(static function (object $item): array {
            return [
                'id' => (int) $item->catalog_item_id,
                'slug' => (string) $item->slug,
                'code' => (string) $item->codigo_concepto,
                'name' => (string) $item->name,
                'description' => $item->description,
                'requirements' => $item->requirements,
                'category' => $item->category,
                'requires_eligibility_check' => (bool) $item->requires_eligibility_check,
                'office' => [
                    'id' => (int) $item->legacy_office_id,
                    'name' => (string) $item->office_name,
                ],
                // A legacy cost is NOT an approved online checkout tariff.
                // It must not be represented as a payable amount.
                'pricing_status' => 'pending_institutional_approval',
                'checkout_enabled' => false,
            ];
        });
    }
}

