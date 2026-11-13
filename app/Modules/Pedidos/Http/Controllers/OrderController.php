<?php

namespace App\Modules\Pedidos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pedidos\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function quote(Request $request, OrderService $orders): JsonResponse
    {
        $data = $request->validate(['cart_id' => ['required', 'integer', 'min:1']]);
        $quote = $orders->quote((int) $request->user()->id, (int) $data['cart_id']);
        return response()->json(['success' => true, 'data' => [
            'items' => array_map(static fn ($line) => ['name' => $line['concept_name'], 'quantity' => $line['quantity'], 'total_amount' => OrderService::money($line['total_cents'])], $quote['items']),
            'total_amount' => OrderService::money($quote['total_cents']), 'currency' => 'PEN',
        ]])->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, OrderService $orders): JsonResponse
    {
        $data = $request->validate(['cart_id' => ['required', 'integer', 'min:1']]);
        return response()->json(['success' => true, 'data' => $orders->create((int) $request->user()->id, (int) $data['cart_id'])], 201)->header('Cache-Control', 'no-store');
    }

    public function modify(Request $request, string $code, OrderService $orders): JsonResponse
    {
        $data = $request->validate(['cart_token' => ['nullable', 'string', 'regex:/^[a-f0-9]{64}$/D']]);
        if (! empty($data['cart_token'])) {
            $cart = app(\App\Modules\Pedidos\Services\CartService::class)->find($data['cart_token']);
            abort_if(($cart['items_count'] ?? 0) > 0, 409, 'Ya tienes otra selección en tu carrito. Revísala antes de modificar este pedido.');
        }

        return response()->json(['success' => true, 'data' => $orders->modify((int) $request->user()->id, $code)])
            ->header('Cache-Control', 'no-store');
    }

    public function index(Request $request, OrderService $orders): JsonResponse
    {
        $data = $request->validate(['page' => ['sometimes', 'integer', 'min:1', 'max:100000']]);
        return response()->json(['success' => true, 'data' => $orders->listing((int) $request->user()->id, (int) ($data['page'] ?? 1))])->header('Cache-Control', 'no-store');
    }

    public function show(Request $request, string $code, OrderService $orders): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $orders->show((int) $request->user()->id, $code)])->header('Cache-Control', 'no-store');
    }
}
