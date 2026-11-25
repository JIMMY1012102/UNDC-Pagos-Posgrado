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
        DB::table('tienda_configuracion_oficinas')->insert(['legacy_office_id' => 7, 'slug' => 'prueba', 'catalogo_habilitado' => true]);
        DB::table('tienda_conceptos_catalogo')->insert(['id' => 101, 'legacy_office_id' => 7, 'legacy_concept_id' => 12, 'slug' => 'concepto', 'esta_publicado' => true]);
        DB::table('tienda_versiones_tarifas')->insert(['concepto_catalogo_id' => 101, 'monto' => '25.10', 'moneda' => 'PEN', 'vigente_desde' => now()->subDay(), 'esta_aprobado' => $approved, 'referencia_aprobacion' => 'PRUEBA-NO-REAL']);
        $buyer = User::factory()->create();
        DB::table('tienda_perfiles_compradores')->insert(['usuario_id' => $buyer->id, 'first_names' => 'Persona', 'last_names' => 'Prueba', 'document_type' => 'DNI', 'document_number' => '00000000']);
        $id = DB::table('tienda_carritos')->insertGetId(['usuario_id' => $buyer->id, 'estado' => 'active', 'moneda' => 'PEN', 'vence_en' => now()->addDay()]);
        DB::table('tienda_detalles_carrito')->insert(['carrito_id' => $id, 'concepto_catalogo_id' => 101, 'cantidad' => 2]);
        $this->actingAs($buyer);
        return [$buyer, $id];
    }

    public function test_quote_and_order_use_approved_price_not_browser_or_legacy_amount(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        $this->postJson('/api/v1/tienda/pedidos/cotizar', ['carrito_id' => $cart])->assertOk()->assertJsonPath('data.total_amount', '50.20');
        $response = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart, 'monto_total' => '0.01', 'estado' => 'paid', 'usuario_id' => 999]);
        $response->assertCreated()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.payment_enabled', false);
        $this->assertDatabaseHas('tienda_pedidos', ['usuario_id' => $buyer->id, 'estado' => 'pending', 'monto_total' => '50.20', 'pagado_en' => null]);
        $this->assertDatabaseHas('tienda_detalles_pedido', ['cantidad' => 2, 'monto_unitario' => '25.10']);
        $this->assertDatabaseCount('tienda_transacciones_pago', 0);
        $this->assertDatabaseCount('tienda_intentos_pago', 0);
    }

    public function test_retry_returns_same_order_and_preserves_snapshot(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        DB::table('tienda_perfiles_compradores')->where('usuario_id', $buyer->id)->update(['first_names' => 'Cambio']);
        DB::table('tienda_versiones_tarifas')->update(['monto' => '80.00']);
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertCreated()->assertJsonPath('data.code', $code)->assertJsonPath('data.buyer_name', 'Persona Prueba');
        $this->assertDatabaseCount('tienda_pedidos', 1);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 1);
        $this->assertDatabaseHas('tienda_pedidos', ['codigo' => $code, 'monto_total' => '50.20']);
    }

    public function test_official_catalog_price_generates_order_without_duplicate_approval(): void
    {
        [$buyer, $cart] = $this->buyerCart(false);
        $this->postJson('/api/v1/tienda/pedidos/cotizar', ['carrito_id' => $cart])
            ->assertOk()->assertJsonPath('data.total_amount', '1998.00');
        $response = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart, 'monto_total' => '0.01', 'estado' => 'paid']);
        $response->assertCreated()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.payment_enabled', false);
        $this->assertDatabaseHas('tienda_pedidos', ['usuario_id' => $buyer->id, 'monto_total' => '1998.00', 'pagado_en' => null]);
        $this->assertDatabaseHas('tienda_versiones_tarifas', ['concepto_catalogo_id' => 101, 'esta_aprobado' => false]);
        $this->assertDatabaseCount('tienda_intentos_pago', 0);
        DB::table('tm_conceptos')->update(['costo' => '1.00']);
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])
            ->assertCreated()->assertJsonPath('data.code', $response->json('data.code'))
            ->assertJsonPath('data.total_amount', fn ($amount) => (float) $amount === 1998.0);
        $this->assertDatabaseCount('tienda_pedidos', 1);
    }

    public function test_ambiguous_tariffs_and_invalid_catalog_prices_are_blocked(): void
    {
        [, $cart] = $this->buyerCart();
        DB::table('tienda_versiones_tarifas')->insert(['concepto_catalogo_id' => 101, 'monto' => '30.00', 'moneda' => 'PEN', 'vigente_desde' => now()->subDay(), 'esta_aprobado' => true, 'referencia_aprobacion' => 'OTRA-PRUEBA']);
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertUnprocessable();
        DB::table('tienda_versiones_tarifas')->delete();
        foreach (['0.00', '-10.00', null] as $amount) {
            DB::table('tm_conceptos')->update(['costo' => $amount]);
            $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertUnprocessable();
        }
        $this->assertDatabaseCount('tienda_pedidos', 0);
        $this->assertDatabaseHas('tienda_carritos', ['id' => $cart, 'estado' => 'active']);
    }

    public function test_ownership_is_enforced_for_cart_detail_and_history(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/v1/tienda/pedidos/'.$code)->assertNotFound();
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertNotFound();
        $this->getJson('/api/v1/tienda/pedidos')->assertOk()->assertJsonCount(0, 'data.items');
    }

    public function test_expiry_is_recorded_once_and_never_marks_paid(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $this->travel(25)->hours();
        $this->getJson('/api/v1/tienda/pedidos/'.$code.'?status=paid')->assertOk()->assertJsonPath('data.status', 'expired')->assertJsonPath('data.paid_at', null);
        $this->getJson('/api/v1/tienda/pedidos/'.$code)->assertOk()->assertJsonCount(1, 'data.history');
        $this->assertDatabaseHas('tienda_pedidos', ['codigo' => $code, 'estado' => 'pending']);
        $this->artisan('market:expire')->assertSuccessful();
        $this->artisan('market:expire')->assertSuccessful();
        $this->getJson('/api/v1/tienda/pedidos/'.$code)->assertOk()->assertJsonCount(2, 'data.history');
        $this->postJson('/api/v1/tienda/pedidos/'.$code, ['estado' => 'paid'])->assertMethodNotAllowed();
        $this->travelBack();
    }

    public function test_expired_order_is_presented_without_writing_on_detail_or_listing(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $before = DB::table('tienda_pedidos')->where('codigo', $code)->first();
        $this->travel(25)->hours();
        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->getJson('/api/v1/tienda/pedidos/'.$code)->assertOk()
            ->assertJsonPath('data.status', 'expired')->assertJsonPath('data.can_modify', false)
            ->assertJsonPath('data.payment_enabled', false)->assertJsonCount(1, 'data.history');
        $this->getJson('/api/v1/tienda/pedidos')->assertOk()
            ->assertJsonPath('data.items.0.status', 'expired')->assertJsonPath('data.items.0.can_modify', false)
            ->assertJsonCount(1, 'data.items.0.history');

        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        foreach ($queries as $query) {
            $this->assertDoesNotMatchRegularExpression('/\b(insert|update|delete|replace)\b|for update/i', $query['query']);
        }
        $this->assertEquals($before, DB::table('tienda_pedidos')->where('codigo', $code)->first());
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 1);
        $this->travelBack();
    }

    public function test_claim_retires_expired_owned_cart_and_clears_its_token(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        $carts = app(\App\Modules\Pedidos\Services\CartService::class);
        $anonymous = $carts->create();
        DB::table('tienda_carritos')->where('id', $cart)->update([
            'vence_en' => now()->subSecond(), 'token_hash' => hash('sha256', 'retired-token'),
        ]);

        $this->assertSame($anonymous['cart']['id'], $carts->claim($anonymous['token'], $buyer->id)['id']);
        $this->assertDatabaseHas('tienda_carritos', ['id' => $cart, 'estado' => 'expired', 'token_hash' => null]);
    }

    public function test_ineligible_unpublished_expired_cart_and_incomplete_profile_are_blocked(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        DB::table('tienda_conceptos_catalogo')->update(['requiere_verificacion_elegibilidad' => true]);
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertUnprocessable();
        DB::table('tienda_conceptos_catalogo')->update(['requiere_verificacion_elegibilidad' => false, 'esta_publicado' => false]);
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertUnprocessable();
        DB::table('tienda_conceptos_catalogo')->update(['esta_publicado' => true]);
        DB::table('tienda_carritos')->where('id', $cart)->update(['vence_en' => now()->subMinute()]);
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertNotFound();
        DB::table('tienda_carritos')->where('id', $cart)->update(['vence_en' => now()->addDay()]);
        DB::table('tienda_perfiles_compradores')->where('usuario_id', $buyer->id)->update(['document_number' => null]);
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertUnprocessable();
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
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => 1])->assertUnauthorized();
    }
    public function test_pending_order_returns_to_cart_once_and_reconfirmation_creates_new_snapshot(): void
    {
        [, $cart] = $this->buyerCart();
        $order = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertCreated()->json('data');
        $this->assertTrue($order['can_modify']);
        $url = '/api/v1/tienda/pedidos/'.$order['codigo'].'/modificar';
        $newCart = $this->postJson($url)->assertOk()->json('data.cart_id');
        $this->assertNotEquals($cart, $newCart);
        $this->postJson($url)->assertOk()->assertJsonPath('data.cart_id', $newCart);
        $this->assertDatabaseCount('tienda_carritos', 2);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 2);
        $this->getJson('/api/v1/tienda/pedidos/'.$order['codigo'])->assertOk()
            ->assertJsonPath('data.status', 'cancelled')->assertJsonPath('data.can_modify', false)
            ->assertJsonPath('data.total_amount', fn ($amount) => (float) $amount === 50.20);
        $this->getJson('/api/v1/tienda/carritos/actual')->assertOk()
            ->assertJsonPath('data.id', $newCart)->assertJsonPath('data.items_count', 2);
        $this->postJson('/api/v1/tienda/carritos/actual/items', ['concepto_catalogo_id' => 101, 'cantidad' => 1])->assertOk();
        $updated = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $newCart])->assertCreated()
            ->assertJsonPath('data.total_amount', fn ($amount) => (float) $amount === 75.30)->json('data');
        $this->assertNotSame($order['codigo'], $updated['codigo']);
        $this->assertDatabaseHas('tienda_detalles_pedido', ['cantidad' => 2, 'monto_total' => '50.20']);
        $this->assertDatabaseHas('tienda_detalles_pedido', ['cantidad' => 3, 'monto_total' => '75.30']);
        $this->postJson($url)->assertStatus(409);
        $this->assertDatabaseCount('tienda_pedidos', 2);
    }

    public function test_modification_does_not_overwrite_other_carts_or_bypass_ownership(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $url = '/api/v1/tienda/pedidos/'.$code.'/modificar';
        $this->actingAs(User::factory()->create())->postJson($url)->assertNotFound();
        $this->actingAs($buyer);
        $active = DB::table('tienda_carritos')->insertGetId(['usuario_id' => $buyer->id, 'estado' => 'active', 'moneda' => 'PEN']);
        DB::table('tienda_detalles_carrito')->insert(['carrito_id' => $active, 'concepto_catalogo_id' => 101, 'cantidad' => 1]);
        $this->postJson($url)->assertStatus(409);
        $this->assertDatabaseHas('tienda_pedidos', ['codigo' => $code, 'estado' => 'pending']);
        $this->assertDatabaseHas('tienda_detalles_carrito', ['carrito_id' => $active, 'cantidad' => 1]);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 1);
    }

    public function test_paid_expired_and_payment_in_progress_orders_cannot_be_modified(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $query = DB::table('tienda_pedidos')->where('codigo', $code);
        $id = (int) $query->value('id');
        $url = '/api/v1/tienda/pedidos/'.$code.'/modificar';
        foreach ([['estado' => 'paid'], ['estado' => 'cancelled'], ['estado' => 'confirmed'],
            ['estado' => 'pending', 'pagado_en' => now()],
            ['estado' => 'pending', 'pagado_en' => null, 'confirmado_en' => now()],
            ['estado' => 'pending', 'confirmado_en' => null, 'vence_en' => now()->subMinute()]] as $state) {
            (clone $query)->update($state);
            $this->postJson($url)->assertStatus(409);
        }
        (clone $query)->update(['estado' => 'pending', 'vence_en' => now()->addDay()]);
        DB::table('tienda_intentos_pago')->insert([
            'pedido_id' => $id, 'canal' => 'gateway', 'proveedor' => 'demo', 'estado' => 'initiated',
            'idempotency_key' => 'test-only', 'monto_solicitado' => '50.20', 'moneda' => 'PEN', 'iniciado_en' => now(),
        ]);
        $this->getJson('/api/v1/tienda/pedidos/'.$code)->assertOk()->assertJsonPath('data.can_modify', false);
        $this->postJson($url)->assertStatus(409);
        DB::table('tienda_intentos_pago')->delete();
        DB::table('tienda_transacciones_pago')->insert([
            'pedido_id' => $id, 'proveedor' => 'demo', 'estado' => 'pending_verification',
            'monto' => '50.20', 'moneda' => 'PEN', 'observado_en' => now(),
        ]);
        $this->postJson($url)->assertStatus(409);
        $this->assertDatabaseCount('tienda_carritos', 1);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 1);
    }

    public function test_anonymous_selection_is_not_lost_when_modifying_an_order(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $created = app(\App\Modules\Pedidos\Services\CartService::class)->create();
        app(\App\Modules\Pedidos\Services\CartService::class)->add($created['token'], 101, 1);
        $this->postJson('/api/v1/tienda/pedidos/'.$code.'/modificar', ['cart_token' => $created['token']])->assertStatus(409);
        $this->assertDatabaseHas('tienda_pedidos', ['codigo' => $code, 'estado' => 'pending']);
        $this->assertDatabaseHas('tienda_detalles_carrito', ['carrito_id' => $created['cart']['id'], 'cantidad' => 1]);
    }


    public function test_scheduled_expiry_is_idempotent_and_retires_anonymous_carts(): void
    {
        [, $cart] = $this->buyerCart();
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertCreated();
        $anonymous = app(\App\Modules\Pedidos\Services\CartService::class)->create();
        $this->travel(25)->hours();
        $this->artisan('market:expire')->assertSuccessful();
        $this->artisan('market:expire')->assertSuccessful();
        $this->assertDatabaseHas('tienda_pedidos', ['carrito_id' => $cart, 'estado' => 'expired', 'pagado_en' => null]);
        $this->assertDatabaseHas('tienda_carritos', ['id' => $anonymous['cart']['id'], 'estado' => 'expired', 'token_hash' => null]);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 2);
        $this->travelBack();
    }

    public function test_listing_batches_detail_queries_and_preserves_response(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        $orders = app(\App\Modules\Pedidos\Services\OrderService::class);
        $first = $orders->create($buyer->id, $cart);
        for ($i = 0; $i < 10; $i++) {
            $nextCart = DB::table('tienda_carritos')->insertGetId([
                'usuario_id' => $buyer->id, 'estado' => 'active', 'moneda' => 'PEN', 'vence_en' => now()->addDay(),
            ]);
            DB::table('tienda_detalles_carrito')->insert([
                'carrito_id' => $nextCart, 'concepto_catalogo_id' => 101, 'cantidad' => 2,
            ]);
            $first = $orders->create($buyer->id, $nextCart);
        }
        $orderIds = DB::table('tienda_pedidos')->where('usuario_id', $buyer->id)->pluck('id');
        DB::table('tienda_pedidos')->whereIn('id', $orderIds)->update(['vence_en' => now()->subMinute()]);
        foreach ($orderIds as $orderId) {
            DB::table('tienda_intentos_pago')->insert([
                'pedido_id' => $orderId, 'canal' => 'gateway', 'proveedor' => 'test', 'estado' => 'initiated',
                'idempotency_key' => 'listing-review-'.$orderId, 'monto_solicitado' => '50.20',
                'moneda' => 'PEN', 'iniciado_en' => now()->subMinutes(2),
            ]);
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $result = $orders->listing($buyer->id, 1);
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertCount(10, $result['items']);
        $this->assertTrue($result['has_more']);
        $this->assertSame($first['codigo'], $result['items'][0]['codigo']);
        $this->assertSame('payment_in_review', $result['items'][0]['estado']);
        $this->assertCount(10, array_filter($result['items'], fn ($order) => $order['estado'] === 'payment_in_review'));
        $this->assertLessThanOrEqual(9, $queries);
        $this->assertCount(1, $orders->listing($buyer->id, 2)['items']);
    }

    public function test_database_rejects_duplicate_order_for_same_cart(): void
    {
        [, $cart] = $this->buyerCart();
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertCreated();
        $row = (array) DB::table('tienda_pedidos')->first();
        unset($row['id']);
        $row['codigo'] = 'DUPLICATE-TEST';
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('tienda_pedidos')->insert($row);
    }

    public function test_expiry_skips_initiated_and_unknown_payment_attempts(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        DB::table('tienda_intentos_pago')->insert([
            'pedido_id' => $orderId, 'canal' => 'gateway', 'proveedor' => 'test', 'estado' => 'initiated',
            'idempotency_key' => 'expiry-open-test', 'monto_solicitado' => '50.20', 'moneda' => 'PEN', 'iniciado_en' => now(),
        ]);

        $this->travel(25)->hours();
        $this->artisan('market:expire')->assertSuccessful();
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'pending', 'pagado_en' => null]);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 1);

        DB::table('tienda_intentos_pago')->where('pedido_id', $orderId)->update(['estado' => 'future_unknown_status']);
        $this->artisan('market:expire')->assertSuccessful();
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'pending', 'pagado_en' => null]);

        DB::table('tienda_intentos_pago')->where('pedido_id', $orderId)->update(['estado' => 'expired']);
        $this->artisan('market:expire')->assertSuccessful();
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'expired', 'pagado_en' => null]);
        $this->assertDatabaseCount('tienda_historial_estados_pedido', 2);
        $this->travelBack();
    }

    public function test_verified_payment_is_applied_once_and_paid_transition_is_recorded(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'VERIFIED-ONCE',
            'estado' => 'authorized', 'monto' => '50.20', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);

        $service = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class);
        $this->assertSame(['result' => 'applied', 'pedido_id' => $orderId], $service->apply($transactionId));
        $this->assertSame(['result' => 'already_applied', 'pedido_id' => $orderId], $service->apply($transactionId));
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'paid']);
        $this->assertNotNull(DB::table('tienda_pedidos')->where('id', $orderId)->value('pagado_en'));
        $this->assertDatabaseHas('tienda_pagos_aplicados_pedidos', [
            'pedido_id' => $orderId, 'transaccion_pago_id' => $transactionId,
        ]);
        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 1);
        $this->assertDatabaseHas('tienda_historial_estados_pedido', [
            'pedido_id' => $orderId, 'estado_anterior' => 'pending', 'estado_nuevo' => 'paid',
        ]);
    }

    public function test_amount_mismatch_and_late_payment_create_incidents_without_marking_paid(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $mismatchId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'VERIFIED-MISMATCH',
            'estado' => 'authorized', 'monto' => '50.21', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);

        $service = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class);
        $this->assertSame('mismatch', $service->apply($mismatchId)['result']);
        $this->assertDatabaseHas('tienda_incidencias_pago', [
            'pedido_id' => $orderId, 'transaccion_pago_id' => $mismatchId, 'tipo' => 'mismatch', 'estado' => 'open',
        ]);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'pending', 'pagado_en' => null]);

        $currencyMismatchId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'VERIFIED-CURRENCY',
            'estado' => 'authorized', 'monto' => '50.20', 'moneda' => 'USD',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);
        $this->assertSame('mismatch', $service->apply($currencyMismatchId)['result']);
        $this->assertDatabaseHas('tienda_incidencias_pago', [
            'pedido_id' => $orderId, 'transaccion_pago_id' => $currencyMismatchId, 'tipo' => 'mismatch', 'estado' => 'open',
        ]);

        DB::table('tienda_pedidos')->where('id', $orderId)->update(['estado' => 'expired', 'vence_en' => now()->subMinute()]);
        $lateId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'VERIFIED-LATE',
            'estado' => 'settled', 'monto' => '50.20', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);
        $this->assertSame('late_payment', $service->apply($lateId)['result']);
        $this->assertDatabaseHas('tienda_incidencias_pago', [
            'pedido_id' => $orderId, 'transaccion_pago_id' => $lateId, 'tipo' => 'late_payment', 'estado' => 'open',
        ]);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'expired', 'pagado_en' => null]);
        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 0);
    }

    public function test_second_verified_charge_is_recorded_as_duplicate_incident(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $first = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'VERIFIED-FIRST',
            'estado' => 'authorized', 'monto' => '50.20', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);
        $second = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'VERIFIED-SECOND',
            'estado' => 'settled', 'monto' => '50.20', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);

        $service = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class);
        $this->assertSame('applied', $service->apply($first)['result']);
        $this->assertSame('duplicate_charge', $service->apply($second)['result']);
        $this->assertDatabaseHas('tienda_incidencias_pago', [
            'pedido_id' => $orderId, 'transaccion_pago_id' => $second, 'tipo' => 'duplicate_charge', 'estado' => 'open',
        ]);
        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 1);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'paid']);
    }

    public function test_authorized_status_without_server_verification_does_not_mark_order_paid(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'UNVERIFIED-ONLY',
            'estado' => 'authorized', 'monto' => '50.20', 'moneda' => 'PEN', 'observado_en' => now(),
        ]);

        $result = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class)->apply($transactionId);
        $this->assertSame('not_verified', $result['result']);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'pending', 'pagado_en' => null]);
        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 0);
    }


    public function test_payment_started_before_expiry_is_applied_when_verified_after_expiry(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $order = DB::table('tienda_pedidos')->where('codigo', $code)->first();
        $expiresAt = \Illuminate\Support\Carbon::parse($order->expires_at);
        $attemptId = DB::table('tienda_intentos_pago')->insertGetId([
            'pedido_id' => $order->id, 'canal' => 'gateway', 'proveedor' => 'test', 'estado' => 'initiated',
            'idempotency_key' => 'started-before-expiry', 'monto_solicitado' => '50.20', 'moneda' => 'PEN',
            'iniciado_en' => $expiresAt->copy()->subMinute(),
        ]);

        $this->travel(26)->hours();
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $order->id, 'intento_pago_id' => $attemptId, 'proveedor' => 'test',
            'referencia_transaccion_proveedor' => 'VERIFIED-AFTER-EXPIRY', 'estado' => 'authorized',
            'monto' => '50.20', 'moneda' => 'PEN', 'fuente_verificacion' => 'test-server-query',
            'observado_en' => now(), 'verificado_en' => now(),
        ]);

        $result = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class)->apply($transactionId);
        $this->assertSame('applied', $result['result']);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $order->id, 'estado' => 'paid']);
        $this->assertDatabaseHas('tienda_pagos_aplicados_pedidos', [
            'pedido_id' => $order->id, 'transaccion_pago_id' => $transactionId,
        ]);
        $this->assertDatabaseHas('tienda_historial_estados_pedido', [
            'pedido_id' => $order->id, 'estado_anterior' => 'pending', 'estado_nuevo' => 'paid',
        ]);
        $this->travelBack();
    }

    public function test_payment_started_after_expiry_creates_late_payment_incident(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $order = DB::table('tienda_pedidos')->where('codigo', $code)->first();

        $this->travel(26)->hours();
        $attemptId = DB::table('tienda_intentos_pago')->insertGetId([
            'pedido_id' => $order->id, 'canal' => 'gateway', 'proveedor' => 'test', 'estado' => 'initiated',
            'idempotency_key' => 'started-after-expiry', 'monto_solicitado' => '50.20', 'moneda' => 'PEN',
            'iniciado_en' => now(),
        ]);
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $order->id, 'intento_pago_id' => $attemptId, 'proveedor' => 'test',
            'referencia_transaccion_proveedor' => 'VERIFIED-STARTED-LATE', 'estado' => 'authorized',
            'monto' => '50.20', 'moneda' => 'PEN', 'fuente_verificacion' => 'test-server-query',
            'observado_en' => now(), 'verificado_en' => now(),
        ]);

        $result = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class)->apply($transactionId);
        $this->assertSame('late_payment', $result['result']);
        $this->assertDatabaseHas('tienda_incidencias_pago', [
            'pedido_id' => $order->id, 'transaccion_pago_id' => $transactionId, 'tipo' => 'late_payment', 'estado' => 'open',
        ]);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $order->id, 'estado' => 'pending', 'pagado_en' => null]);
        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 0);
        $this->travelBack();
    }

    public function test_expired_pending_order_with_unresolved_payment_is_presented_for_review(): void
    {
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        DB::table('tienda_intentos_pago')->insert([
            'pedido_id' => $orderId, 'canal' => 'gateway', 'proveedor' => 'test', 'estado' => 'initiated',
            'idempotency_key' => 'review-after-expiry', 'monto_solicitado' => '50.20', 'moneda' => 'PEN',
            'iniciado_en' => now(),
        ]);

        $this->travel(25)->hours();
        $this->getJson('/api/v1/tienda/pedidos/'.$code)->assertOk()
            ->assertJsonPath('data.status', 'payment_in_review')
            ->assertJsonPath('data.payment_enabled', false)
            ->assertJsonPath('data.can_modify', false);
        $this->getJson('/api/v1/tienda/pedidos')->assertOk()
            ->assertJsonPath('data.items.0.status', 'payment_in_review')
            ->assertJsonPath('data.items.0.payment_enabled', false);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'pending']);
        $this->travelBack();
    }

    public function test_checkout_disabled_allows_order_without_address_snapshot(): void
    {
        config(['payments.payme_enabled' => false]);
        [$buyer, $cart] = $this->buyerCart();
        $response = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertCreated();
        $this->assertDatabaseHas('tienda_pedidos', [
            'codigo' => $response->json('data.code'),
            'buyer_address_line_1' => null,
            'buyer_address_city' => null,
            'buyer_address_state' => null,
            'buyer_address_country' => 'PE',
        ]);
    }

    public function test_checkout_enabled_requires_address_and_phone_and_snapshots_them(): void
    {
        config(['payments.payme_enabled' => true]);
        [$buyer, $cart] = $this->buyerCart();

        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('profile')
            ->assertJsonPath('errors.profile.0', 'Para continuar con el pago, completa tu dirección y teléfono (incluido el código de país) en tu perfil.');
        $this->assertDatabaseCount('tienda_pedidos', 0);

        DB::table('tienda_perfiles_compradores')->where('usuario_id', $buyer->id)->update([
            'line_1' => 'Av. Prueba 123', 'line_2' => 'Interior 2',
            'city' => 'Mala', 'state' => 'Lima', 'country' => 'PE',
            'phone_country_code' => '+51', 'phone' => '987654321',
        ]);
        $order = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])
            ->assertCreated()->json('data');
        $this->assertDatabaseHas('tienda_pedidos', [
            'codigo' => $order['codigo'],
            'buyer_address_line_1' => 'Av. Prueba 123',
            'buyer_address_line_2' => 'Interior 2',
            'buyer_address_city' => 'Mala',
            'buyer_address_state' => 'Lima',
            'buyer_address_country' => 'PE',
            'buyer_phone_country_code' => '+51',
            'comprador_telefono' => '987654321',
        ]);

        DB::table('tienda_perfiles_compradores')->where('usuario_id', $buyer->id)->update(['line_1' => 'Dirección modificada']);
        $this->assertDatabaseHas('tienda_pedidos', ['codigo' => $order['codigo'], 'buyer_address_line_1' => 'Av. Prueba 123']);
    }


    public function test_legacy_invalid_phone_blocks_enabled_checkout_but_not_disabled_checkout(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        DB::table('tienda_perfiles_compradores')->where('usuario_id', $buyer->id)->update([
            'line_1' => 'Av. Prueba 123', 'city' => 'Mala', 'state' => 'Lima',
            'country' => 'PE', 'phone_country_code' => '+51', 'phone' => '+51 987 654 321',
        ]);
        $profiles = app(\App\Modules\Usuarios\Services\BuyerProfileService::class);
        $this->assertTrue($profiles->isComplete($buyer->id));
        $this->assertFalse($profiles->isCheckoutReady($buyer->id));

        config(['payments.payme_enabled' => true]);
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])
            ->assertUnprocessable()
            ->assertJsonPath('errors.profile.0', 'Para continuar con el pago, completa tu dirección y teléfono (incluido el código de país) en tu perfil.');
        $this->assertDatabaseCount('tienda_pedidos', 0);

        config(['payments.payme_enabled' => false]);
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertCreated();
    }

    public function test_correcting_legacy_phone_through_profile_api_enables_checkout_and_snapshots_normalized_number(): void
    {
        [$buyer, $cart] = $this->buyerCart();
        DB::table('tienda_perfiles_compradores')->where('usuario_id', $buyer->id)->update([
            'line_1' => 'Av. Prueba 123', 'city' => 'Mala', 'state' => 'Lima',
            'country' => 'PE', 'phone_country_code' => '+51', 'phone' => '+51 987 654 321',
        ]);
        $this->actingAs($buyer)->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
            'line_1' => 'Av. Prueba 123', 'city' => 'Mala', 'state' => 'Lima',
            'country' => 'PE', 'phone_country_code' => '+51', 'phone' => '+51 987 654 321',
        ])->assertOk()->assertJsonPath('data.phone', '987654321');

        $profiles = app(\App\Modules\Usuarios\Services\BuyerProfileService::class);
        $this->assertTrue($profiles->isCheckoutReady($buyer->id));
        config(['payments.payme_enabled' => true]);
        $order = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->assertCreated()->json('data');
        $this->assertDatabaseHas('tienda_pedidos', [
            'codigo' => $order['codigo'], 'buyer_phone_country_code' => '+51',
            'comprador_telefono' => '987654321', 'buyer_address_line_1' => 'Av. Prueba 123',
        ]);
    }

    public function test_checkout_readiness_rejects_unknown_iso_country_inserted_directly(): void
    {
        [$buyer] = $this->buyerCart();
        DB::table('tienda_perfiles_compradores')->where('usuario_id', $buyer->id)->update([
            'line_1' => 'Av. Prueba 123', 'city' => 'Mala', 'state' => 'Lima',
            'country' => 'ZZ', 'phone_country_code' => '+51', 'phone' => '987654321',
        ]);
        $this->assertFalse(app(\App\Modules\Usuarios\Services\BuyerProfileService::class)->isCheckoutReady($buyer->id));
    }

    public function test_complete_personal_profile_without_address_can_open_profile_after_checkout_block(): void
    {
        config(['payments.payme_enabled' => true]);
        [$buyer, $cart] = $this->buyerCart();
        $this->assertTrue(app(\App\Modules\Usuarios\Services\BuyerProfileService::class)->isComplete($buyer->id));
        $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])
            ->assertUnprocessable()
            ->assertJsonPath('errors.profile.0', 'Para continuar con el pago, completa tu dirección y teléfono (incluido el código de país) en tu perfil.');
        $this->get('/tienda/perfil')->assertOk()->assertSee('PERFIL DEL COMPRADOR');
    }



    public function test_reversed_verified_transaction_is_not_applied(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'VERIFIED-REVERSED',
            'estado' => 'reversed', 'monto' => '50.20', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);

        $result = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class)->apply($transactionId);

        $this->assertSame('not_verified', $result['result']);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'pending', 'pagado_en' => null]);
        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 0);
        $this->travelBack();
    }

    public function test_reversing_an_applied_transaction_does_not_unmark_the_paid_order(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'VERIFIED-APPLIED-THEN-REVERSED',
            'estado' => 'authorized', 'monto' => '50.20', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);
        $service = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class);

        $this->assertSame('applied', $service->apply($transactionId)['result']);
        $paidAt = DB::table('tienda_pedidos')->where('id', $orderId)->value('pagado_en');
        DB::table('tienda_transacciones_pago')->where('id', $transactionId)->update(['estado' => 'reversed']);

        $this->assertSame('not_verified', $service->apply($transactionId)['result']);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'paid', 'pagado_en' => $paidAt]);
        $this->assertSame(1, DB::table('tienda_pagos_aplicados_pedidos')->where('pedido_id', $orderId)->count());
        $this->assertSame(0, DB::table('tienda_incidencias_pago')->where('transaccion_pago_id', $transactionId)->count());
        $this->travelBack();
    }

    public function test_settled_status_after_authorized_application_is_already_applied(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'AUTHORIZED-THEN-SETTLED',
            'estado' => 'authorized', 'monto' => '50.20', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);
        $service = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class);

        $this->assertSame('applied', $service->apply($transactionId)['result']);
        DB::table('tienda_transacciones_pago')->where('id', $transactionId)->update(['estado' => 'settled']);

        $this->assertSame('already_applied', $service->apply($transactionId)['result']);
        $this->assertSame(1, DB::table('tienda_pagos_aplicados_pedidos')->where('pedido_id', $orderId)->count());
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'paid']);
        $this->travelBack();
    }

    public function test_verified_payment_for_cancelled_order_creates_late_payment_incident(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        DB::table('tienda_pedidos')->where('id', $orderId)->update(['estado' => 'cancelled']);
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'VERIFIED-CANCELLED-ORDER',
            'estado' => 'authorized', 'monto' => '50.20', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);

        $result = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class)->apply($transactionId);

        $this->assertSame('late_payment', $result['result']);
        $this->assertDatabaseHas('tienda_incidencias_pago', [
            'pedido_id' => $orderId, 'transaccion_pago_id' => $transactionId, 'tipo' => 'late_payment', 'estado' => 'open',
        ]);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'cancelled']);
        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 0);
        $this->travelBack();
    }

    public function test_verified_terminal_transaction_statuses_are_not_applied(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $service = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class);

        foreach (['declined', 'expired', 'cancelled'] as $status) {
            $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
                'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'TERMINAL-'.$status,
                'estado' => $status, 'monto' => '50.20', 'moneda' => 'PEN',
                'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
            ]);

            $this->assertSame('not_verified', $service->apply($transactionId)['result']);
            $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'pending', 'pagado_en' => null]);
        }

        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 0);
        $this->travelBack();
    }



    public function test_transaction_without_attempt_uses_observed_at_before_expiry(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $order = DB::table('tienda_pedidos')->where('codigo', $code)->first();
        $expiresAt = \Illuminate\Support\Carbon::parse($order->expires_at);
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $order->id, 'intento_pago_id' => null, 'proveedor' => 'test',
            'referencia_transaccion_proveedor' => 'OBSERVED-BEFORE-EXPIRY', 'estado' => 'authorized',
            'monto' => '50.20', 'moneda' => 'PEN', 'fuente_verificacion' => 'test-server-query',
            'verificado_en' => now(), 'observado_en' => $expiresAt->copy()->subMinute(),
        ]);

        $result = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class)->apply($transactionId);

        $this->assertSame('applied', $result['result']);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $order->id, 'estado' => 'paid']);
        $this->assertDatabaseHas('tienda_pagos_aplicados_pedidos', [
            'pedido_id' => $order->id, 'transaccion_pago_id' => $transactionId,
        ]);
        $this->travelBack();
    }

    public function test_transaction_without_attempt_uses_observed_at_after_expiry(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $order = DB::table('tienda_pedidos')->where('codigo', $code)->first();
        $expiresAt = \Illuminate\Support\Carbon::parse($order->expires_at);
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $order->id, 'intento_pago_id' => null, 'proveedor' => 'test',
            'referencia_transaccion_proveedor' => 'OBSERVED-AFTER-EXPIRY', 'estado' => 'authorized',
            'monto' => '50.20', 'moneda' => 'PEN', 'fuente_verificacion' => 'test-server-query',
            'verificado_en' => now(), 'observado_en' => $expiresAt->copy()->addMinute(),
        ]);

        $result = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class)->apply($transactionId);

        $this->assertSame('late_payment', $result['result']);
        $this->assertDatabaseHas('tienda_incidencias_pago', [
            'pedido_id' => $order->id, 'transaccion_pago_id' => $transactionId, 'tipo' => 'late_payment', 'estado' => 'open',
        ]);
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $order->id, 'estado' => 'pending', 'pagado_en' => null]);
        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 0);
        $this->travelBack();
    }

    public function test_reapplying_the_same_amount_mismatch_keeps_one_open_incident(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $orderId, 'proveedor' => 'test', 'referencia_transaccion_proveedor' => 'REPEATED-MISMATCH',
            'estado' => 'authorized', 'monto' => '50.21', 'moneda' => 'PEN',
            'fuente_verificacion' => 'test-server-query', 'verificado_en' => now(), 'observado_en' => now(),
        ]);
        $service = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class);

        $this->assertSame('mismatch', $service->apply($transactionId)['result']);
        $this->assertSame('mismatch', $service->apply($transactionId)['result']);
        $this->assertSame(1, DB::table('tienda_incidencias_pago')
            ->where('transaccion_pago_id', $transactionId)->where('tipo', 'mismatch')->where('estado', 'open')->count());
        $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'pending', 'pagado_en' => null]);
        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 0);
        $this->travelBack();
    }

    public function test_applying_a_nonexistent_transaction_throws_the_documented_exception(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        $this->buyerCart();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La transacción de pago no existe.');

        app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class)->apply(PHP_INT_MAX);
    }

    public function test_blank_server_verification_source_or_provider_reference_is_not_verified(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-01-15 12:00:00'));
        [, $cart] = $this->buyerCart();
        $code = $this->postJson('/api/v1/tienda/pedidos', ['carrito_id' => $cart])->json('data.code');
        $orderId = (int) DB::table('tienda_pedidos')->where('codigo', $code)->value('id');
        $service = app(\App\Modules\Pagos\Services\ApplyVerifiedPaymentService::class);

        foreach ([
            ['reference' => 'BLANK-SOURCE', 'source' => '   '],
            ['reference' => '   ', 'source' => 'test-server-query'],
        ] as $evidence) {
            $transactionId = DB::table('tienda_transacciones_pago')->insertGetId([
                'pedido_id' => $orderId, 'proveedor' => 'test',
                'referencia_transaccion_proveedor' => $evidence['reference'], 'estado' => 'authorized',
                'monto' => '50.20', 'moneda' => 'PEN', 'fuente_verificacion' => $evidence['source'],
                'verificado_en' => now(), 'observado_en' => now(),
            ]);

            $this->assertSame('not_verified', $service->apply($transactionId)['result']);
            $this->assertDatabaseHas('tienda_pedidos', ['id' => $orderId, 'estado' => 'pending', 'pagado_en' => null]);
        }

        $this->assertDatabaseCount('tienda_pagos_aplicados_pedidos', 0);
        $this->travelBack();
    }

}
