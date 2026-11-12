<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Sispagos\Queries\PublishedCatalogQuery;
use Illuminate\Support\Collection;

final class CatalogService
{
    // Mantiene la lectura de datos antiguos hasta ejecutar la migración en cada entorno.
    private const CATEGORIAS_ANTERIORES = [
        'admission' => 'admision',
        'tuition' => 'matricula_pensiones',
        'transfers' => 'tramites',
        'certificates' => 'certificados_constancias',
        'degrees' => 'tesis_grados',
        'administrative' => 'servicios_administrativos',
    ];

    public function __construct(private readonly PublishedCatalogQuery $catalog) {}

    /** Resolve all cart concepts in one query, independently of catalog pagination. */
    public function referencesFor(array $ids): Collection
    {
        return $this->catalog->referencesFor($ids)->mapWithKeys(static fn (object $item): array => [
            (int) $item->catalog_item_id => [
                'name' => (string) $item->name,
                'reference_amount' => number_format((float) $item->reference_amount, 2, '.', ''),
            ],
        ]);
    }

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
                'category' => self::CATEGORIAS_ANTERIORES[$item->category] ?? $item->category,
                'categoria_id' => $item->categoria_id === null ? null : (int) $item->categoria_id,
                'category_name' => $item->category_name,
                'requires_eligibility_check' => (bool) $item->requires_eligibility_check,
                'office' => [
                    'id' => (int) $item->legacy_office_id,
                    'name' => (string) $item->office_name,
                ],
                'reference_amount' => number_format((float) $item->legacy_base_amount, 2, '.', ''),
                // A legacy cost is NOT an approved online checkout tariff.
                // It must not be represented as a payable amount.
                'pricing_status' => 'pending_institutional_approval',
                'checkout_enabled' => false,
            ];
        });
    }
}
