<?php

namespace App\Modules\Tienda\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class CartService
{
    private const MAX_QUANTITY = 20;

    /**
     * Anonymous carts are identified by a cryptographically random bearer token.
     * Only the SHA-256 digest is stored; possession of the token is required for access.
     *
     * @return array{token:string, cart:array<string,mixed>}
     */
    public function create(): array
    {
        $token = bin2hex(random_bytes(32));
        $id = DB::table('store_carts')->insertGetId([
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
    public function find(string $token): ?array
    {
        $cart = $this->activeCart($token);

        return $cart ? $this->snapshot((int) $cart->id) : null;
    }

    /** @return array<string,mixed>|null */
    public function add(string $token, int $catalogId, int $quantity): ?array
    {
        return DB::transaction(function () use ($token, $catalogId, $quantity): ?array {
            $cart = $this->activeCart($token, true);
            if ($cart === null) {
                return null;
            }

            // Guard against unpublished or disabled items and inactive offices.
            $valid = DB::table('store_catalog_items as item')
                ->join('store_office_settings as settings', 'settings.legacy_office_id', '=', 'item.legacy_office_id')
                ->join('tm_oficina as office', 'office.oficina_id', '=', 'item.legacy_office_id')
                ->join('tm_conceptos as concept', 'concept.id_concepto', '=', 'item.legacy_concept_id')
                ->where('item.id', $catalogId)
                ->where('item.is_published', true)
                ->where('settings.catalog_enabled', true)
                ->where('office.estado', 1)
                ->whereNotNull('concept.costo')
                ->where('concept.costo', '>=', 0)
                ->exists();

            if (! $valid) {
                throw new RuntimeException('CATALOG_ITEM_UNAVAILABLE');
            }

            $line = DB::table('store_cart_items')
                ->where('cart_id', $cart->id)
                ->where('catalog_item_id', $catalogId)
                ->whereNull('beneficiary_id')
                ->lockForUpdate()
                ->first();

            if ($line) {
                if ((int) $line->quantity + $quantity > self::MAX_QUANTITY) {
                    throw new RuntimeException('QUANTITY_LIMIT');
                }

                DB::table('store_cart_items')->where('id', $line->id)->update([
                    'quantity' => (int) $line->quantity + $quantity,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('store_cart_items')->insert([
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
    public function remove(string $token, int $lineId): ?array
    {
        return DB::transaction(function () use ($token, $lineId): ?array {
            $cart = $this->activeCart($token, true);
            if ($cart === null) {
                return null;
            }

            DB::table('store_cart_items')
                ->where('cart_id', $cart->id)
                ->where('id', $lineId)
                ->delete();

            return $this->snapshot((int) $cart->id);
        });
    }

    private function activeCart(string $token, bool $lock = false): ?object
    {
        $query = DB::table('store_carts')
            ->where('token_hash', hash('sha256', $token))
            ->where('status', 'active')
            ->whereNull('user_id')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        return ($lock ? $query->lockForUpdate() : $query)->first();
    }

    /** @return array<string,mixed> */
    private function snapshot(int $id): array
    {
        $lines = DB::table('store_cart_items as line')
            ->join('store_catalog_items as catalog', 'catalog.id', '=', 'line.catalog_item_id')
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
