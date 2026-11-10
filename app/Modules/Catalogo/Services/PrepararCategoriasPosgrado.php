<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Sispagos\Queries\PosgradoOfficesQuery;
use Illuminate\Support\Facades\DB;

/** Predefine opciones de clasificación; no crea ni publica conceptos. */
final class PrepararCategoriasPosgrado
{
    public const CATEGORIAS = [
        'admision' => 'Admisión',
        'matricula_pensiones' => 'Matrícula y pensiones',
        'tramites' => 'Trámites académicos',
        'certificados_constancias' => 'Certificados y constancias',
        'tesis_grados' => 'Tesis y grados',
        'servicios_administrativos' => 'Servicios administrativos',
    ];

    private const ANTERIORES = [
        'admision' => 'admission',
        'matricula_pensiones' => 'tuition',
        'tramites' => 'transfers',
        'certificados_constancias' => 'certificates',
        'tesis_grados' => 'degrees',
        'servicios_administrativos' => 'administrative',
    ];

    public function __construct(private readonly PosgradoOfficesQuery $oficinas) {}

    public function ejecutar(): int
    {
        $oficinas = $this->oficinas->ids();
        DB::transaction(function () use ($oficinas): void {
            foreach ($oficinas as $oficinaId) {
                $orden = 0;
                foreach (self::CATEGORIAS as $codigo => $nombre) {
                    DB::table('tienda_categorias')->insertOrIgnore([
                        'legacy_office_id' => $oficinaId, 'codigo' => $codigo,
                        'nombre' => $nombre, 'orden' => ++$orden,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $categoriaId = DB::table('tienda_categorias')
                        ->where('legacy_office_id', $oficinaId)->where('codigo', $codigo)->value('id');
                    DB::table('tienda_conceptos_catalogo')
                        ->where('legacy_office_id', $oficinaId)->whereNull('categoria_id')
                        ->whereIn('category', [$codigo, self::ANTERIORES[$codigo]])
                        ->update(['categoria_id' => $categoriaId]);
                }
            }
        });
        return $oficinas->count();
    }
}
