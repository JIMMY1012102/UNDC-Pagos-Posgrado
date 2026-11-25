<?php

namespace App\Modules\Pedidos\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class ActiveCartQuery
{
    public static function make(): Builder
    {
        return DB::table('tienda_carritos')->where('estado', 'active')
            ->where(fn (Builder $query) => $query->whereNull('vence_en')->orWhere('vence_en', '>', now()));
    }
}
