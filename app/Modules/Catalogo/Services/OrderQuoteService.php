<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Sispagos\Queries\PublishedCatalogQuery;
use Illuminate\Support\Facades\DB;

/** Tarifas aprobadas, nunca importes enviados por el navegador. */
final class OrderQuoteService
{
    public function __construct(private readonly PublishedCatalogQuery $catalog) {}

    public function line(int $id, int $quantity): ?array
    {
        $item = $this->catalog->findAvailable($id);
        if (! $item || $item->requires_eligibility_check || $quantity < 1 || $quantity > 20) {
            return null;
        }
        $prices = DB::table('tienda_versiones_tarifas')->where('catalog_item_id', $id)
            ->where('is_approved', true)->whereNotNull('approval_reference')
            ->where('approval_reference', '<>', '')->where('currency', 'PEN')
            ->where('valid_from', '<=', now())
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>', now()))->get();
        // Una tarifa vigente inequívoca; no escoger arbitrariamente entre aprobaciones.
        if ($prices->count() !== 1) {
            return null;
        }
        $price = $prices->first();
        $amount = (string) $price->amount;
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
