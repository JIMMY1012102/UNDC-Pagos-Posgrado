<?php

namespace App\Modules\Sispagos\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-only integration adapter for canonical SISPAGOS offices and concepts.
 * Does not assume that tm_conceptos.centro_costo is an office foreign key.
 */
final class PublishedCatalogQuery
{
    /** Offices with enabled, available publications; no institutional ID is assumed. */
    public function offices(): Collection
    {
        return $this->availablePublications()
            ->select('office.oficina_id as id', 'office.oficina_nom as name')
            ->distinct()->orderBy('office.oficina_nom')->get();
    }

    /** @return Collection<int, object> */
    public function forOffice(int $legacyOfficeId, int $limit = 50): Collection
    {
        return $this->availablePublications()
            ->where('publication.legacy_office_id', $legacyOfficeId)
            ->orderBy('concept.concepto')
            ->limit(max(1, min($limit, 100)))
            ->get([
                'publication.id as catalog_item_id',
                'publication.slug',
                'publication.description',
                'publication.requirements',
                'publication.category',
                'publication.requires_eligibility_check',
                'office.oficina_id as legacy_office_id',
                'office.oficina_nom as office_name',
                'concept.id_concepto as legacy_concept_id',
                'concept.codigo_concepto',
                'concept.concepto as name',
                'concept.costo as legacy_base_amount',
            ]);
    }

    public function isAvailable(int $catalogItemId): bool
    {
        return $this->availablePublications()->where('publication.id', $catalogItemId)->exists();
    }

    private function availablePublications(): Builder
    {
        return DB::table('tienda_conceptos_catalogo as publication')
            ->join('tienda_configuracion_oficinas as settings', 'settings.legacy_office_id', '=', 'publication.legacy_office_id')
            ->join('tm_oficina as office', 'office.oficina_id', '=', 'publication.legacy_office_id')
            ->join('tm_conceptos as concept', 'concept.id_concepto', '=', 'publication.legacy_concept_id')
            ->where('settings.catalog_enabled', true)
            ->where('publication.is_published', true)
            ->where('office.estado', 1)
            ->whereNotNull('concept.costo')
            ->where('concept.costo', '>=', 0);
    }
}

