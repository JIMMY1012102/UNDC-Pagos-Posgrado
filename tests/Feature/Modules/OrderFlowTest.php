<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private function buyerCart(bool $approved = true): array
    {
        Schema::create('tm_oficina', function (Blueprint $t): void {
            $t->integer('oficina_id')->primary(); $t->string('oficina_nom'); $t->integer('estado');
        });
        Schema::create('tm_conceptos', function (Blueprint $t): void {
            $t->integer('id_concepto')->primary(); $t->string('codigo_concepto'); $t->string('concepto'); $t->decimal('costo', 10, 2)->nullable();
        });
        DB::table('tm_oficina')->insert(['oficina_id' => 7, 'oficina_nom' => 'Posgrado prueba', 'estado' => 1]);
        DB::table('tm_conceptos')->insert(['id_concepto' => 12, 'codigo_concepto' => 'DEMO', 'concepto' => 'Concepto prueba', 'costo' => '999.00']);
        DB::table('tienda_configuracion_oficinas')->insert(['legacy_office_id' => 7, 'slug' => 'prueba', 'catalog_enabled' => true]);
        DB::table('tienda_conceptos_catalogo')->insert(['id' => 101, 'legacy_office_id' => 7, 'legacy_concept_id' => 12, 'slug' => 'concepto', 'is_published' => true]);
        DB::table('tienda_versiones_tarifas')->insert(['catalog_item_id' => 101, 'amount' => '25.10', 'currency' => 'PEN', 'valid_from' => now()->subDay(), 'is_approved' => $approved, 'approval_reference' => 'PRUEBA-NO-REAL']);
        $buyer = User::factory()->create();
        DB::table('tienda_perfiles_compradores')->insert(['user_id' => $buyer->id, 'first_names' => 'Persona', 'last_names' => 'Prueba', 'document_type' => 'DNI', 'document_number' => '00000000']);
        $id = DB::table('tienda_carritos')->insertGetId(['user_id' => $buyer->id, 'status' => 'active', 'currency' => 'PEN', 'expires_at' => now()->addDay()]);
        DB::table('tienda_detalles_carrito')->insert(['cart_id' => $id, 'catalog_item_id' => 101, 'quantity' => 2]);
        $this->actingAs($buyer);
        return [$buyer, $id];
    }

    public function test_quote_and_order_use_approved_price_not_browser_or_legacy_amount(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        $this->postJson('/api/v1/tienda/pedidos/cotizar', ['cart_id' => $cart])->assertOk()->assertJsonPath('data.total_amount', '50.20');
        $response = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart, 'total_amount' => '0.01', 'status' => 'paid', 'user_id' => 999]);
        $response->assertCreated()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.payment_enabled', false);
        $this->assertDatabaseHas('tienda_pedidos', ['user_id' => $buyer->id, 'status' => 'pending', 'total_amount' => '50.20', 'paid_at' => null]);
        $this->assertDatabaseHas('tienda_detalles_pedido', ['quantity' => 2, 'unit_amount' => '25.10']);
        $this->assertDatabaseCount('tienda_transacciones_pago', 0);
        $this->assertDatabaseCount('tienda_intentos_pago', 0);
    }

    public function test_retry_returns_same_order_and_preserves_snapshot(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->json('data.code');
        DB::table('tienda_perfiles_compradores')->where('user_id', $buyer->id)->update(['first_names' => 'Cambio']);
        DB::table('tienda_versiones_tarifas')->update(['amount' => '80.00']);
        $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->assertCreated()->assertJsonPath('data.code', $code)->assertJsonPath('data.buyer_name', 'Persona Prueba');
        $this->assertDatabaseCount('tienda_pedidos', 1);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 1);
        $this->assertDatabaseHas('tienda_pedidos', ['code' => $code, 'total_amount' => '50.20']);
    }

    public function test_official_catalog_price_generates_order_without_duplicate_approval(): void
    {
        [$buyer, $cart] = $this->buyerCart(false);
        $this->postJson('/api/v1/tienda/pedidos/cotizar', ['cart_id' => $cart])
            ->assertOk()->assertJsonPath('data.total_amount', '1998.00');
        $response = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart, 'total_amount' => '0.01', 'status' => 'paid']);
        $response->assertCreated()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.payment_enabled', false);
        $this->assertDatabaseHas('tienda_pedidos', ['user_id' => $buyer->id, 'total_amount' => '1998.00', 'paid_at' => null]);
        $this->assertDatabaseHas('tienda_versiones_tarifas', ['catalog_item_id' => 101, 'is_approved' => false]);
        $this->assertDatabaseCount('tienda_intentos_pago', 0);
        DB::table('tm_conceptos')->update(['costo' => '1.00']);
        $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])
            ->assertCreated()->assertJsonPath('data.code', $response->json('data.code'))
            ->assertJsonPath('data.total_amount', fn ($amount) => (float) $amount === 1998.0);
        $this->assertDatabaseCount('tienda_pedidos', 1);
    }

    public function test_ambiguous_tariffs_and_invalid_catalog_prices_are_blocked(): void
    {
        [, $cart] = $this->buyerCart();
        DB::table('tienda_versiones_tarifas')->insert(['catalog_item_id' => 101, 'amount' => '30.00', 'currency' => 'PEN', 'valid_from' => now()->subDay(), 'is_approved' => true, 'approval_reference' => 'OTRA-PRUEBA']);
        $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->assertUnprocessable();
        DB::table('tienda_versiones_tarifas')->delete();
        foreach (['0.00', '-10.00', null] as $amount) {
            DB::table('tm_conceptos')->update(['costo' => $amount]);
            $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->assertUnprocessable();
        }
        $this->assertDatabaseCount('tienda_pedidos', 0);
        $this->assertDatabaseHas('tienda_carritos', ['id' => $cart, 'status' => 'active']);
    }

    public function test_ownership_is_enforced_for_cart_detail_and_history(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->json('data.code');
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/v1/tienda/pedidos/'.$code)->assertNotFound();
        $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->assertNotFound();
        $this->getJson('/api/v1/tienda/pedidos')->assertOk()->assertJsonCount(0, 'data.items');
    }

    public function test_expiry_is_recorded_once_and_never_marks_paid(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->json('data.code');
        $this->travel(25)->hours();
        $this->getJson('/api/v1/tienda/pedidos/'.$code.'?status=paid')->assertOk()->assertJsonPath('data.status', 'expired')->assertJsonPath('data.paid_at', null);
        $this->getJson('/api/v1/tienda/pedidos/'.$code)->assertOk()->assertJsonCount(2, 'data.history');
        $this->postJson('/api/v1/tienda/pedidos/'.$code, ['status' => 'paid'])->assertMethodNotAllowed();
        $this->travelBack();
    }

    public function test_ineligible_unpublished_expired_cart_and_incomplete_profile_are_blocked(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        DB::table('tienda_conceptos_catalogo')->update(['requires_eligibility_check' => true]);
        $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->assertUnprocessable();
        DB::table('tienda_conceptos_catalogo')->update(['requires_eligibility_check' => false, 'is_published' => false]);
        $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->assertUnprocessable();
        DB::table('tienda_conceptos_catalogo')->update(['is_published' => true]);
        DB::table('tienda_carritos')->where('id', $cart)->update(['expires_at' => now()->subMinute()]);
        $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->assertNotFound();
        DB::table('tienda_carritos')->where('id', $cart)->update(['expires_at' => now()->addDay()]);
        DB::table('tienda_perfiles_compradores')->where('user_id', $buyer->id)->update(['document_number' => null]);
        $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->assertUnprocessable();
        $this->assertDatabaseCount('tienda_pedidos', 0);
    }

    public function test_history_page_renders_for_an_authenticated_buyer(): void
    {
        $this->actingAs(User::factory()->create())->get('/tienda/mis-pedidos')
            ->assertOk()->assertSee('id="ordersList"', false)->assertSee('js/undc-orders.js');
    }

    public function test_guest_cannot_query_or_create_orders(): void
    {
        $this->getJson('/api/v1/tienda/pedidos')->assertUnauthorized();
        $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => 1])->assertUnauthorized();
    }
    public function test_pending_order_returns_to_cart_once_and_reconfirmation_creates_new_snapshot(): void
    {
        [, $cart] = $this->buyerCart();
        $order = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->assertCreated()->json('data');
        $this->assertTrue($order['can_modify']);
        $url = '/api/v1/tienda/pedidos/'.$order['code'].'/modificar';
        $newCart = $this->postJson($url)->assertOk()->json('data.cart_id');
        $this->assertNotEquals($cart, $newCart);
        $this->postJson($url)->assertOk()->assertJsonPath('data.cart_id', $newCart);
        $this->assertDatabaseCount('tienda_carritos', 2);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 2);
        $this->getJson('/api/v1/tienda/pedidos/'.$order['code'])->assertOk()
            ->assertJsonPath('data.status', 'cancelled')->assertJsonPath('data.can_modify', false)
            ->assertJsonPath('data.total_amount', fn ($amount) => (float) $amount === 50.20);
        $this->getJson('/api/v1/tienda/carritos/actual')->assertOk()
            ->assertJsonPath('data.id', $newCart)->assertJsonPath('data.items_count', 2);
        $this->postJson('/api/v1/tienda/carritos/actual/items', ['catalog_item_id' => 101, 'quantity' => 1])->assertOk();
        $updated = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $newCart])->assertCreated()
            ->assertJsonPath('data.total_amount', '75.30')->json('data');
        $this->assertNotSame($order['code'], $updated['code']);
        $this->assertDatabaseHas('tienda_detalles_pedido', ['quantity' => 2, 'total_amount' => '50.20']);
        $this->assertDatabaseHas('tienda_detalles_pedido', ['quantity' => 3, 'total_amount' => '75.30']);
        $this->postJson($url)->assertStatus(409);
        $this->assertDatabaseCount('tienda_pedidos', 2);
    }

    public function test_modification_does_not_overwrite_other_carts_or_bypass_ownership(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->json('data.code');
        $url = '/api/v1/tienda/pedidos/'.$code.'/modificar';
        $this->actingAs(User::factory()->create())->postJson($url)->assertNotFound();
        $this->actingAs($buyer);
        $active = DB::table('tienda_carritos')->insertGetId(['user_id' => $buyer->id, 'status' => 'active', 'currency' => 'PEN']);
        DB::table('tienda_detalles_carrito')->insert(['cart_id' => $active, 'catalog_item_id' => 101, 'quantity' => 1]);
        $this->postJson($url)->assertStatus(409);
        $this->assertDatabaseHas('tienda_pedidos', ['code' => $code, 'status' => 'pending']);
        $this->assertDatabaseHas('tienda_detalles_carrito', ['cart_id' => $active, 'quantity' => 1]);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 1);
    }

    public function test_paid_expired_and_payment_in_progress_orders_cannot_be_modified(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->json('data.code');
        $query = DB::table('tienda_pedidos')->where('code', $code);
        $id = (int) $query->value('id');
        $url = '/api/v1/tienda/pedidos/'.$code.'/modificar';
        foreach ([['status' => 'paid'], ['status' => 'cancelled'], ['status' => 'confirmed'],
            ['status' => 'pending', 'paid_at' => now()],
            ['status' => 'pending', 'paid_at' => null, 'confirmed_at' => now()],
            ['status' => 'pending', 'confirmed_at' => null, 'expires_at' => now()->subMinute()]] as $state) {
            (clone $query)->update($state);
            $this->postJson($url)->assertStatus(409);
        }
        (clone $query)->update(['status' => 'pending', 'expires_at' => now()->addDay()]);
        DB::table('tienda_intentos_pago')->insert([
            'order_id' => $id, 'channel' => 'gateway', 'provider' => 'demo', 'status' => 'initiated',
            'idempotency_key' => 'test-only', 'requested_amount' => '50.20', 'currency' => 'PEN', 'started_at' => now(),
        ]);
        $this->getJson('/api/v1/tienda/pedidos/'.$code)->assertOk()->assertJsonPath('data.can_modify', false);
        $this->postJson($url)->assertStatus(409);
        DB::table('tienda_intentos_pago')->delete();
        DB::table('tienda_transacciones_pago')->insert([
            'order_id' => $id, 'provider' => 'demo', 'status' => 'pending_verification',
            'amount' => '50.20', 'currency' => 'PEN', 'observed_at' => now(),
        ]);
        $this->postJson($url)->assertStatus(409);
        $this->assertDatabaseCount('tienda_carritos', 1);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 1);
    }

    public function test_anonymous_selection_is_not_lost_when_modifying_an_order(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['cart_id' => $cart])->json('data.code');
        $created = app(\App\Modules\Pedidos\Services\CartService::class)->create();
        app(\App\Modules\Pedidos\Services\CartService::class)->add($created['token'], 101, 1);
        $this->postJson('/api/v1/tienda/pedidos/'.$code.'/modificar', ['cart_token' => $created['token']])->assertStatus(409);
        $this->assertDatabaseHas('tienda_pedidos', ['code' => $code, 'status' => 'pending']);
        $this->assertDatabaseHas('tienda_detalles_carrito', ['cart_id' => $created['cart']['id'], 'quantity' => 1]);
    }

}
