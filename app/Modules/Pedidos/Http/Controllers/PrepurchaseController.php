<?php

namespace App\Modules\Pedidos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Usuarios\Services\BuyerProfileService;
use App\Modules\Pedidos\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class PrepurchaseController extends Controller
{
    public function claim(Request $request, CartService $carts): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cart_token' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/D'],
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Token de carrito inválido.'], 422)->header('Cache-Control', 'no-store');
        }

        $cart = $carts->claim($validator->validated()['cart_token'], (int) $request->user()->id);

        return $cart === null
            ? response()->json(['success' => false, 'message' => 'Carrito vencido o ya vinculado.'], 404)->header('Cache-Control', 'no-store')
            : response()->json(['success' => true, 'data' => $cart, 'message' => 'Carrito vinculado a la cuenta.'])->header('Cache-Control', 'no-store');
    }

    public function summary(Request $request, CartService $carts, BuyerProfileService $profiles): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $complete = $profiles->isComplete($userId);
        $cart = $carts->forBuyer($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'profile_complete' => $complete,
                'profile' => $profiles->find($userId),
                'cart' => $cart,
                'next_step' => ! $complete ? 'complete_profile' : ($cart === null || $cart['items_count'] === 0 ? 'cart' : 'review'),
                'checkout_enabled' => false,
                'total_amount' => null,
                'pricing_status' => 'pending_institutional_approval',
            ],
            'message' => 'Precompra pendiente de autorización de cobro.',
        ])->header('Cache-Control', 'no-store');
    }
}
