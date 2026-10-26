<?php

namespace App\Modules\Tienda\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tienda\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller prepared for an approved read-only catalog route.
 *
 * Intentionally NOT registered in routes: approval of publication, prices
 * and access policy is still pending with OTI/Tesorería.
 */
final class CatalogController extends Controller
{
    public function index(Request $request, CatalogService $catalog, int $legacyOfficeId): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        abort_if($legacyOfficeId <= 0, 404);

        return response()->json(
            $catalog->listForOffice($legacyOfficeId, (int) ($validated['limit'] ?? 50))
        );
    }
}
