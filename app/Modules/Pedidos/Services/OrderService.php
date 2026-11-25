<?php

namespace App\Modules\Pedidos\Services;

use App\Modules\Catalogo\Services\OrderQuoteService;
use App\Modules\Catalogo\Services\PricingPolicy;
use App\Modules\Pagos\Services\OrderPaymentGuard;
use App\Modules\Pedidos\Queries\ActiveCartQuery;
use App\Modules\Usuarios\Services\BuyerProfileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class OrderService
{
    public function __construct(private readonly OrderQuoteService $prices, private readonly BuyerProfileService $profiles, private readonly OrderPaymentGuard $payments, private readonly CartService $carts) {}

    public function quote(int $userId, int $cartId): array
    {
        $cart = ActiveCartQuery::make()->where('id', $cartId)->where('usuario_id', $userId)->first();
        abort_unless($cart, 404);
        $lines = DB::table('tienda_detalles_carrito')->where('carrito_id', $cartId)->orderBy('id')->get();
        $items = []; $total = 0;
        foreach ($lines as $line) {
            $item = $line->beneficiario_id === null ? $this->prices->line((int) $line->concepto_catalogo_id, (int) $line->cantidad) : null;
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
            $cart = DB::table('tienda_carritos')->where('id', $cartId)->where('usuario_id', $userId)->lockForUpdate()->first();
            abort_unless($cart, 404);
            $existing = DB::table('tienda_pedidos')->where('carrito_id', $cartId)->where('usuario_id', $userId)->first();
            if ($existing) {
                return $this->show($userId, $existing->codigo);
            }
            if (! $this->profiles->isComplete($userId)) {
                throw ValidationException::withMessages(['profile' => 'Completa tus datos antes de generar el pedido.']);
            }
            $checkoutEnabled = PricingPolicy::status()['checkout_enabled'];
            if ($checkoutEnabled && ! $this->profiles->isCheckoutReady($userId)) {
                throw ValidationException::withMessages(['profile' => 'Para continuar con el pago, completa tu dirección y teléfono (incluido el código de país) en tu perfil.']);
            }
            $quote = $this->quote($userId, $cartId);
            $profile = $this->profiles->find($userId);
            $email = $profile['contact_email'] ?: DB::table('users')->where('id', $userId)->value('email');
            $now = now();
            $code = 'UNDC-'.Str::upper((string) Str::ulid());
            $id = DB::table('tienda_pedidos')->insertGetId([
                'codigo' => $code, 'usuario_id' => $userId, 'carrito_id' => $cartId,
                'estado' => 'pending', 'moneda' => 'PEN', 'monto_total' => self::money($quote['total_cents']),
                'comprador_nombres' => $profile['first_names'], 'comprador_apellidos' => $profile['last_names'],
                'comprador_tipo_documento' => $profile['document_type'], 'comprador_numero_documento' => $profile['document_number'],
                'comprador_correo' => $email, 'comprador_telefono' => $profile['phone'],
                'comprador_codigo_pais_telefono' => $profile['phone_country_code'] ?? null,
                'comprador_direccion_linea_1' => $profile['line_1'] ?? null,
                'comprador_direccion_linea_2' => $profile['line_2'] ?? null,
                'comprador_direccion_ciudad' => $profile['city'] ?? null,
                'comprador_direccion_region' => $profile['state'] ?? null,
                'comprador_direccion_pais' => $profile['country'] ?? null,
                'vence_en' => $now->copy()->addHours(24), 'created_at' => $now, 'updated_at' => $now,
            ]);
            foreach ($quote['items'] as $line) {
                DB::table('tienda_detalles_pedido')->insert([
                    'pedido_id' => $id, 'concepto_catalogo_id' => $line['catalog_item_id'],
                    'codigo_concepto' => $line['concept_code'], 'concepto_nombre' => $line['concept_name'],
                    'cantidad' => $line['quantity'], 'monto_unitario' => self::money($line['unit_cents']),
                    'monto_total' => self::money($line['total_cents']),
                    'beneficiario_nombres' => $profile['first_names'], 'beneficiario_apellidos' => $profile['last_names'],
                    'beneficiario_tipo_documento' => $profile['document_type'], 'beneficiario_numero_documento' => $profile['document_number'],
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            DB::table('tienda_historial_estados_pedido')->insert([
                'pedido_id' => $id, 'usuario_actor_id' => $userId, 'estado_nuevo' => 'pending',
                'motivo' => 'Pedido generado con importes oficiales verificados. No acredita pago ni inicia trámite académico.', 'ocurrido_en' => $now,
            ]);
            DB::table('tienda_carritos')->where('id', $cartId)->update(['estado' => 'converted', 'updated_at' => $now]);
            return $this->show($userId, $code);
        });
    }

    /** Replace an unpaid order with an editable cart; keep its original snapshot. */
    public function modify(int $userId, string $code, ?string $cartToken = null): array
    {
        if ($cartToken !== null) {
            $cart = $this->carts->find($cartToken);
            abort_if(($cart['items_count'] ?? 0) > 0, 409, 'Ya tienes otra selección en tu carrito. Revísala antes de modificar este pedido.');
        }

        return DB::transaction(function () use ($userId, $code): array {
            // Serialize replacements for the same buyer; preserve create() lock order.
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            $source = DB::table('tienda_pedidos')->where('usuario_id', $userId)->where('codigo', $code)->first();
            abort_unless($source, 404);
            DB::table('tienda_carritos')->where('id', $source->carrito_id)->lockForUpdate()->first();
            $order = DB::table('tienda_pedidos')->where('id', $source->id)->lockForUpdate()->first();

            if ($order->estado === 'cancelled') {
                $event = DB::table('tienda_historial_estados_pedido')->where('pedido_id', $order->id)
                    ->where('estado_nuevo', 'cancelled')->latest('id')->first();
                $context = $event ? json_decode($event->contexto ?? '{}', true) : [];
                $action = $context['action'] ?? null;
                if ($action === 'modify_order') {
                    $cart = ActiveCartQuery::make()->where('id', $context['replacement_cart_id'] ?? 0)
                        ->where('usuario_id', $userId)->first();
                    if ($cart) {
                        return ['cart_id' => (int) $cart->id];
                    }
                }
                abort(409, 'Este pedido ya no se puede modificar.');
            }

            abort_unless($order->estado === 'pending' && $order->pagado_en === null && $order->confirmado_en === null
                && ($order->vence_en === null || now()->lt($order->vence_en))
                && $this->payments->canModify((int) $order->id), 409,
                'Solo puedes modificar un pedido pendiente, vigente y sin pagos iniciados.');

            // Do not overwrite another selection that the buyer has already started.
            $active = ActiveCartQuery::make()->where('usuario_id', $userId)
                ->whereExists(fn ($q) => $q->selectRaw('1')->from('tienda_detalles_carrito')
                    ->whereColumn('carrito_id', 'tienda_carritos.id'))->exists();
            abort_if($active, 409, 'Ya tienes otra selección en tu carrito. Revísala antes de modificar este pedido.');

            DB::table('tienda_carritos')->where('usuario_id', $userId)->where('estado', 'active')
                ->update(['estado' => 'expired', 'updated_at' => now()]);
            $now = now();
            $cartId = DB::table('tienda_carritos')->insertGetId([
                'usuario_id' => $userId, 'token_hash' => null, 'estado' => 'active', 'moneda' => $order->moneda,
                'vence_en' => $now->copy()->addDay(), 'created_at' => $now, 'updated_at' => $now,
            ]);
            $lines = DB::table('tienda_detalles_pedido')->where('pedido_id', $order->id)->orderBy('id')->get();
            foreach ($lines as $line) {
                DB::table('tienda_detalles_carrito')->insert([
                    'carrito_id' => $cartId, 'concepto_catalogo_id' => $line->concepto_catalogo_id,
                    'beneficiario_id' => $line->beneficiario_id, 'cantidad' => $line->cantidad,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            DB::table('tienda_pedidos')->where('id', $order->id)->update([
                'estado' => 'cancelled', 'cancelado_en' => $now, 'updated_at' => $now,
            ]);
            DB::table('tienda_historial_estados_pedido')->insert([
                'pedido_id' => $order->id, 'usuario_actor_id' => $userId, 'estado_anterior' => 'pending', 'estado_nuevo' => 'cancelled',
                'motivo' => 'El comprador volvió al carrito para modificar su selección. Se generará un nuevo pedido al confirmar.',
                'contexto' => json_encode(['action' => 'modify_order', 'replacement_cart_id' => $cartId]), 'ocurrido_en' => $now,
            ]);

            return ['cart_id' => $cartId];
        }, 3);
    }

    public function show(int $userId, string $code): array
    {
        $order = DB::table('tienda_pedidos')->where('usuario_id', $userId)->where('codigo', $code)->first();
        abort_unless($order, 404);
        return $this->present($order);
    }

    private function present(object $order, ?array $items = null, ?array $history = null, ?bool $canModify = null, ?bool $hasUnresolvedPayment = null): array
    {
        $isPastExpiry = $order->estado === 'pending' && $order->pagado_en === null && $order->confirmado_en === null
            && $order->vence_en !== null && now()->gte($order->vence_en);
        $status = $isPastExpiry
            ? (($hasUnresolvedPayment ?? $this->payments->hasUnresolvedPayment((int) $order->id)) ? 'payment_in_review' : 'expired')
            : $order->estado;

        return [
            'code' => $order->codigo, 'status' => $status, 'currency' => $order->moneda,
            'total_amount' => $order->monto_total, 'created_at' => $order->created_at, 'expires_at' => $order->vence_en,
            'paid_at' => $order->pagado_en, 'buyer_name' => $order->comprador_nombres.' '.$order->comprador_apellidos,
            'buyer_email' => $order->comprador_correo, 'payment_enabled' => PricingPolicy::status()['checkout_enabled'] && $status === 'pending' && $order->pagado_en === null && ($order->vence_en === null || now()->lt($order->vence_en)),
            'can_modify' => $status === 'pending' && $order->pagado_en === null && $order->confirmado_en === null
                && ($canModify ?? $this->payments->canModify((int) $order->id)),
            'items' => $items ?? DB::table('tienda_detalles_pedido')->where('pedido_id', $order->id)->orderBy('id')
                ->get(['concepto_nombre as concept_name', 'codigo_concepto as concept_code', 'cantidad as quantity', 'monto_unitario as unit_amount', 'monto_total as total_amount'])->all(),
            'history' => $history ?? DB::table('tienda_historial_estados_pedido')->where('pedido_id', $order->id)->orderBy('id')
                ->get(['estado_nuevo as to_status', 'motivo as reason', 'ocurrido_en as occurred_at'])->all(),
        ];
    }

    public function listing(int $userId, int $page): array
    {
        $page = max(1, $page);
        $orders = DB::table('tienda_pedidos')->where('usuario_id', $userId)->latest('id')
            ->offset(($page - 1) * 10)->limit(11)->get();
        $hasMore = $orders->count() > 10;
        $orders = $orders->take(10);
        $ids = $orders->pluck('id')->all();
        $items = DB::table('tienda_detalles_pedido')->whereIn('pedido_id', $ids)->orderBy('id')->get()->groupBy('pedido_id');
        $history = DB::table('tienda_historial_estados_pedido')->whereIn('pedido_id', $ids)->orderBy('id')->get()->groupBy('pedido_id');
        $blocked = $this->payments->blockedOrderIds($ids);
        $expiredPendingIds = $orders->filter(fn ($order) => $order->estado === 'pending'
            && $order->pagado_en === null && $order->confirmado_en === null
            && $order->vence_en !== null && now()->gte($order->vence_en))->pluck('id')->all();
        $unresolved = $this->payments->unresolvedOrderIds($expiredPendingIds);
        return ['items' => $orders->map(fn ($order) => $this->present($order,
            ($items->get($order->id) ?? collect())->map(fn ($line) => (object) ['concept_name' => $line->concepto_nombre, 'concept_code' => $line->codigo_concepto, 'quantity' => $line->cantidad, 'unit_amount' => $line->monto_unitario, 'total_amount' => $line->monto_total])->all(),
            ($history->get($order->id) ?? collect())->map(fn ($event) => (object) ['to_status' => $event->to_status, 'reason' => $event->reason, 'occurred_at' => $event->occurred_at])->all(),
            ! in_array($order->id, $blocked), in_array($order->id, $unresolved)))->all(), 'page' => $page, 'has_more' => $hasMore];
    }

    /** Bounded transactions preserve one immutable event per expiry. */
    public function expireDue(?int $userId = null): int
    {
        $count = 0;
        do {
            $changed = DB::transaction(function () use ($userId): int {
                $now = now();
                $query = DB::table('tienda_pedidos')->where('estado', 'pending')->whereNull('pagado_en')->whereNull('confirmado_en')
                    ->when($userId !== null, fn ($q) => $q->where('usuario_id', $userId))
                    ->where('vence_en', '<=', $now);
                $ids = $this->payments->excludeOrdersWithUnresolvedPayments($query)
                    ->orderBy('id')->limit(200)->lockForUpdate()->pluck('id');
                if ($ids->isEmpty()) return 0;
                DB::table('tienda_pedidos')->whereIn('id', $ids)->update(['estado' => 'expired', 'updated_at' => $now]);
                DB::table('tienda_historial_estados_pedido')->insert($ids->map(fn ($id) => [
                    'pedido_id' => $id, 'estado_anterior' => 'pending', 'estado_nuevo' => 'expired',
                    'motivo' => 'Venció el plazo del pedido sin confirmación de pago.', 'ocurrido_en' => $now,
                ])->all());
                return $ids->count();
            }, 3);
            $count += $changed;
        } while ($changed === 200);
        return $count;
    }

}
