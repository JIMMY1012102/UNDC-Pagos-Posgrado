<?php

namespace Tests\Feature\Modules;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    private function prepareFixture(): void
    {
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
            ['legacy_office_id' => 7, 'slug' => 'posgrado-demo', 'catalog_enabled' => 1],
            ['legacy_office_id' => 8, 'slug' => 'inactiva', 'catalog_enabled' => 1],
        ]);
        DB::table('tienda_conceptos_catalogo')->insert([
            ['legacy_office_id' => 7, 'legacy_concept_id' => 12, 'slug' => 'demo', 'is_published' => 1],
            ['legacy_office_id' => 7, 'legacy_concept_id' => 13, 'slug' => 'oculto', 'is_published' => 0],
            ['legacy_office_id' => 8, 'legacy_concept_id' => 12, 'slug' => 'suspendido', 'is_published' => 1],
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
            ->assertJsonPath('data.0.pricing_status', 'pending_institutional_approval')
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
            ->update(['catalog_enabled' => false]);
        $this->getJson('/api/v1/tienda/catalogo/oficinas')
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_category_is_read_from_publication_and_not_inferred_from_price(): void
    {
        $this->prepareFixture();
        DB::table('tienda_conceptos_catalogo')->where('slug', 'demo')
            ->update(['category' => 'admision']);
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
                    ->update(['category' => $stored]);
                $this->getJson('/api/v1/tienda/catalogo?office_id=7')
                    ->assertOk()->assertJsonPath('data.0.category', $new);
            }
        }
        foreach ([null, '', 'personalizada'] as $category) {
            DB::table('tienda_conceptos_catalogo')->where('slug', 'demo')
                ->update(['category' => $category]);
            $this->getJson('/api/v1/tienda/catalogo?office_id=7')
                ->assertOk()->assertJsonPath('data.0.category', $category);
        }
    }
}

