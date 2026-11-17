<?php

namespace App\Modules\Pedidos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pedidos\Exceptions\CatalogItemUnavailable;
use App\Modules\Pedidos\Exceptions\QuantityLimitExceeded;
use App\Modules\Pedidos\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        if ($this->requiresToken($request, $token)) {
            return $this->invalidToken();
        }

        $cart = $service->find($token, $request->user()?->id);

        return $cart === null ? $this->notFound() : response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Carrito consultado.',
        ])->header('Cache-Control', 'no-store');
    }

    public function add(Request $request, CartService $service): JsonResponse
    {
        $token = $this->token($request);
        if ($this->requiresToken($request, $token)) {
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
            $cart = $service->add($token, (int) $validator->validated()['catalog_item_id'], (int) $validator->validated()['quantity'], $request->user()?->id);
        } catch (CatalogItemUnavailable|QuantityLimitExceeded $e) {
            $quantityLimit = $e instanceof QuantityLimitExceeded;

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $quantityLimit
                    ? 'Se excedió la cantidad permitida por concepto.'
                    : 'Concepto no disponible para este catálogo.',
            ], $quantityLimit ? 422 : 409)->header('Cache-Control', 'no-store');
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
        if ($this->requiresToken($request, $token)) {
            return $this->invalidToken();
        }

        $cart = $service->remove($token, $line, $request->user()?->id);

        return $cart === null ? $this->notFound() : response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Carrito actualizado.',
        ])->header('Cache-Control', 'no-store');
    }

    private function requiresToken(Request $request, ?string $token): bool
    {
        return $token === null && ($request->bearerToken() !== null || $request->user() === null);
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
