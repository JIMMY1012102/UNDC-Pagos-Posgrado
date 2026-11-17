<?php

namespace App\Modules\Pedidos\Services;

use App\Modules\Catalogo\Contracts\CatalogItemLookup;
use App\Modules\Catalogo\Services\CatalogService;
use App\Modules\Catalogo\Services\PricingPolicy;
use App\Modules\Pedidos\Exceptions\CatalogItemUnavailable;
use App\Modules\Pedidos\Exceptions\QuantityLimitExceeded;
use App\Modules\Pedidos\Queries\ActiveCartQuery;
use Illuminate\Support\Facades\DB;

final class CartService
{
    private const MAX_QUANTITY = 20;

    public function __construct(private readonly CatalogItemLookup $catalog, private readonly CatalogService $references) {}


    /**
     * Anonymous carts are identified by a cryptographically random bearer token.
     * Only the SHA-256 digest is stored; possession of the token is required for access.
     *
     * @return array{token:string, cart:array<string,mixed>}
     */
    public function create(): array
    {
        $token = bin2hex(random_bytes(32));
        $id = DB::table('tienda_carritos')->insertGetId([
            'token_hash' => hash('sha256', $token),
            'status' => 'active',
            'currency' => 'PEN',
            'expires_at' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['token' => $token, 'cart' => $this->snapshot((int) $id)];
    }

    /** @return array<string,mixed>|null */
    public function find(?string $token, ?int $userId = null): ?array
    {
        $cart = $this->activeCart($token, false, $userId);

        return $cart ? $this->snapshot((int) $cart->id) : null;
    }

    /** Associate an anonymous cart with the authenticated buyer once. */
    public function claim(string $token, int $userId): ?array
    {
        return DB::transaction(function () use ($token, $userId): ?array {
            // Serialize all claims and order replacements for this buyer.
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            $this->expireDue($userId);
            $cart = $this->activeCart($token, true);
            if ($cart === null) {
                return null;
            }

            $existing = $this->activeCart(null, true, $userId);
            if ($existing) {
                $lines = DB::table('tienda_detalles_carrito')->where('cart_id', $cart->id)->orderBy('id')->get();
                foreach ($lines as $line) {
                    $target = DB::table('tienda_detalles_carrito')->where('cart_id', $existing->id)
                        ->where('catalog_item_id', $line->catalog_item_id)->where('beneficiary_id', $line->beneficiary_id)->first();
                    if ($target) {
                        $quantity = (int) $target->quantity + (int) $line->quantity;
                        // Preserve both selections on overflow; never silently truncate.
                        if ($quantity > self::MAX_QUANTITY) {
                            throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => 'La selección combinada supera 20 unidades. Ajusta el carrito antes de vincularlo.']);
                        }
                        DB::table('tienda_detalles_carrito')->where('id', $target->id)->update(['quantity' => $quantity, 'updated_at' => now()]);
                        DB::table('tienda_detalles_carrito')->where('id', $line->id)->delete();
                    } else {
                        DB::table('tienda_detalles_carrito')->where('id', $line->id)->update(['cart_id' => $existing->id, 'updated_at' => now()]);
                    }
                }
                DB::table('tienda_carritos')->where('id', $cart->id)->update(['status' => 'merged', 'token_hash' => null, 'updated_at' => now()]);
                return $this->snapshot((int) $existing->id);
            }

            DB::table('tienda_carritos')->where('id', $cart->id)->update([
                'user_id'  => $userId,
                'token_hash' => null,
                'updated_at' => now(),
            ]);

            return $this->snapshot((int) $cart->id);
        });
    }

    public function expireDue(?int $userId = null): int
    {
        $now = now();

        return DB::table('tienda_carritos')->where('status', 'active')
            ->when($userId !== null, fn ($q) => $q->where('user_id', $userId))
            ->where('expires_at', '<=', $now)
            ->update(['status' => 'expired', 'token_hash' => null, 'updated_at' => $now]);
    }

