<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentsSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_expected_modules_are_migrated_without_touching_legacy_tables(): void
    {
        foreach ([
            'offices', 'staff_roles', 'staff_role_assignments', 'customer_profiles',
            'oauth_identities', 'beneficiaries', 'concepts', 'concept_prices',
            'carts', 'cart_items', 'orders', 'order_items', 'order_status_history',
            'payment_attempts', 'payment_transactions', 'order_payment_claims',
            'payment_webhook_events', 'payment_incidents', 'refunds',
            'reconciliation_batches', 'reconciliation_entries', 'external_system_links',
            'payment_validations', 'validation_certificates', 'audit_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }

        $this->assertFalse(Schema::hasTable('tm_recaudacion'));
        $this->assertFalse(Schema::hasTable('tm_resultado'));
    }

    public function test_two_customer_profiles_can_have_the_same_document(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        foreach ([$first, $second] as $user) {
            DB::table('customer_profiles')->insert([
                'user_id' => $user->id,
                'document_type' => 'DNI',
                'document_number' => '12345678',
            ]);
        }

        $this->assertSame(2, DB::table('customer_profiles')
            ->where('document_number', '12345678')->count());
    }

    public function test_a_confirmed_order_cannot_claim_two_different_transactions(): void
    {
        $orderId = $this->createOrder();
        $firstTransaction = $this->createPaymentTransaction($orderId, 'payme', 'TX-001');
        $secondTransaction = $this->createPaymentTransaction($orderId, 'interbank', 'BANK-002');

        DB::table('order_payment_claims')->insert([
            'order_id' => $orderId,
            'payment_transaction_id' => $firstTransaction,
            'claimed_at' => now(),
        ]);

        $this->expectException(QueryException::class);
        DB::table('order_payment_claims')->insert([
            'order_id' => $orderId,
            'payment_transaction_id' => $secondTransaction,
            'claimed_at' => now(),
        ]);
    }

    public function test_provider_transaction_reference_is_idempotent(): void
    {
        $orderId = $this->createOrder();
        $this->createPaymentTransaction($orderId, 'payme', 'SAME-ID');

        $this->expectException(QueryException::class);
        $this->createPaymentTransaction($orderId, 'payme', 'SAME-ID');
    }

    public function test_repeated_gateway_event_cannot_be_inserted_twice(): void
    {
        $event = [
            'provider' => 'payme',
            'event_key' => 'event-001',
            'payload_digest' => str_repeat('a', 64),
            'received_at' => now(),
        ];
        DB::table('payment_webhook_events')->insert($event);

        $this->expectException(QueryException::class);
        DB::table('payment_webhook_events')->insert($event);
    }

    public function test_bank_rows_have_unique_numbers_within_each_batch(): void
    {
        $batchId = DB::table('reconciliation_batches')->insertGetId([
            'provider' => 'interbank',
            'channel' => 'bank_import',
        ]);

        $row = [
            'reconciliation_batch_id' => $batchId,
            'row_number' => 1,
            'gross_amount' => 100,
            'net_amount' => 100,
        ];
        DB::table('reconciliation_entries')->insert($row);

        $this->expectException(QueryException::class);
        DB::table('reconciliation_entries')->insert($row);
    }

    public function test_order_snapshot_does_not_depend_on_later_concept_price_changes(): void
    {
        $buyer = User::factory()->create();
        $officeId = DB::table('offices')->insertGetId([
            'code' => 'POSGRADO',
            'slug' => 'posgrado',
            'name' => 'Escuela de Posgrado',
        ]);
        $conceptId = DB::table('concepts')->insertGetId([
            'office_id' => $officeId,
            'code' => 'TEST-01',
            'slug' => 'test-01',
            'name' => 'Concepto de prueba',
        ]);
        $priceId = DB::table('concept_prices')->insertGetId([
            'concept_id' => $conceptId,
            'amount' => 100,
            'valid_from' => now(),
        ]);
        $orderId = $this->createOrder($buyer, 100);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'concept_id' => $conceptId,
            'office_id' => $officeId,
            'concept_code' => 'TEST-01',
            'concept_name' => 'Concepto de prueba',
            'quantity' => 1,
            'unit_amount' => 100,
            'total_amount' => 100,
            'beneficiary_first_names' => 'Persona',
            'beneficiary_last_names' => 'Ejemplo',
            'beneficiary_document_type' => 'DNI',
            'beneficiary_document_number' => '00000000',
        ]);

        DB::table('concept_prices')->where('id', $priceId)->update(['amount' => 120]);

        $this->assertEquals(100, DB::table('order_items')
            ->where('order_id', $orderId)->value('unit_amount'));
    }

    private function createOrder(?User $user = null, int $amount = 100): int
    {
        $buyer = $user ?? User::factory()->create();

        return DB::table('orders')->insertGetId([
            'code' => 'TEST-'.uniqid(),
            'user_id' => $buyer->id,
            'total_amount' => $amount,
            'buyer_first_names' => 'Persona',
            'buyer_last_names' => 'Prueba',
            'buyer_document_type' => 'DNI',
            'buyer_document_number' => '00000000',
            'buyer_email' => $buyer->email,
        ]);
    }

    private function createPaymentTransaction(int $orderId, string $provider, string $reference): int
    {
        return DB::table('payment_transactions')->insertGetId([
            'order_id' => $orderId,
            'provider' => $provider,
            'provider_transaction_reference' => $reference,
            'amount' => 100,
            'status' => 'confirmed',
            'observed_at' => now(),
            'verified_at' => now(),
        ]);
    }
}
