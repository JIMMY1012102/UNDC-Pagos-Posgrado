<?php

namespace App\Modules\Pedidos\Services;

use App\Modules\Catalogo\Services\OrderQuoteService;
use App\Modules\Usuarios\Services\BuyerProfileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class OrderService
{
    public function __construct(private readonly OrderQuoteService $prices, private readonly BuyerProfileService $profiles) {}

    public function quote(int $userId, int $cartId): array
    {
        $cart = DB::table('tienda_carritos')->where('id', $cartId)->where('user_id', $userId)
            ->where('status', 'active')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->first();
        abort_unless($cart, 404);
        $lines = DB::table('tienda_detalles_carrito')->where('cart_id', $cartId)->orderBy('id')->get();
        $items = []; $total = 0;
        foreach ($lines as $line) {
            $item = $line->beneficiary_id === null ? $this->prices->line((int) $line->catalog_item_id, (int) $line->quantity) : null;
            if (! $item) {
                throw ValidationException::withMessages(['tariff' => 'Uno o más conceptos no tienen un importe oficial válido o requieren validación académica. No se generó el pedido.']);
            }
            $total += $item['total_cents'];
            $items[] = $item;
        }
        if (! $items || count($items) > 50 || $total > 99999999999999) {
            throw ValidationException::withMessages(['cart' => 'El carrito está vacío o supera el límite permitido.']);
        }
        return ['items' => $items, 'total_cents' => $total];
    }

    public static function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function create(int $userId, int $cartId): array
    {
        return DB::transaction(function () use ($userId, $cartId): array {
            // Serializar doble clic/reintentos sobre el mismo carrito.
            $cart = DB::table('tienda_carritos')->where('id', $cartId)->where('user_id', $userId)->lockForUpdate()->first();
            abort_unless($cart, 404);
            $existing = DB::table('tienda_pedidos')->where('cart_id', $cartId)->where('user_id', $userId)->first();
            if ($existing) {
                return $this->show($userId, $existing->code);
            }
            if (! $this->profiles->isComplete($userId)) {
                throw ValidationException::withMessages(['profile' => 'Completa tus datos antes de generar el pedido.']);
            }
            $quote = $this->quote($userId, $cartId);
            $profile = $this->profiles->find($userId);
            $email = $profile['contact_email'] ?: DB::table('users')->where('id', $userId)->value('email');
            $now = now();
            $code = 'UNDC-'.Str::upper((string) Str::ulid());
            $id = DB::table('tienda_pedidos')->insertGetId([
                'code' => $code, 'user_id' => $userId, 'cart_id' => $cartId,
                'status' => 'pending', 'currency' => 'PEN', 'total_amount' => self::money($quote['total_cents']),
                'buyer_first_names' => $profile['first_names'], 'buyer_last_names' => $profile['last_names'],
                'buyer_document_type' => $profile['document_type'], 'buyer_document_number' => $profile['document_number'],
                'buyer_email' => $email, 'buyer_phone' => $profile['phone'],
                'expires_at' => $now->copy()->addHours(24), 'created_at' => $now, 'updated_at' => $now,
            ]);
            foreach ($quote['items'] as $line) {
                $data = $line;
                unset($data['unit_cents'], $data['total_cents']);
                DB::table('tienda_detalles_pedido')->insert($data + [
                    'order_id' => $id, 'unit_amount' => self::money($line['unit_cents']),
                    'total_amount' => self::money($line['total_cents']),
                    'beneficiary_first_names' => $profile['first_names'], 'beneficiary_last_names' => $profile['last_names'],
                    'beneficiary_document_type' => $profile['document_type'], 'beneficiary_document_number' => $profile['document_number'],
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            DB::table('tienda_historial_estados_pedido')->insert([
                'order_id' => $id, 'actor_user_id' => $userId, 'to_status' => 'pending',
                'reason' => 'Pedido generado con importes oficiales verificados. No acredita pago ni inicia trámite académico.', 'occurred_at' => $now,
            ]);
            DB::table('tienda_carritos')->where('id', $cartId)->update(['status' => 'converted', 'updated_at' => $now]);
            return $this->show($userId, $code);
        });
    }

    public function show(int $userId, string $code): array
    {
        $order = DB::table('tienda_pedidos')->where('user_id', $userId)->where('code', $code)->first();
        abort_unless($order, 404);
        $this->expire((int) $order->id, $userId);
        $order = DB::table('tienda_pedidos')->where('id', $order->id)->first();
        return [
            'code' => $order->code, 'status' => $order->status, 'currency' => $order->currency,
            'total_amount' => $order->total_amount, 'created_at' => $order->created_at, 'expires_at' => $order->expires_at,
            'paid_at' => $order->paid_at, 'buyer_name' => $order->buyer_first_names.' '.$order->buyer_last_names,
            'buyer_email' => $order->buyer_email, 'payment_enabled' => false,
            'items' => DB::table('tienda_detalles_pedido')->where('order_id', $order->id)->orderBy('id')
                ->get(['concept_name', 'concept_code', 'quantity', 'unit_amount', 'total_amount'])->all(),
            'history' => DB::table('tienda_historial_estados_pedido')->where('order_id', $order->id)->orderBy('id')
                ->get(['to_status', 'reason', 'occurred_at'])->all(),
        ];
    }

    public function listing(int $userId, int $page): array
    {
        $query = DB::table('tienda_pedidos')->where('user_id', $userId);
        $total = (clone $query)->count();
        $codes = $query->latest('id')->offset(($page - 1) * 10)->limit(10)->pluck('code');
        return ['items' => $codes->map(fn ($code) => $this->show($userId, $code))->all(), 'page' => $page, 'has_more' => $total > $page * 10];
    }

    private function expire(int $id, int $userId): void
    {
        DB::transaction(function () use ($id, $userId): void {
            $changed = DB::table('tienda_pedidos')->where('id', $id)->where('user_id', $userId)
                ->where('status', 'pending')->where('expires_at', '<=', now())
                ->update(['status' => 'expired', 'updated_at' => now()]);
            if ($changed) {
                DB::table('tienda_historial_estados_pedido')->insert([
                    'order_id' => $id, 'to_status' => 'expired', 'from_status' => 'pending',
                    'reason' => 'Venció el plazo del pedido sin confirmación de pago.', 'occurred_at' => now(),
                ]);
            }
        });
    }
}