    /** @return array<string,mixed>|null */
    public function forBuyer(int $userId): ?array
    {
        $cart = ActiveCartQuery::make()->where('user_id', $userId)
            ->latest('id')->first();

        return $cart ? $this->snapshot((int) $cart->id) : null;
    }

    /** @return array<string,mixed>|null */
    public function add(?string $token, int $catalogId, int $quantity, ?int $userId = null): ?array
    {
        return DB::transaction(function () use ($token, $catalogId, $quantity, $userId): ?array {
            $cart = $this->activeCart($token, true, $userId);
            if ($cart === null) {
                return null;
            }

            // Pedidos asks Catalogo; it must not query SISPAGOS tables itself.
            $valid = $this->catalog->isAvailable($catalogId);

            if (! $valid) {
                throw new CatalogItemUnavailable();
            }

            $line = DB::table('tienda_detalles_carrito')
                ->where('cart_id', $cart->id)
                ->where('catalog_item_id', $catalogId)
                ->whereNull('beneficiary_id')
                ->lockForUpdate()
                ->first();

            if ($line) {
                if ((int) $line->quantity + $quantity > self::MAX_QUANTITY) {
                    throw new QuantityLimitExceeded();
                }

                DB::table('tienda_detalles_carrito')->where('id', $line->id)->update([
                    'quantity' => (int) $line->quantity + $quantity,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('tienda_detalles_carrito')->insert([
                    'cart_id' => $cart->id,
                    'catalog_item_id' => $catalogId,
                    'beneficiary_id' => null,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $this->snapshot((int) $cart->id);
        });
    }

    /** @return array<string,mixed>|null */
    public function remove(?string $token, int $lineId, ?int $userId = null): ?array
    {
        return DB::transaction(function () use ($token, $lineId, $userId): ?array {
            $cart = $this->activeCart($token, true, $userId);
            if ($cart === null) {
                return null;
            }

            DB::table('tienda_detalles_carrito')
                ->where('cart_id', $cart->id)
                ->where('id', $lineId)
                ->delete();

            return $this->snapshot((int) $cart->id);
        });
    }

    private function activeCart(?string $token, bool $lock = false, ?int $userId = null): ?object
    {
        if ($token === null && $userId === null) {
            return null;
        }

        $query = ActiveCartQuery::make()
            ->when($token !== null,
                fn ($q) => $q->where('token_hash', hash('sha256', $token))->whereNull('user_id'),
                fn ($q) => $q->where('user_id', $userId));

        return ($lock ? $query->lockForUpdate() : $query)->latest('id')->first();
    }

    /** @return array<string,mixed> */
    private function snapshot(int $id): array
    {
        $lines = DB::table('tienda_detalles_carrito as line')
            ->join('tienda_conceptos_catalogo as catalog', 'catalog.id', '=', 'line.catalog_item_id')
            ->where('line.cart_id', $id)
            ->orderBy('line.id')
            ->get([
                'line.id',
                'line.catalog_item_id',
                'line.quantity',
                'catalog.slug',
            ])
            ->map(static fn (object $item): array => [
                'id' => (int) $item->id,
                'catalog_item_id' => (int) $item->catalog_item_id,
                'slug' => (string) $item->slug,
                'quantity' => (int) $item->quantity,
            ])->all();

        if ($lines !== []) {
            $references = $this->references->referencesFor(array_column($lines, 'catalog_item_id'));
            $lines = array_map(static fn (array $line): array => $line + (
                $references->get($line['catalog_item_id']) ?? ['name' => null, 'reference_amount' => null]
            ), $lines);
        }

        return [
            'id' => $id,
            'status' => 'active',
            'currency' => 'PEN',
            'items' => $lines,
            'items_count' => array_sum(array_column($lines, 'quantity')),
            ...PricingPolicy::status(),
            'total_amount' => null,
        ];
    }
}
