<?php

namespace App\Modules\Acceso\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Acceso\Services\BuyerProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class BuyerProfileController extends Controller
{
    public function show(Request $request, BuyerProfileService $service): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $service->find((int) $request->user()->id),
            'message' => 'Perfil del comprador consultado.',
        ])->header('Cache-Control', 'no-store');
    }

    public function update(Request $request, BuyerProfileService $service): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_names' => ['required', 'string', 'max:160'],
            'last_names' => ['required', 'string', 'max:160'],
            'document_type' => ['required', 'string', 'max:30'],
            'document_number' => ['required', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:254'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 'data' => null,
                'message' => 'Datos personales no válidos.',
                'errors' => $validator->errors(),
            ], 422)->header('Cache-Control', 'no-store');
        }

        return response()->json([
            'success' => true,
            'data' => $service->save((int) $request->user()->id, $validator->validated()),
            'message' => 'Perfil del comprador guardado.',
        ])->header('Cache-Control', 'no-store');
    }
}
