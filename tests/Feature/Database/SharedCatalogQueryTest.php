<?php

namespace Tests\Feature\Database;

use App\Modules\Tienda\Queries\PublishedCatalogQuery;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SharedCatalogQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_reads_canonical_legacy_concept_and_its_office_from_shared_database(): void
    {
        // Only a minimal fictitious copy in SQLite CI: the real six tm_* tables
        // are never imported or modified by any Laravel store migration.
        Schema::create('tm_oficina', function (Blueprint $table) {
            $table->integer('oficina_id')->primary();
            $table->string('oficina_nom');
            $table->integer('estado');
        });
        Schema::create('tm_conceptos', function (Blueprint $table) {
            $table->integer('id_concepto')->primary();
            $table->string('codigo_concepto');
            $table->string('concepto');
            $table->decimal('costo', 10, 2)->nullable();
        });

        DB::table('tm_oficina')->insert([
            ['oficina_id' => 7, 'oficina_nom' => 'Posgrado ficticio', 'estado' => 1],
            ['oficina_id' => 8, 'oficina_nom' => 'Otra oficina', 'estado' => 0],
        ]);
        DB::table('tm_conceptos')->insert([
            'id_concepto' => 12,
            'codigo_concepto' => 'EJEMPLO',
            'concepto' => 'Concepto oficial ficticio',
            'costo' => 15.50,
        ]);
        DB::table('store_office_settings')->insert([
            ['legacy_office_id' => 7, 'slug' => 'posgrado', 'catalog_enabled' => 1],
            ['legacy_office_id' => 8, 'slug' => 'otra', 'catalog_enabled' => 1],
        ]);
        DB::table('store_catalog_items')->insert([
            ['legacy_office_id' => 7, 'legacy_concept_id' => 12, 'slug' => 'oficial', 'is_published' => 1],
            ['legacy_office_id' => 8, 'legacy_concept_id' => 12, 'slug' => 'no-visible', 'is_published' => 1],
        ]);

        $reader = new PublishedCatalogQuery();
        $result = $reader->forOffice(7);

        $this->assertCount(1, $result);
        $this->assertSame('Concepto oficial ficticio', $result->first()->name);
        $this->assertSame('Posgrado ficticio', $result->first()->office_name);
        $this->assertEquals(15.50, $result->first()->legacy_base_amount);
        $this->assertCount(0, $reader->forOffice(8));
    }
}
