<?php

namespace Tests\Feature\Modules;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    private function prepareFixture(): void
    {
        Cache::flush();
        Schema::create('tm_oficina', function (Blueprint $table): void {
            $table->integer('oficina_id')->primary();
            $table->string('oficina_nom');
            $table->integer('estado');
        });

        Schema::create('tm_conceptos', function (Blueprint $table): void {
            $table->integer('id_concepto')->primary();
            $table->string('codigo_concepto');
            $table->string('concepto');
            $table->decimal('costo', 10, 2)->nullable();
        });

        DB::table('tm_oficina')->insert([
            ['oficina_id' => 7, 'oficina_nom' => 'Posgrado ficticio', 'estado' => 1],
            ['oficina_id' => 8, 'oficina_nom' => 'Oficina suspendida', 'estado' => 0],
        ]);
        DB::table('tm_conceptos')->insert([
            ['id_concepto' => 12, 'codigo_concepto' => 'DEMO-OTI-001', 'concepto' => 'Concepto ficticio', 'costo' => 10.00],
            ['id_concepto' => 13, 'codigo_concepto' => 'NO-PUBLICADO', 'concepto' => 'Concepto oculto', 'costo' => 25.00],
        ]);
        DB::table('tienda_configuracion_oficinas')->insert([
            ['legacy_office_id' => 7, 'slug' => 'posgrado-demo', 'catalogo_habilitado' => 1],
            ['legacy_office_id' => 8, 'slug' => 'inactiva', 'catalogo_habilitado' => 1],
        ]);
        DB::table('tienda_conceptos_catalogo')->insert([
            ['legacy_office_id' => 7, 'legacy_concept_id' => 12, 'slug' => 'demo', 'esta_publicado' => 1],
            ['legacy_office_id' => 7, 'legacy_concept_id' => 13, 'slug' => 'oculto', 'esta_publicado' => 0],
            ['legacy_office_id' => 8, 'legacy_concept_id' => 12, 'slug' => 'suspendido', 'esta_publicado' => 1],
        ]);
    }

    public function test_catalog_rejects_missing_or_invalid_office(): void
    {
        $this->getJson('/api/v1/tienda/catalogo')->assertStatus(422)->assertJsonPath('success', false);
        $this->getJson('/api/v1/tienda/catalogo?office_id=abc')->assertStatus(422);
        $this->getJson('/api/v1/tienda/catalogo?office_id=0')->assertStatus(422);
    }

    public function test_catalog_returns_only_published_concepts_without_unapproved_prices(): void
    {
        $this->prepareFixture();
        $this->getJson('/api/v1/tienda/catalogo?office_id=7')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'DEMO-OTI-001')
            ->assertJsonPath('data.0.pricing_status', 'official')
            ->assertJsonPath('data.0.reference_amount', '10.00')
            ->assertJsonPath('data.0.checkout_enabled', false)
            ->assertJsonMissingPath('data.0.amount')
            ->assertJsonMissingPath('data.0.legacy_base_amount');
    }

    public function test_catalog_excludes_inactive_or_unknown_office(): void
    {
        $this->prepareFixture();
        $this->getJson('/api/v1/tienda/catalogo?office_id=8')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/tienda/catalogo?office_id=999')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_catalog_does_not_accept_post(): void
    {
        $this->postJson('/api/v1/tienda/catalogo', [])->assertStatus(405);
    }

    public function test_office_selector_returns_only_offices_with_available_publications(): void
    {
        $this->prepareFixture();
        $this->getJson('/api/v1/tienda/catalogo/oficinas')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 7)
            ->assertJsonPath('data.0.name', 'Posgrado ficticio');

        DB::table('tienda_configuracion_oficinas')->where('legacy_office_id', 7)
            ->update(['catalogo_habilitado' => false]);
        $this->travel(121)->seconds();
        $this->getJson('/api/v1/tienda/catalogo/oficinas')
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_category_is_read_from_publication_and_not_inferred_from_price(): void
    {
        $this->prepareFixture();
        DB::table('tienda_conceptos_catalogo')->where('slug', 'demo')
            ->update(['categoria_codigo' => 'admision']);
        $this->getJson('/api/v1/tienda/catalogo?office_id=7')
            ->assertOk()->assertJsonPath('data.0.category', 'admision')
            ->assertJsonPath('data.0.checkout_enabled', false)
            ->assertJsonMissingPath('data.0.amount');
    }
    public function test_api_returns_spanish_categories_before_and_after_data_migration(): void
    {
        $this->prepareFixture();
        $categories = [
            'admission' => 'admision',
            'tuition' => 'matricula_pensiones',
            'transfers' => 'tramites',
            'certificates' => 'certificados_constancias',
            'degrees' => 'tesis_grados',
            'administrative' => 'servicios_administrativos',
        ];
        foreach ($categories as $old => $new) {
            foreach ([$old, $new] as $stored) {
                DB::table('tienda_conceptos_catalogo')->where('slug', 'demo')
                    ->update(['categoria_codigo' => $stored]);
                Cache::forget('catalogo.publico.v2.oficina.7');
                $this->getJson('/api/v1/tienda/catalogo?office_id=7')
                    ->assertOk()->assertJsonPath('data.0.category', $new);
            }
        }
        foreach ([null, '', 'personalizada'] as $category) {
            DB::table('tienda_conceptos_catalogo')->where('slug', 'demo')
                ->update(['categoria_codigo' => $category]);
            Cache::forget('catalogo.publico.v2.oficina.7');
            $this->getJson('/api/v1/tienda/catalogo?office_id=7')
                ->assertOk()->assertJsonPath('data.0.category', $category);
        }
    }

    public function test_catalog_listing_reads_one_join_and_one_tariff_query(): void
    {
        $this->prepareFixture();
        $this->assertSame('array', config('cache.default'));
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $this->getJson('/api/v1/tienda/catalogo?office_id=7')->assertOk()
                ->assertJsonPath('data.0.reference_amount', '10.00');
            $queries = DB::getQueryLog();
            $this->assertNotEmpty($queries);
            $this->assertLessThanOrEqual(2, count($queries));
            $this->assertCount(1, array_filter($queries, fn ($q) => str_contains($q['query'], 'join')));
            $this->assertCount(1, array_filter($queries, fn ($q) => str_contains($q['query'], 'tienda_versiones_tarifas')));
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_references_read_one_join_and_one_tariff_query_without_page_limit(): void
    {
        $this->prepareFixture();
        // More than the public page limit, including a concept that cannot be quoted.
        foreach (range(1, 55) as $index) {
            DB::table('tm_conceptos')->insert([
                'id_concepto' => 100 + $index, 'codigo_concepto' => 'EXTRA-'.$index,
                'concepto' => 'Concepto ficticio', 'costo' => 10,
            ]);
            DB::table('tienda_conceptos_catalogo')->insert([
                'legacy_office_id' => 7, 'legacy_concept_id' => 100 + $index,
                'slug' => 'extra-'.$index, 'esta_publicado' => true,
                'requiere_verificacion_elegibilidad' => $index === 55,
            ]);
        }
        $ids = DB::table('tienda_conceptos_catalogo')->where('esta_publicado', true)
            ->where('legacy_office_id', 7)->pluck('id')->all();
        $service = app(\App\Modules\Catalogo\Services\CatalogService::class);
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $references = $service->referencesFor([...$ids, 999999]);
            $queries = DB::getQueryLog();
            $this->assertCount(56, $references);
            $this->assertSame(['name' => 'Concepto ficticio', 'reference_amount' => '10.00'], $references[$ids[55]]);
            $this->assertLessThanOrEqual(2, count($queries));
            $this->assertCount(1, array_filter($queries, fn ($q) => str_contains($q['query'], 'join')));
            $this->assertCount(1, array_filter($queries, fn ($q) => str_contains($q['query'], 'tienda_versiones_tarifas')));
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_listing_and_references_use_current_approved_tariff_and_keep_ineligible_reference(): void
    {
        $this->prepareFixture();
        $id = (int) DB::table('tienda_conceptos_catalogo')->where('slug', 'demo')->value('id');
        DB::table('tienda_versiones_tarifas')->insert([
            'concepto_catalogo_id' => $id, 'monto' => '23.45', 'moneda' => 'PEN',
            'esta_aprobado' => true, 'referencia_aprobacion' => 'DEMO-APPROVAL',
            'vigente_desde' => now()->subDay(),
        ]);
        $service = app(\App\Modules\Catalogo\Services\CatalogService::class);
        $this->assertSame('23.45', $service->forOffice(7)->first()['reference_amount']);
        $this->assertSame('23.45', $service->referencesFor([$id])[$id]['reference_amount']);
        DB::table('tienda_conceptos_catalogo')->where('id', $id)->update(['requiere_verificacion_elegibilidad' => true]);
        $this->assertSame('10.00', $service->referencesFor([$id])[$id]['reference_amount']);
        Cache::forget('catalogo.publico.v2.oficina.7');
        $this->assertFalse($service->forOffice(7)->first()['orderable']);
    }
    public function test_second_public_request_uses_array_cache_without_catalog_queries(): void
    {
        $this->prepareFixture();
        $this->assertSame('array', config('cache.default'));
        $this->getJson('/api/v1/tienda/catalogo/oficinas')->assertOk()->assertJsonCount(1, 'data');
        $this->assertIsArray(Cache::get('catalogo.publico.v2.oficinas'));
        $this->getJson('/api/v1/tienda/catalogo?office_id=7')->assertOk()->assertJsonCount(1, 'data');
        $this->assertIsArray(Cache::get('catalogo.publico.v2.oficina.7'));
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $this->getJson('/api/v1/tienda/catalogo/oficinas')->assertOk()->assertJsonCount(1, 'data');
            $this->getJson('/api/v1/tienda/catalogo?office_id=7')->assertOk()
                ->assertJsonPath('data.0.reference_amount', '10.00');
            $this->assertCount(0, DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
        // A different office must never reuse office 7's entries.
        $this->getJson('/api/v1/tienda/catalogo?office_id=8')->assertOk()->assertJsonCount(0, 'data');
        DB::table('tienda_conceptos_catalogo')->where('slug', 'demo')->update(['esta_publicado' => false]);
        $this->getJson('/api/v1/tienda/catalogo?office_id=7')->assertJsonCount(1, 'data');
        $this->travel(121)->seconds();
        $this->getJson('/api/v1/tienda/catalogo?office_id=7')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/tienda/catalogo/oficinas')->assertJsonCount(0, 'data');
    }

    public function test_checkout_status_is_added_after_cache_and_quotes_always_read_current_tariff(): void
    {
        $this->prepareFixture();
        $id = (int) DB::table('tienda_conceptos_catalogo')->where('slug', 'demo')->value('id');
        $this->getJson('/api/v1/tienda/catalogo?office_id=7')->assertOk()
            ->assertJsonPath('data.0.checkout_enabled', false);
        $cachedItems = Cache::get('catalogo.publico.v2.oficina.7');
        $this->assertIsArray($cachedItems);
        $cached = $cachedItems[0];
        $this->assertArrayNotHasKey('checkout_enabled', $cached);
        $this->assertArrayNotHasKey('pricing_status', $cached);
        config(['payments.payme_enabled' => true]); // Test configuration only; no payment action.
        DB::table('tienda_versiones_tarifas')->insert([
            'concepto_catalogo_id' => $id, 'monto' => '23.45', 'moneda' => 'PEN',
            'esta_aprobado' => true, 'referencia_aprobacion' => 'DEMO-APPROVAL',
            'vigente_desde' => now()->subDay(),
        ]);
        $this->getJson('/api/v1/tienda/catalogo?office_id=7')->assertOk()
            ->assertJsonPath('data.0.checkout_enabled', true)
            ->assertJsonPath('data.0.reference_amount', '10.00');
        $quotes = app(\App\Modules\Catalogo\Services\OrderQuoteService::class);
        $this->assertSame(2345, $quotes->line($id, 1)['unit_cents']);
        $this->assertSame(4690, $quotes->lines([$id], 2)[$id]['total_cents']);
        $references = app(\App\Modules\Catalogo\Services\CatalogService::class)->referencesFor([$id]);
        $this->assertSame('23.45', $references[$id]['reference_amount']);
        $this->travel(121)->seconds();
        $this->getJson('/api/v1/tienda/catalogo?office_id=7')->assertOk()
            ->assertJsonPath('data.0.reference_amount', '23.45');
    }
}
