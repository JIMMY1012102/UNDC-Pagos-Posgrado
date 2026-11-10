<?php

namespace App\Modules\Sispagos\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Solo lectura: no presupone ningún ID institucional ni crea oficinas. */
final class PosgradoOfficesQuery
{
    public function ids(): Collection
    {
        if (! Schema::hasTable('tm_oficina')) {
            return collect();
        }

        return DB::table('tm_oficina')->get(['oficina_id', 'oficina_nom'])
            ->filter(static function (object $oficina): bool {
                $nombre = Str::lower(Str::ascii($oficina->oficina_nom));
                return str_contains($nombre, 'posgrado') || str_contains($nombre, 'postgrado');
            })->pluck('oficina_id');
    }
}
