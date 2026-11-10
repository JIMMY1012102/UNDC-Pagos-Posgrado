<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Solo datos de publicación: no modifica conceptos ni oficinas de SISPAGOS.
    private const CATEGORIAS = [
        'admission' => 'admision',
        'tuition' => 'matricula_pensiones',
        'transfers' => 'tramites',
        'certificates' => 'certificados_constancias',
        'degrees' => 'tesis_grados',
        'administrative' => 'servicios_administrativos',
    ];

    public function up(): void
    {
        $this->traducir(self::CATEGORIAS);
    }

    public function down(): void
    {
        // Revierte el vocabulario completo; también incluye publicaciones nuevas.
        $this->traducir(array_flip(self::CATEGORIAS));
    }

    private function traducir(array $categorias): void
    {
        DB::transaction(function () use ($categorias): void {
            foreach ($categorias as $anterior => $nuevo) {
                DB::table('tienda_conceptos_catalogo')
                    ->where('category', $anterior)
                    ->update(['category' => $nuevo]);
            }
        });
    }
};
