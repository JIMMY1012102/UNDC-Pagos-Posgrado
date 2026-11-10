<?php

use App\Modules\Catalogo\Services\PrepararCategoriasPosgrado;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tienda_categorias', function (Blueprint $table): void {
            $table->id();
            $table->integer('legacy_office_id');
            $table->string('codigo', 100);
            $table->string('nombre', 160);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
            $table->unique(['legacy_office_id', 'codigo'], 'uq_categoria_oficina_codigo');
            $table->unique(['id', 'legacy_office_id'], 'uq_categoria_id_oficina');
            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('legacy_office_id', 'fk_categoria_oficina_tm')
                    ->references('oficina_id')->on('tm_oficina')
                    ->restrictOnDelete()->restrictOnUpdate();
            }
        });

        Schema::table('tienda_conceptos_catalogo', function (Blueprint $table): void {
            // Relación simple para el selector de valores relacionados de phpMyAdmin.
            $table->foreignId('categoria_id')->nullable()
                ->constrained('tienda_categorias')->restrictOnDelete()->restrictOnUpdate();
            // La base impide elegir una categoría que pertenece a otra oficina.
            $table->foreign(['categoria_id', 'legacy_office_id'], 'fk_publicacion_categoria_oficina')
                ->references(['id', 'legacy_office_id'])->on('tienda_categorias')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        app(PrepararCategoriasPosgrado::class)->ejecutar();
    }

    public function down(): void
    {
        // Conservar las selecciones realizadas antes de volver al campo anterior.
        foreach (DB::table('tienda_categorias')->get(['id', 'codigo']) as $categoria) {
            DB::table('tienda_conceptos_catalogo')->where('categoria_id', $categoria->id)
                ->update(['category' => $categoria->codigo]);
        }
        Schema::table('tienda_conceptos_catalogo', function (Blueprint $table): void {
            if (DB::getDriverName() === 'sqlite') {
                $table->dropForeign(['categoria_id', 'legacy_office_id']);
            } else {
                $table->dropForeign('fk_publicacion_categoria_oficina');
            }
            $table->dropConstrainedForeignId('categoria_id');
        });
        Schema::dropIfExists('tienda_categorias');
    }
};
