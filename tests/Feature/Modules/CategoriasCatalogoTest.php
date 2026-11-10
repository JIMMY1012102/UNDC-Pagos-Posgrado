<?php

namespace Tests\Feature\Modules;

use Database\Seeders\CategoriasPosgradoSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CategoriasCatalogoTest extends TestCase
{
    use RefreshDatabase;

    private function preparar(): void
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
            ['oficina_id' => 91, 'oficina_nom' => 'Escuela de Postgrado', 'estado' => 1],
            ['oficina_id' => 92, 'oficina_nom' => 'Centro de Idiomas', 'estado' => 1],
        ]);
    }

    private function publicacion(?string $category = null): int
    {
        DB::table('tm_conceptos')->insert([
            'id_concepto' => 25, 'codigo_concepto' => '6.22',
            'concepto' => 'Constancia de similitud de Posgrado (Turnitin)', 'costo' => 40,
        ]);
        DB::table('tienda_configuracion_oficinas')->insert([
            'legacy_office_id' => 91, 'slug' => 'posgrado', 'catalog_enabled' => true,
        ]);
        return DB::table('tienda_conceptos_catalogo')->insertGetId([
            'legacy_office_id' => 91, 'legacy_concept_id' => 25, 'slug' => 'similitud',
            'category' => $category, 'description' => 'Descripción conservada', 'is_published' => true,
        ]);
    }

    public function test_six_predefined_options_exist_without_concepts_and_seeding_is_repeatable(): void
    {
        $this->preparar();
        $this->seed(CategoriasPosgradoSeeder::class);
        $expected = [
            'admision' => 'Admisión',
            'matricula_pensiones' => 'Matrícula y pensiones',
            'tramites' => 'Trámites académicos',
            'certificados_constancias' => 'Certificados y constancias',
            'tesis_grados' => 'Tesis y grados',
            'servicios_administrativos' => 'Servicios administrativos',
        ];
        $this->assertSame($expected, DB::table('tienda_categorias')->orderBy('orden')->pluck('nombre', 'codigo')->all());
        $before = DB::table('tienda_categorias')->orderBy('id')->get()->toJson();
        $this->seed(CategoriasPosgradoSeeder::class);
        $this->assertSame($before, DB::table('tienda_categorias')->orderBy('id')->get()->toJson());
        $this->assertSame(0, DB::table('tm_conceptos')->count());
        $this->assertSame(0, DB::table('tienda_conceptos_catalogo')->count());
        $this->assertSame(0, DB::table('tienda_categorias')->where('legacy_office_id', 92)->count());
    }

    public function test_migration_backfills_known_categories_and_rollback_preserves_selected_codes(): void
    {
        $this->preparar();
        $id = $this->publicacion('certificados_constancias');
        $migration = require database_path('migrations/2026_11_10_000002_create_tienda_categorias.php');
        $migration->down();
        $migration->up(); // Reproduce una actualización con datos ya existentes.
        $categoryId = DB::table('tienda_conceptos_catalogo')->where('id', $id)->value('categoria_id');
        $this->assertNotNull($categoryId);
        $this->assertSame('certificados_constancias', DB::table('tienda_categorias')->where('id', $categoryId)->value('codigo'));
        $this->assertSame('Descripción conservada', DB::table('tienda_conceptos_catalogo')->where('id', $id)->value('description'));
        DB::table('tienda_conceptos_catalogo')->where('id', $id)->update(['category' => null]);
        $migration->down();
        $this->assertFalse(Schema::hasColumn('tienda_conceptos_catalogo', 'categoria_id'));
        $this->assertSame('certificados_constancias', DB::table('tienda_conceptos_catalogo')->where('id', $id)->value('category'));
        $migration->up();
        $this->assertSame(6, DB::table('tienda_categorias')->count());
    }

    public function test_selected_category_takes_precedence_and_turnitin_can_be_a_certificate(): void
    {
        $this->preparar();
        $id = $this->publicacion('tesis_grados');
        $this->seed(CategoriasPosgradoSeeder::class);
        $categoryId = DB::table('tienda_categorias')->where('codigo', 'certificados_constancias')->value('id');
        DB::table('tienda_conceptos_catalogo')->where('id', $id)->update(['categoria_id' => $categoryId]);
        $this->seed(CategoriasPosgradoSeeder::class); // No deshace una selección manual.
        $this->getJson('/api/v1/tienda/catalogo?office_id=91')->assertOk()
            ->assertJsonPath('data.0.category', 'certificados_constancias')
            ->assertJsonPath('data.0.category_name', 'Certificados y constancias')
            ->assertJsonPath('data.0.categoria_id', (int) $categoryId)
            ->assertJsonPath('data.0.checkout_enabled', false);
    }

    public function test_no_category_is_assigned_to_a_blank_publication_by_guessing(): void
    {
        $this->preparar();
        $id = $this->publicacion();
        $this->seed(CategoriasPosgradoSeeder::class);
        $this->assertNull(DB::table('tienda_conceptos_catalogo')->where('id', $id)->value('categoria_id'));
    }

    public function test_category_from_another_office_is_rejected_by_the_database(): void
    {
        $this->preparar();
        $id = $this->publicacion();
        $categoryId = DB::table('tienda_categorias')->insertGetId([
            'legacy_office_id' => 92, 'codigo' => 'certificados_constancias', 'nombre' => 'Certificados de Idiomas',
        ]);
        $this->expectException(QueryException::class);
        DB::table('tienda_conceptos_catalogo')->where('id', $id)->update(['categoria_id' => $categoryId]);
    }

    public function test_referenced_categories_cannot_be_deleted(): void
    {
        $this->preparar();
        $id = $this->publicacion();
        $this->seed(CategoriasPosgradoSeeder::class);
        $categoryId = DB::table('tienda_categorias')->where('codigo', 'admision')->value('id');
        DB::table('tienda_conceptos_catalogo')->where('id', $id)->update(['categoria_id' => $categoryId]);
        $this->expectException(QueryException::class);
        DB::table('tienda_categorias')->where('id', $categoryId)->delete();
    }
}
