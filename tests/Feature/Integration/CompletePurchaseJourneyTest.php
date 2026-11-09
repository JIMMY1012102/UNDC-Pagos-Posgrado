<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Runs only in the isolated GitHub Actions MariaDB service.
 * The six tm_* tables come from a sanitized fixture, never an institutional dump.
 * Covers HTTP routes, controllers, services and real InnoDB tables in one journey.
 */
class CompletePurchaseJourneyTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'mariadb' || env('UNDC_INTEGRATION_FIXTURE') !== '1') {
            $this->markTestSkipped('Requires the disposable sanitized MariaDB integration fixture.');
        }

        DB::table('tm_oficina')->updateOrInsert(
            ['oficina_id' => 7], ['oficina_nom' => 'Posgrado de prueba', 'estado' => 1]
        );
        DB::table('tm_conceptos')->updateOrInsert(
            ['id_concepto' => 1001],
            ['codigo_concepto' => 'E2E-TEST', 'concepto' => 'Trámite ficticio', 'costo' => 15.00]
        );
        DB::table('store_office_settings')->updateOrInsert(
            ['legacy_office_id' => 7],
            ['slug' => 'posgrado-e2e', 'catalog_enabled' => true]
        );
        DB::table('store_catalog_items')->updateOrInsert(
            ['legacy_office_id' => 7, 'legacy_concept_id' => 1001],
            ['slug' => 'e2e-test', 'is_published' => true]
        );
    }

    public function test_anonymous_catalog_cart_login_profile_and_review_use_real_mariadb(): void
    {
        $catalog = $this->getJson('/api/v1/tienda/catalogo?office_id=7')->assertOk();
        $item = collect($catalog->json('data'))->firstWhere('code', 'E2E-TEST');
        $this->assertNotNull($item, 'A published legacy concept must appear in the public API.');

        $created = $this->postJson('/api/v1/tienda/carritos')->assertCreated();
        $token = $created->json('data.token');
        $this->assertSame(64, strlen($token));

        $this->withToken($token)->postJson('/api/v1/tienda/carritos/actual/items', [
            'catalog_item_id' => $item['id'], 'quantity' => 2,
        ])->assertOk()->assertJsonPath('data.items_count', 2);

        $this->getJson('/api/v1/tienda/precompra/resumen')->assertUnauthorized();

        // Real Google tokens cannot be used until institutional OAuth is configured.
        // actingAs simulates the result of successful Google identity resolution.
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->postJson('/api/v1/tienda/precompra/vincular-carrito', [
            'cart_token' => $token,
        ])->assertOk()->assertJsonPath('data.items_count', 2);

        $this->getJson('/api/v1/tienda/precompra/resumen')->assertOk()
            ->assertJsonPath('data.next_step', 'complete_profile');

        $this->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Persona', 'last_names' => 'De Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
            'phone' => '900000000', 'contact_email' => 'test@example.org',
        ])->assertOk();

        $this->getJson('/api/v1/tienda/precompra/resumen')->assertOk()
            ->assertJsonPath('data.next_step', 'review')
            ->assertJsonPath('data.profile.document_number', '00000000')
            ->assertJsonPath('data.cart.items_count', 2)
            ->assertJsonPath('data.checkout_enabled', false)
            ->assertJsonPath('data.total_amount', null);

        $this->assertDatabaseHas('store_carts', ['user_id' => $buyer->id, 'token_hash' => null]);
        $this->assertDatabaseCount('store_orders', 0);
        $this->assertDatabaseCount('store_payment_transactions', 0);
        $this->withToken($token)->getJson('/api/v1/tienda/carritos/actual')->assertNotFound();
        $this->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => $token])->assertNotFound();

        // A new login with the same buyer does not re-prompt the registration.
        $this->flushSession();
        $this->actingAs($buyer->fresh())->getJson('/api/v1/tienda/precompra/resumen')
            ->assertJsonPath('data.next_step', 'review');
        $this->assertSame(1, DB::table('store_customer_profiles')->where('user_id', $buyer->id)->count());
    }

    public function test_invalid_expired_and_reused_tokens_cannot_claim_a_cart(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer)
            ->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => 'invalid'])
            ->assertUnprocessable();

        $token = $this->postJson('/api/v1/tienda/carritos')->json('data.token');
        DB::table('store_carts')->where('token_hash', hash('sha256', $token))
            ->update(['expires_at' => now()->subSecond()]);
        $this->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => $token])
            ->assertNotFound();

        $new = $this->postJson('/api/v1/tienda/carritos')->json('data.token');
        $this->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => $new])
            ->assertOk();
        $this->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => $new])
            ->assertNotFound();

        $this->assertDatabaseCount('store_orders', 0);
        $this->assertDatabaseCount('store_payment_transactions', 0);
    }

    public function test_returning_buyer_can_recover_previous_cart_without_registering_again(): void
    {
        $buyer = User::factory()->create();
        $token = $this->postJson('/api/v1/tienda/carritos')->json('data.token');
        $this->actingAs($buyer)->postJson('/api/v1/tienda/precompra/vincular-carrito', [
            'cart_token' => $token,
        ])->assertOk();

        $this->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Cliente', 'last_names' => 'Recurrente',
            'document_type' => 'DNI', 'document_number' => '00000001',
        ])->assertOk();

        $this->flushSession();
        $this->actingAs($buyer->fresh())->getJson('/api/v1/acceso/estado')
            ->assertJsonPath('data.profile_complete', true);
        $this->getJson('/api/v1/tienda/precompra/resumen')
            ->assertJsonPath('data.profile.document_number', '00000001')
            ->assertJsonPath('data.cart.items_count', 0);

        $this->assertSame(1, DB::table('store_customer_profiles')->where('user_id', $buyer->id)->count());
    }

    public function test_one_buyers_claimed_cart_is_not_accessible_from_another_account(): void
    {
        $created = $this->postJson('/api/v1/tienda/carritos')->assertCreated();
        $token = $created->json('data.token');
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first)->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => $token])
            ->assertOk();

        $this->actingAs($second)->getJson('/api/v1/tienda/precompra/resumen')
            ->assertOk()->assertJsonPath('data.cart', null);
        $this->actingAs($second)->postJson('/api/v1/tienda/precompra/vincular-carrito', ['cart_token' => $token])
            ->assertNotFound();
    }
}
