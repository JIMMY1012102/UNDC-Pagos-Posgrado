<?php

namespace App\Modules\Tienda\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tienda\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CatalogController extends Controller
{
    public function __invoke(Request $request, CatalogService $service): JsonResponse
    {
        // The public API is explicitly scoped to an enabled office. Until
        // Posgrado's official office mapping is approved, no slug is assumed.
        $office = $request->query('office_id');

        if (! is_string($office) || ! preg_match('/^[1-9][0-9]*$/', $office) || filter_var($office, FILTER_VALIDATE_INT) === false) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Debe indicar un identificador de oficina válido.',
                'errors' => ['office_id' => ['El campo office_id debe ser un entero positivo.']],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $service->forOffice((int) $office)->values()->all(),
            'message' => 'Catálogo consultado correctamente.',
        ]);
    }
}
