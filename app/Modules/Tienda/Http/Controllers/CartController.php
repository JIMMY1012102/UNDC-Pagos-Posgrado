<?php

namespace App\Modules\Tienda\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tienda\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

final class CartController extends Controller
{
    public function create(CartService $service): JsonResponse
    {
        $result = $service->create();

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Carrito de precompra creado.',
        ], 201)->header('Cache-Control', 'no-store');
    }

    public function show(Request $request, CartService $service): JsonResponse
    {
        $token = $this->token($request);
        if ($token === null) {
            return $this->invalidToken();
        }

        $cart = $service->find($token);

        return $cart === null ? $this->notFound() : response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Carrito consultado.',
        ])->header('Cache-Control', 'no-store');
    }

    public function add(Request $request, CartService $service): JsonResponse
    {
        $token = $this->token($request);
        if ($token === null) {
            return $this->invalidToken();
        }

        $validator = Validator::make($request->all(), [
            'catalog_item_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'between:1,20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Datos de carrito no válidos.',
                'errors' => $validator->errors(),
            ], 422)->header('Cache-Control', 'no-store');
        }

        try {
            $cart = $service->add($token, (int) $validator->validated()['catalog_item_id'], (int) $validator->validated()['quantity']);
        } catch (RuntimeException $e) {
            if (! in_array($e->getMessage(), ['CATALOG_ITEM_UNAVAILABLE', 'QUANTITY_LIMIT'], true)) {
                throw $e;
            }

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage() === 'QUANTITY_LIMIT'
                    ? 'Se excedió la cantidad permitida por concepto.'
                    : 'Concepto no disponible para este catálogo.',
            ], $e->getMessage() === 'QUANTITY_LIMIT' ? 422 : 409)->header('Cache-Control', 'no-store');
        }

        return $cart === null ? $this->notFound() : response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Concepto agregado al carrito.',
        ])->header('Cache-Control', 'no-store');
    }

    public function remove(Request $request, int $line, CartService $service): JsonResponse
    {
        $token = $this->token($request);
        if ($token === null) {
            return $this->invalidToken();
        }

        $cart = $service->remove($token, $line);

        return $cart === null ? $this->notFound() : response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Carrito actualizado.',
        ])->header('Cache-Control', 'no-store');
    }

    private function token(Request $request): ?string
    {
        $token = $request->bearerToken();

        return is_string($token) && preg_match('/^[a-f0-9]{64}$/D', $token) ? $token : null;
    }

    private function invalidToken(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Se requiere un token válido de carrito.',
        ], 401)->header('Cache-Control', 'no-store');
    }

    private function notFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Carrito no disponible.',
        ], 404)->header('Cache-Control', 'no-store');
    }
}
