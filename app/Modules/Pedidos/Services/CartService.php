<?php

namespace App\Modules\Pedidos\Services;

use App\Modules\Catalogo\Contracts\CatalogItemLookup;
use App\Modules\Catalogo\Services\CatalogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

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
            $cart = $this->activeCart($token, true);
            if ($cart === null) {
                return null;
            }

            DB::table('tienda_carritos')->where('id', $cart->id)->update([
                'user_id' => $userId,
                'token_hash' => null,
                'updated_at' => now(),
            ]);

            return $this->snapshot((int) $cart->id);
        });
    }

    /** @return array<string,mixed>|null */
    public function forBuyer(int $userId): ?array
    {
        $cart = DB::table('tienda_carritos')->where('user_id', $userId)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
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
                throw new RuntimeException('CATALOG_ITEM_UNAVAILABLE');
            }

            $line = DB::table('tienda_detalles_carrito')
                ->where('cart_id', $cart->id)
                ->where('catalog_item_id', $catalogId)
                ->whereNull('beneficiary_id')
                ->lockForUpdate()
                ->first();

            if ($line) {
                if ((int) $line->quantity + $quantity > self::MAX_QUANTITY) {
                    throw new RuntimeException('QUANTITY_LIMIT');
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

        $query = DB::table('tienda_carritos')
            ->when($token !== null,
                fn ($q) => $q->where('token_hash', hash('sha256', $token))->whereNull('user_id'),
                fn ($q) => $q->where('user_id', $userId))
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

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
            'pricing_status' => 'pending_institutional_approval',
            'total_amount' => null,
            'checkout_enabled' => false,
        ];
    }
}
