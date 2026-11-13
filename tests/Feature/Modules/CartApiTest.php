<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use App\Modules\Pedidos\Services\CartService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeCatalog(): void
    {
        Schema::create('tm_oficina', function (Blueprint $table): void {
            $table->integer('oficina_id')->primary();
            $table->string('oficina_nom');
            $table->integer('estado');
        });
        Schema::create('tm_conceptos', function (Blueprint $table): void {
            $table->integer('id_concepto')->primary();
            $table->string('codigo_concepto');
            $table->string('concepto');
            $table->decimal('costo', 10, 2)->nullable();
        });
        DB::table('tm_oficina')->insert(['oficina_id' => 7, 'oficina_nom' => 'Posgrado demo', 'estado' => 1]);
        DB::table('tm_conceptos')->insert([
            ['id_concepto' => 12, 'codigo_concepto' => 'DEMO', 'concepto' => 'Prueba', 'costo' => 10],
            ['id_concepto' => 13, 'codigo_concepto' => 'OCULTO', 'concepto' => 'No visible', 'costo' => 10],
        ]);
        DB::table('tienda_configuracion_oficinas')->insert([
            'legacy_office_id' => 7, 'slug' => 'posgrado-demo', 'catalog_enabled' => 1,
        ]);
        DB::table('tienda_conceptos_catalogo')->insert([
            ['id' => 101, 'legacy_office_id' => 7, 'legacy_concept_id' => 12, 'slug' => 'publicado', 'is_published' => 1],
            ['id' => 102, 'legacy_office_id' => 7, 'legacy_concept_id' => 13, 'slug' => 'oculto', 'is_published' => 0],
        ]);
    }

    public function test_cart_creation_returns_one_time_token_and_stores_only_hash(): void
    {
        $response = $this->postJson('/api/v1/tienda/carritos');
        $response->assertCreated()->assertJsonPath('data.cart.checkout_enabled', false);
        $token = $response->json('data.token');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertDatabaseHas('tienda_carritos', ['token_hash' => hash('sha256', $token)]);
        $this->assertDatabaseMissing('tienda_carritos', ['token_hash' => $token]);
        $this->assertNull($response->json('data.cart.total_amount'));
    }

    public function test_cart_requires_token_and_is_isolated_from_other_tokens(): void
    {
        $created = $this->postJson('/api/v1/tienda/carritos');
        $token = $created->json('data.token');
        $this->getJson('/api/v1/tienda/carritos/actual')->assertUnauthorized();
        $this->withToken(str_repeat('a', 64))->getJson('/api/v1/tienda/carritos/actual')->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/tienda/carritos/actual')->assertOk();
    }

    public function test_published_item_can_be_added_without_pricing_or_checkout(): void
    {
        $this->makeCatalog();
        $token = $this->postJson('/api/v1/tienda/carritos')->json('data.token');

        $response = $this->withToken($token)->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 101, 'quantity' => 2,
        ]);
        $response->assertOk()
            ->assertJsonPath('data.items_count', 2)
            ->assertJsonPath('data.items.0.name', 'Prueba')
            ->assertJsonPath('data.items.0.reference_amount', '10.00')
            ->assertJsonPath('data.total_amount', null)
            ->assertJsonPath('data.checkout_enabled', false)
            ->assertJsonPath('data.pricing_status', 'pending_institutional_approval')
            ->assertJsonCount(1, 'data.items')
            ->assertJsonMissingPath('data.amount');

        $line = $response->json('data.items.0.id');
        $this->withToken($token)->deleteJson('/api/v1/tienda/carritos/actual/items/'.$line)
            ->assertOk()->assertJsonCount(0, 'data.items');
    }

    public function test_unpublished_item_is_rejected_and_quantity_is_validated(): void
    {
        $this->makeCatalog();
        $token = $this->postJson('/api/v1/tienda/carritos')->json('data.token');

        $this->withToken($token)->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 102, 'quantity' => 1,
        ])->assertStatus(409);

        $this->withToken($token)->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 101, 'quantity' => 21,
        ])->assertUnprocessable();
    }

    public function test_expired_cart_is_unavailable(): void
    {
        $token = $this->postJson('/api/v1/tienda/carritos')->json('data.token');
        DB::table('tienda_carritos')->where('token_hash', hash('sha256', $token))
            ->update(['expires_at' => now()->subMinute()]);
        $this->withToken($token)->getJson('/api/v1/tienda/carritos/actual')->assertNotFound();
    }
    public function test_withdrawn_concept_keeps_cart_line_without_a_stale_reference_price(): void
    {
        $this->makeCatalog();
        $token = $this->postJson('/api/v1/tienda/carritos')->json('data.token');
        $this->withToken($token)->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 101, 'quantity' => 1,
        ])->assertOk();
        DB::table('tienda_conceptos_catalogo')->where('id', 101)->update(['is_published' => false]);
        $this->withToken($token)->getJson('/api/v1/tienda/carritos/actual')
            ->assertOk()->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.name', null)
            ->assertJsonPath('data.items.0.reference_amount', null)
            ->assertJsonPath('data.total_amount', null)
            ->assertJsonPath('data.checkout_enabled', false);
    }

    private function ownedCart(User $buyer): array
    {
        $created = app(CartService::class)->create();
        app(CartService::class)->add($created['token'], 101, 1);
        app(CartService::class)->claim($created['token'], $buyer->id);

        return $created;
    }

    public function test_owner_can_add_and_remove_after_claim_without_reusing_token(): void
    {
        $this->makeCatalog();
        $buyer = User::factory()->create();
        $created = $this->ownedCart($buyer);
        $this->actingAs($buyer);
        $response = $this->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 101, 'quantity' => 2, 'user_id' => 999999,
        ])->assertOk()->assertJsonPath('data.id', $created['cart']['id'])
            ->assertJsonPath('data.items_count', 3);
        $this->getJson('/api/v1/tienda/carritos/actual')->assertOk()->assertJsonPath('data.items_count', 3);
        $this->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 101, 'quantity' => 18,
        ])->assertUnprocessable();
        $this->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 102, 'quantity' => 1,
        ])->assertStatus(409);
        $this->deleteJson('/api/v1/tienda/carritos/actual/items/'.$response->json('data.items.0.id'))
            ->assertOk()->assertJsonCount(0, 'data.items');
        $this->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 101, 'quantity' => 1,
        ])->assertOk()->assertJsonPath('data.id', $created['cart']['id']);
        $this->withToken($created['token'])->getJson('/api/v1/tienda/carritos/actual')->assertNotFound();
        $this->assertDatabaseHas('tienda_carritos', ['id' => $created['cart']['id'], 'token_hash' => null]);
    }

    public function test_session_cannot_modify_another_buyers_cart_or_lines(): void
    {
        $this->makeCatalog();
        $first = User::factory()->create();
        $second = User::factory()->create();
        $firstCart = $this->ownedCart($first);
        $line = DB::table('tienda_detalles_carrito')->where('cart_id', $firstCart['cart']['id'])->value('id');
        $this->actingAs($second)->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 101, 'quantity' => 1, 'user_id' => $first->id,
            'cart_id' => $firstCart['cart']['id'],
        ])->assertNotFound();
        $this->ownedCart($second);
        $this->deleteJson('/api/v1/tienda/carritos/actual/items/'.$line)->assertOk();
        $this->assertDatabaseHas('tienda_detalles_carrito', ['id' => $line, 'quantity' => 1]);
    }

    public function test_owner_cannot_edit_expired_or_converted_cart(): void
    {
        $this->makeCatalog();
        $buyer = User::factory()->create();
        $created = $this->ownedCart($buyer);
        $this->actingAs($buyer);
        foreach (['expired', 'converted'] as $status) {
            DB::table('tienda_carritos')->where('id', $created['cart']['id'])->update(['status' => $status]);
            $this->postJson('/api/v1/tienda/carritos/actual/items', [
                'catalog_item_id' => 101, 'quantity' => 1,
            ])->assertNotFound();
        }
        DB::table('tienda_carritos')->where('id', $created['cart']['id'])
            ->update(['status' => 'active', 'expires_at' => now()->subMinute()]);
        $this->getJson('/api/v1/tienda/carritos/actual')->assertNotFound();
        $this->deleteJson('/api/v1/tienda/carritos/actual/items/1')->assertNotFound();
    }

}
