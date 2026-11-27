<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Sispagos\Queries\PublishedCatalogQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

    public function __construct(private readonly PublishedCatalogQuery $catalog, private readonly OrderQuoteService $prices) {}

    /** Resolve all cart concepts in two queries, independently of catalog pagination. */
    public function referencesFor(array $ids): Collection
    {
        $items = $this->catalog->quoteItems($ids);
        $quotes = $this->prices->linesForItems($items);
        return $items->mapWithKeys(static function (object $item) use ($quotes): array {
            $quote = $quotes[$item->catalog_item_id] ?? null;
            return [(int) $item->catalog_item_id => [
                'name' => (string) $item->name,
                'reference_amount' => $quote !== null
                    ? sprintf('%d.%02d', intdiv($quote['unit_cents'], 100), $quote['unit_cents'] % 100)
                    : number_format((float) $item->reference_amount, 2, '.', ''),
            ]];
        });
    }

    public function offices(): Collection
    {
        // Cache only scalar arrays: production disables unserialization of PHP classes.
        $offices = Cache::remember('catalogo.publico.v2.oficinas', 120, fn (): array => $this->catalog->offices()
            ->map(static fn (object $office): array => [
                'id' => (int) $office->id,
                'name' => (string) $office->name,
            ])
            ->all());

        return collect($offices);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function forOffice(int $officeId): Collection
    {
        $items = Cache::remember("catalogo.publico.v2.oficina.{$officeId}", 120, function () use ($officeId): array {
            $items = $this->catalog->forOffice($officeId);
            $quotes = $this->prices->linesForItems($items);

            return $items->map(function (object $item) use ($quotes): array {
                $quote = $quotes[$item->catalog_item_id] ?? null;
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
                    'reference_amount' => $quote !== null ? sprintf('%d.%02d', intdiv($quote['unit_cents'], 100), $quote['unit_cents'] % 100) : number_format((float) $item->legacy_base_amount, 2, '.', ''),
                    'orderable' => $quote !== null,
                    'orderable_reason' => $quote !== null ? null : ($item->requires_eligibility_check ? 'eligibility_required' : 'invalid_or_ambiguous_price'),
                ];
            })->all();
        });

        $status = PricingPolicy::status();

        return collect($items)->map(static fn (array $item): array => [...$item, ...$status]);
    }
}
