<?php

namespace Tests\Feature\Tienda;

use App\Modules\Tienda\Services\CatalogService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

final class CatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    private function seedFictitiousLegacyCatalog(): void
    {
        // Isolated, synthetic legacy schema. Never import real student records.
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
            ['oficina_id' => 7, 'oficina_nom' => 'Posgrado DEMO', 'estado' => 1],
            ['oficina_id' => 8, 'oficina_nom' => 'Inactiva DEMO', 'estado' => 0],
        ]);

        DB::table('tm_conceptos')->insert([
            ['id_concepto' => 12, 'codigo_concepto' => 'DEMO-001', 'concepto' => 'Concepto ficticio', 'costo' => 10.00],
            ['id_concepto' => 13, 'codigo_concepto' => 'DEMO-002', 'concepto' => 'No publicado', 'costo' => 12.00],
        ]);

        DB::table('store_office_settings')->insert([
            ['legacy_office_id' => 7, 'slug' => 'posgrado-demo', 'catalog_enabled' => 1],
            ['legacy_office_id' => 8, 'slug' => 'inactiva-demo', 'catalog_enabled' => 1],
        ]);

        DB::table('store_catalog_items')->insert([
            ['legacy_office_id' => 7, 'legacy_concept_id' => 12, 'slug' => 'visible', 'is_published' => 1],
            ['legacy_office_id' => 7, 'legacy_concept_id' => 13, 'slug' => 'oculto', 'is_published' => 0],
            ['legacy_office_id' => 8, 'legacy_concept_id' => 12, 'slug' => 'inactivo', 'is_published' => 1],
        ]);
    }

    public function test_lists_only_published_concepts_as_reference_not_as_payable_products(): void
    {
        $this->seedFictitiousLegacyCatalog();

        $result = app(CatalogService::class)->listForOffice(7);

        $this->assertSame(7, $result['legacy_office_id']);
        $this->assertSame('reference_only', $result['pricing_status']);
        $this->assertFalse($result['purchase_enabled']);
        $this->assertSame(1, $result['count']);
        $this->assertSame('DEMO-001', $result['items'][0]['code']);
        $this->assertSame('Concepto ficticio', $result['items'][0]['name']);
        $this->assertSame('Posgrado DEMO', $result['items'][0]['office']['name']);
    }

    public function test_inactive_office_returns_no_items(): void
    {
        $this->seedFictitiousLegacyCatalog();

        $result = app(CatalogService::class)->listForOffice(8);

        $this->assertSame(0, $result['count']);
        $this->assertSame([], $result['items']);
    }

    public function test_rejects_invalid_office_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CatalogService::class)->listForOffice(0);
    }
}
