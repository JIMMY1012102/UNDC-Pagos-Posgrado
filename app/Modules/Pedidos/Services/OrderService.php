<?php

namespace App\Modules\Pedidos\Services;

use App\Modules\Catalogo\Services\OrderQuoteService;
use App\Modules\Pagos\Services\OrderPaymentGuard;
use App\Modules\Usuarios\Services\BuyerProfileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class OrderService
{
    public function __construct(private readonly OrderQuoteService $prices, private readonly BuyerProfileService $profiles, private readonly OrderPaymentGuard $payments) {}

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

    /** Replace an unpaid order with an editable cart; keep its original snapshot. */
    public function modify(int $userId, string $code): array
    {
        return DB::transaction(function () use ($userId, $code): array {
            // Serialize replacements for the same buyer; preserve create() lock order.
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            $source = DB::table('tienda_pedidos')->where('user_id', $userId)->where('code', $code)->first();
            abort_unless($source, 404);
            DB::table('tienda_carritos')->where('id', $source->cart_id)->lockForUpdate()->first();
            $order = DB::table('tienda_pedidos')->where('id', $source->id)->lockForUpdate()->first();

            if ($order->status === 'cancelled') {
                $event = DB::table('tienda_historial_estados_pedido')->where('order_id', $order->id)
                    ->where('to_status', 'cancelled')->latest('id')->first();
                $context = $event ? json_decode($event->context ?? '{}', true) : [];
                $action = $context['action'] ?? null;
                if ($action === 'modify_order') {
                    $cart = DB::table('tienda_carritos')->where('id', $context['replacement_cart_id'] ?? 0)
                        ->where('user_id', $userId)->where('status', 'active')
                        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->first();
                    if ($cart) {
                        return ['cart_id' => (int) $cart->id];
                    }
                }
                abort(409, 'Este pedido ya no se puede modificar.');
            }

            abort_unless($order->status === 'pending' && $order->paid_at === null && $order->confirmed_at === null
                && ($order->expires_at === null || now()->lt($order->expires_at))
                && $this->payments->canModify((int) $order->id), 409,
                'Solo puedes modificar un pedido pendiente, vigente y sin pagos iniciados.');

            // Do not overwrite another selection that the buyer has already started.
            $active = DB::table('tienda_carritos')->where('user_id', $userId)->where('status', 'active')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->whereExists(fn ($q) => $q->selectRaw('1')->from('tienda_detalles_carrito')
                    ->whereColumn('cart_id', 'tienda_carritos.id'))->exists();
            abort_if($active, 409, 'Ya tienes otra selección en tu carrito. Revísala antes de modificar este pedido.');

            $now = now();
            $cartId = DB::table('tienda_carritos')->insertGetId([
                'user_id' => $userId, 'token_hash' => null, 'status' => 'active', 'currency' => $order->currency,
                'expires_at' => $now->copy()->addDay(), 'created_at' => $now, 'updated_at' => $now,
            ]);
            $lines = DB::table('tienda_detalles_pedido')->where('order_id', $order->id)->orderBy('id')->get();
            foreach ($lines as $line) {
                DB::table('tienda_detalles_carrito')->insert([
                    'cart_id' => $cartId, 'catalog_item_id' => $line->catalog_item_id,
                    'beneficiary_id' => $line->beneficiary_id, 'quantity' => $line->quantity,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            DB::table('tienda_pedidos')->where('id', $order->id)->update([
                'status' => 'cancelled', 'cancelled_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('tienda_historial_estados_pedido')->insert([
                'order_id' => $order->id, 'actor_user_id' => $userId, 'from_status' => 'pending', 'to_status' => 'cancelled',
                'reason' => 'El comprador volvió al carrito para modificar su selección. Se generará un nuevo pedido al confirmar.',
                'context' => json_encode(['action' => 'modify_order', 'replacement_cart_id' => $cartId]), 'occurred_at' => $now,
            ]);

            return ['cart_id' => $cartId];
        }, 3);
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
            'can_modify' => $order->status === 'pending' && $order->paid_at === null && $order->confirmed_at === null
                && $this->payments->canModify((int) $order->id),
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
