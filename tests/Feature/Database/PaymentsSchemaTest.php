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

    public function test_store_migrations_do_not_recreate_or_modify_treasury_master_tables(): void
    {
        foreach ([
            'tienda_configuracion_oficinas', 'tienda_conceptos_catalogo', 'tienda_versiones_tarifas',
            'tienda_perfiles_compradores', 'tienda_identidades_oauth', 'tienda_beneficiarios',
            'tienda_carritos', 'tienda_detalles_carrito', 'tienda_pedidos', 'tienda_detalles_pedido',
            'tienda_historial_estados_pedido', 'tienda_intentos_pago', 'tienda_transacciones_pago',
            'tienda_pagos_aplicados_pedidos', 'tienda_eventos_notificacion_pago',
            'tienda_incidencias_pago', 'tienda_reembolsos', 'tienda_lotes_conciliacion',
            'tienda_detalles_conciliacion', 'tienda_vinculos_sistemas_externos',
            'tienda_validaciones_pago', 'tienda_constancias_validacion', 'tienda_eventos_auditoria',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing new table: {$table}");
        }

        // CI starts without the imported legacy dump and must NOT create a fake copy.
        foreach (['tm_oficina', 'tm_rol', 'tm_usuario', 'tm_conceptos',
            'tm_recaudacion', 'tm_resultado'] as $legacyTable) {
            $this->assertFalse(Schema::hasTable($legacyTable));
        }
    }

    public function test_catalog_maps_explicit_legacy_office_and_concept_ids(): void
    {
        DB::table('tienda_configuracion_oficinas')->insert([
            'legacy_office_id' => 7, 'slug' => 'posgrado', 'catalog_enabled' => true,
        ]);
        DB::table('tienda_conceptos_catalogo')->insert([
            'legacy_office_id' => 7, 'legacy_concept_id' => 1,
            'slug' => 'matricula-posgrado', 'is_published' => true,
        ]);

        $this->assertSame(7, DB::table('tienda_conceptos_catalogo')->value('legacy_office_id'));
        $this->assertSame(1, DB::table('tienda_conceptos_catalogo')->value('legacy_concept_id'));

        $this->expectException(QueryException::class);
        DB::table('tienda_conceptos_catalogo')->insert([
            'legacy_office_id' => 7, 'legacy_concept_id' => 1, 'slug' => 'duplicado',
        ]);
    }

    public function test_buyer_profiles_do_not_enforce_document_uniqueness(): void
    {
        foreach ([User::factory()->create(), User::factory()->create()] as $user) {
            DB::table('tienda_perfiles_compradores')->insert([
                'user_id' => $user->id, 'document_type' => 'DNI',
                'document_number' => '00000000',
            ]);
        }
        $this->assertSame(2, DB::table('tienda_perfiles_compradores')->count());
    }

    public function test_second_payment_must_not_be_applied_to_same_order(): void
    {
        $order = $this->createOrder();
        $first = $this->transaction($order, 'payme', 'TEST-A');
        $second = $this->transaction($order, 'interbank', 'TEST-B');
        DB::table('tienda_pagos_aplicados_pedidos')->insert([
            'order_id' => $order, 'payment_transaction_id' => $first, 'claimed_at' => now(),
        ]);

        $this->assertSame(2, DB::table('tienda_transacciones_pago')->count());
        $this->expectException(QueryException::class);
        DB::table('tienda_pagos_aplicados_pedidos')->insert([
            'order_id' => $order, 'payment_transaction_id' => $second, 'claimed_at' => now(),
        ]);
    }

    public function test_provider_transaction_reference_is_unique(): void
    {
        $order = $this->createOrder();
        $this->transaction($order, 'payme', 'SAME-REF');
        $this->expectException(QueryException::class);
        $this->transaction($order, 'payme', 'SAME-REF');
    }

    public function test_duplicate_webhook_event_is_rejected(): void
    {
        $row = [
            'provider' => 'payme', 'event_key' => 'evt-1',
            'payload_digest' => str_repeat('a', 64), 'received_at' => now(),
        ];
        DB::table('tienda_eventos_notificacion_pago')->insert($row);
        $this->expectException(QueryException::class);
        DB::table('tienda_eventos_notificacion_pago')->insert($row);
    }

    public function test_reconciliation_can_reference_legacy_bank_result_without_changing_it(): void
    {
        $batch = DB::table('tienda_lotes_conciliacion')->insertGetId([
            'provider' => 'interbank', 'channel' => 'bank_import',
        ]);
        $entry = [
            'reconciliation_batch_id' => $batch, 'row_number' => 1,
            'legacy_result_id' => 26, 'legacy_recaudacion_id' => 1144,
            'gross_amount' => 100, 'net_amount' => 100,
        ];
        DB::table('tienda_detalles_conciliacion')->insert($entry);
        $this->assertSame(26, DB::table('tienda_detalles_conciliacion')->value('legacy_result_id'));

        $this->expectException(QueryException::class);
        DB::table('tienda_detalles_conciliacion')->insert($entry);
    }

    public function test_order_item_keeps_price_snapshot_after_catalog_changes(): void
    {
        DB::table('tienda_configuracion_oficinas')->insert([
            'legacy_office_id' => 7, 'slug' => 'posgrado',
        ]);
        $item = DB::table('tienda_conceptos_catalogo')->insertGetId([
            'legacy_office_id' => 7, 'legacy_concept_id' => 1,
            'slug' => 'concepto-prueba',
        ]);
        $price = DB::table('tienda_versiones_tarifas')->insertGetId([
            'catalog_item_id' => $item, 'amount' => 100,
            'valid_from' => now(),
        ]);
        $order = $this->createOrder();
        DB::table('tienda_detalles_pedido')->insert([
            'order_id' => $order, 'catalog_item_id' => $item,
            'legacy_concept_id' => 1, 'legacy_office_id' => 7,
            'concept_code' => 'EJEMPLO', 'concept_name' => 'Concepto ficticio',
            'quantity' => 1, 'unit_amount' => 100, 'total_amount' => 100,
            'beneficiary_first_names' => 'Persona',
            'beneficiary_last_names' => 'Ejemplo',
            'beneficiary_document_type' => 'DNI',
            'beneficiary_document_number' => '00000000',
        ]);
        DB::table('tienda_versiones_tarifas')->where('id', $price)->update(['amount' => 120]);
        $this->assertEquals(100, DB::table('tienda_detalles_pedido')->value('unit_amount'));
    }

    private function createOrder(): int
    {
        $user = User::factory()->create();

        return DB::table('tienda_pedidos')->insertGetId([
            'code' => 'TEST-'.uniqid(),
            'user_id' => $user->id, 'total_amount' => 100,
            'buyer_first_names' => 'Persona', 'buyer_last_names' => 'Ejemplo',
            'buyer_document_type' => 'DNI', 'buyer_document_number' => '00000000',
            'buyer_email' => $user->email,
        ]);
    }

    private function transaction(int $order, string $provider, string $ref): int
    {
        return DB::table('tienda_transacciones_pago')->insertGetId([
            'order_id' => $order, 'provider' => $provider,
            'provider_transaction_reference' => $ref,
            'amount' => 100, 'status' => 'confirmed',
            'observed_at' => now(), 'verified_at' => now(),
        ]);
    }
}
