<?php

namespace Database\Seeders;

use App\Modules\Catalogo\Services\PrepararCategoriasPosgrado;
use Illuminate\Database\Seeder;

class CategoriasPosgradoSeeder extends Seeder
{
    public function run(): void
    {
        $oficinas = app(PrepararCategoriasPosgrado::class)->ejecutar();
        $this->command?->info("Categorías preparadas para {$oficinas} oficina(s) de Posgrado.");
        if ($oficinas === 0) {
            $this->command?->warn('No se encontró una oficina con Posgrado/Postgrado en el nombre. No se inventaron oficinas ni IDs.');
        }
    }
}
