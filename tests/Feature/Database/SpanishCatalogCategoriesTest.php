<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpanishCatalogCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_preserves_publications_across_offices_and_can_be_reversed(): void
    {
        $categories = [
            'admission' => 'admision',
            'tuition' => 'matricula_pensiones',
            'transfers' => 'tramites',
            'certificates' => 'certificados_constancias',
            'degrees' => 'tesis_grados',
            'administrative' => 'servicios_administrativos',
        ];
        $before = [];
        foreach ([7, 9] as $office) {
            foreach (array_merge(array_keys($categories), array_values($categories), [null, '', 'personalizada']) as $index => $category) {
                $id = DB::table('tienda_conceptos_catalogo')->insertGetId([
                    'legacy_office_id' => $office,
                    'legacy_concept_id' => 100 + $index,
                    'slug' => 'concepto-'.$index,
                    'category' => $category,
                    'is_published' => $index % 2,
                    'description' => 'Descripción conservada',
                ]);
                $before[$id] = (array) DB::table('tienda_conceptos_catalogo')->find($id);
            }
        }
        $migration = require database_path('migrations/2026_11_10_000001_translate_catalog_categories_to_spanish.php');
        $migration->up();
        $expected = [];
        foreach ($before as $id => $row) {
            $row['category'] = $categories[$row['category']] ?? $row['category'];
            $expected[$id] = $row;
            $this->assertSame($row, (array) DB::table('tienda_conceptos_catalogo')->find($id));
        }
        $this->assertSame(count($before), DB::table('tienda_conceptos_catalogo')->count());
        $migration->up(); // Repetir no altera categorías ya traducidas.
        foreach ($expected as $id => $row) {
            $this->assertSame($row, (array) DB::table('tienda_conceptos_catalogo')->find($id));
        }
        $migration->down();
        $reverse = array_flip($categories);
        foreach ($expected as $id => $row) {
            $row['category'] = $reverse[$row['category']] ?? $row['category'];
            $this->assertSame($row, (array) DB::table('tienda_conceptos_catalogo')->find($id));
        }
        $migration->up();
        foreach ($expected as $id => $row) {
            $this->assertSame($row, (array) DB::table('tienda_conceptos_catalogo')->find($id));
        }
    }
}
