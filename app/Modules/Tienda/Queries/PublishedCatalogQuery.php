<?php

namespace App\Modules\Tienda\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reads SISPAGOS' canonical offices/concepts from the SAME database as the store.
 * Does not assume tm_conceptos.centro_costo is an FK to tm_oficina.
 * No public route is exposed until business rules and authorization are approved.
 */
final class PublishedCatalogQuery
{
    /** @return Collection<int, object> */
    public function forOffice(int $legacyOfficeId, int $limit = 50): Collection
    {
        return DB::table('store_catalog_items as publication')
            ->join('store_office_settings as settings', function ($join) {
                $join->on('settings.legacy_office_id', '=', 'publication.legacy_office_id');
            })
            ->join('tm_oficina as office', 'office.oficina_id', '=', 'publication.legacy_office_id')
            ->join('tm_conceptos as concept', 'concept.id_concepto', '=', 'publication.legacy_concept_id')
            ->where('publication.legacy_office_id', $legacyOfficeId)
            ->where('settings.catalog_enabled', true)
            ->where('publication.is_published', true)
            ->where('office.estado', 1)
            ->whereNotNull('concept.costo')
            ->where('concept.costo', '>=', 0)
            ->orderBy('concept.concepto')
            ->limit(max(1, min($limit, 100)))
            ->get([
                'publication.id as catalog_item_id',
                'publication.slug',
                'publication.description',
                'publication.requirements',
                'publication.requires_eligibility_check',
                'office.oficina_id as legacy_office_id',
                'office.oficina_nom as office_name',
                'concept.id_concepto as legacy_concept_id',
                'concept.codigo_concepto',
                'concept.concepto as name',
                'concept.costo as legacy_base_amount',
            ]);
    }
}
