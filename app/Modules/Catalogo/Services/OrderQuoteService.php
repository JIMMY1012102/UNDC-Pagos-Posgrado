<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Sispagos\Queries\PublishedCatalogQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Importes oficiales publicados, nunca importes enviados por el navegador. */
final class OrderQuoteService
{
    public function __construct(private readonly PublishedCatalogQuery $catalog) {}

    public function line(int $id, int $quantity): ?array
    {
        return $this->lines([$id], $quantity)[$id] ?? null;
    }

    /** Resolve a complete catalog page in two queries. */
    public function lines(array $ids, int $quantity = 1): array
    {
        if ($quantity < 1 || $quantity > 20) return [];
        $items = $this->catalog->quoteItems($ids);

        return $this->linesForItems($items, $quantity);
    }

    /** Cotiza publicaciones ya leídas, sin repetir el join del catálogo. */
    public function linesForItems(Collection $items, int $quantity = 1): array
    {
        if ($quantity < 1 || $quantity > 20) return [];
        $ids = $items->pluck('catalog_item_id')->all();
        $versions = DB::table('tienda_versiones_tarifas')->whereIn('catalog_item_id', $ids)
            ->where('is_approved', true)->whereNotNull('approval_reference')
            ->where('approval_reference', '<>', '')->where('currency', 'PEN')
            ->where('valid_from', '<=', now())
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>', now()))->get()->groupBy('catalog_item_id');
        $result = [];
        foreach ($items as $item) {
            $id = (int) $item->catalog_item_id;
            $result[$id] = $this->resolve((int) $id, $quantity, $item, $versions->get($id) ?? collect());
        }
        return $result;
    }

    private function resolve(int $id, int $quantity, object $item, \Illuminate\Support\Collection $prices): ?array
    {
        if ($item->requires_eligibility_check) return null;
        // Una tarifa vigente inequívoca; no escoger arbitrariamente entre aprobaciones.
        if ($prices->count() > 1) {
            return null;
        }
        // El catálogo institucional ya contiene importes oficiales. Una versión
        // aprobada vigente puede sustituirlos; no se exige duplicar su aprobación.
        $amount = $prices->isNotEmpty()
            ? (string) $prices->first()->amount
            : (string) ($item->reference_amount ?? $item->legacy_base_amount);
        if (! preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $amount)) {
            return null;
        }
        [$whole, $fraction] = array_pad(explode('.', $amount), 2, '');
        $cents = (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
        if ($cents <= 0) {
            return null;
        }
        return [
            'catalog_item_id' => $id, 'concept_code' => (string) $item->codigo_concepto,
            'concept_name' => (string) $item->name, 'legacy_concept_id' => (int) $item->legacy_concept_id,
            'legacy_office_id' => (int) $item->legacy_office_id, 'quantity' => $quantity,
            'unit_cents' => $cents, 'total_cents' => $cents * $quantity,
        ];
    }
}
