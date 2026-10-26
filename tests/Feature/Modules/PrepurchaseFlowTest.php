<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrepurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private function catalog(): void
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
            $table->decimal('costo', 10, 2);
        });
        DB::table('tm_oficina')->insert(['oficina_id' => 7, 'oficina_nom' => 'Posgrado demo', 'estado' => 1]);
        DB::table('tm_conceptos')->insert(['id_concepto' => 12, 'codigo_concepto' => 'DEMO', 'concepto' => 'Prueba', 'costo' => 10]);
        DB::table('store_office_settings')->insert(['legacy_office_id' => 7, 'slug' => 'posgrado-demo', 'catalog_enabled' => 1]);
        DB::table('store_catalog_items')->insert(['id' => 101, 'legacy_office_id' => 7, 'legacy_concept_id' => 12, 'slug' => 'publicado', 'is_published' => 1]);
    }

    public function test_public_tienda_screen_does_not_require_authentication(): void
    {
        $this->get('/tienda')->assertOk()->assertSee('Comprar');
        $this->getJson('/api/v1/tienda/precompra/resumen')->assertUnauthorized();
        $this->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => str_repeat('a', 64)])->assertUnauthorized();
    }

    public function test_buyer_claims_anonymous_cart_once_then_completes_profile_and_reviews(): void
    {
        $this->catalog();
        $token = $this->postJson('/api/v1/tienda/carritos')->json('data.token');
        $this->withToken($token)->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => 101, 'quantity' => 2,
        ])->assertOk();

        $buyer = User::factory()->create();
        $this->actingAs($buyer)->postJson('/api/v1/tienda/precompra/vincular-carrito', [
            'cart_token' => $token,
        ])->assertOk()->assertJsonPath('data.items_count', 2);

        $this->getJson('/api/v1/tienda/precompra/resumen')
            ->assertOk()->assertJsonPath('data.next_step', 'complete_profile')
            ->assertJsonPath('data.checkout_enabled', false)
            ->assertJsonPath('data.cart.items_count', 2);

        $this->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Comprador', 'last_names' => 'Demo',
            'document_type' => 'DNI', 'document_number' => '00000000',
        ])->assertOk();

        $this->getJson('/api/v1/tienda/precompra/resumen')
            ->assertOk()->assertJsonPath('data.next_step', 'review')
            ->assertJsonPath('data.cart.items_count', 2)
            ->assertJsonPath('data.total_amount', null);

        $this->assertDatabaseHas('store_carts', ['user_id' => $buyer->id, 'token_hash' => null]);
        $this->assertDatabaseCount('store_orders', 0);
        $this->assertDatabaseCount('store_customer_profiles', 1);
        $this->postJson('/api/v1/tienda/precompra/vincular-carrito', [
            'cart_token' => $token,
        ])->assertNotFound();
    }

    public function test_cart_claim_requires_owner_auth_and_expires_safely(): void
    {
        $token = $this->postJson('/api/v1/tienda/carritos')->json('data.token');
        DB::table('store_carts')->where('token_hash', hash('sha256', $token))->update(['expires_at' => now()->subMinute()]);
        $this->actingAs(User::factory()->create())->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => $token])->assertNotFound();
        $this->assertDatabaseCount('store_orders', 0);
    }

    public function test_one_buyers_cart_not_exposed_to_another_buyer(): void
    {
        $this->catalog();
        $token = $this->postJson('/api/v1/tienda/carritos')->json('data.token');
        $this->withToken($token)->postJson('/api/v1/tienda/carritos/actual/items', ['catalog_item_id' => 101, 'quantity' => 1])->assertOk();

        $first = User::factory()->create();
        $second = User::factory()->create();
        $this->actingAs($first)->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => $token])->assertOk();
        $this->actingAs($second)->getJson('/api/v1/tienda/precompra/resumen')->assertJsonPath('data.cart', null);
        $this->actingAs($second)->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => $token])->assertNotFound();
    }
}
