<?php

namespace App\Modules\Tienda\Services;

use App\Modules\Tienda\Queries\PublishedCatalogQuery;
use InvalidArgumentException;

/**
 * Read-only contract for the shared catalog.
 *
 * Institutional prices and purchase permissions have not yet been approved.
 * Never interpret legacy_base_amount_reference as the checkout price.
 */
final class CatalogService
{
    public function __construct(
        private readonly PublishedCatalogQuery $publishedCatalog,
    ) {}

    /** @return array<string, mixed> */
    public function listForOffice(int $legacyOfficeId, int $limit = 50): array
    {
        if ($legacyOfficeId <= 0) {
            throw new InvalidArgumentException('El identificador de oficina debe ser positivo.');
        }

        $safeLimit = max(1, min($limit, 100));
        $items = $this->publishedCatalog->forOffice($legacyOfficeId, $safeLimit)
            ->map(static fn (object $row): array => [
                'id' => (int) $row->catalog_item_id,
                'slug' => (string) $row->slug,
                'legacy_concept_id' => (int) $row->legacy_concept_id,
                'code' => (string) $row->codigo_concepto,
                'name' => (string) $row->name,
                'description' => $row->description,
                'requirements' => $row->requirements,
                'requires_eligibility_check' => (bool) $row->requires_eligibility_check,
                // Reference-only cost from the original SISPAGOS master.
                // Never use browser-provided or this display field to charge a buyer.
                'legacy_base_amount_reference' => (string) $row->legacy_base_amount,
                'office' => [
                    'id' => (int) $row->legacy_office_id,
                    'name' => (string) $row->office_name,
                ],
            ])
            ->values()
            ->all();

        return [
            'legacy_office_id' => $legacyOfficeId,
            'pricing_status' => 'reference_only',
            'purchase_enabled' => false,
            'count' => count($items),
            'items' => $items,
        ];
    }
}
