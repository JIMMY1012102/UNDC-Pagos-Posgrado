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
            'tienda_categorias', 'tienda_configuracion_oficinas', 'tienda_conceptos_catalogo', 'tienda_versiones_tarifas',
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
            'legacy_office_id' => 7, 'slug' => 'posgrado', 'catalogo_habilitado' => true,
        ]);
        DB::table('tienda_conceptos_catalogo')->insert([
            'legacy_office_id' => 7, 'legacy_concept_id' => 1,
            'slug' => 'matricula-posgrado', 'esta_publicado' => true,
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
                'usuario_id' => $user->id, 'tipo_documento' => 'DNI',
                'numero_documento' => '00000000',
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
            'pedido_id' => $order, 'transaccion_pago_id' => $first, 'aplicado_en' => now(),
        ]);

        $this->assertSame(2, DB::table('tienda_transacciones_pago')->count());
        $this->expectException(QueryException::class);
        DB::table('tienda_pagos_aplicados_pedidos')->insert([
            'pedido_id' => $order, 'transaccion_pago_id' => $second, 'aplicado_en' => now(),
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
            'proveedor' => 'payme', 'clave_evento' => 'evt-1',
            'payload_digest' => str_repeat('a', 64), 'recibido_en' => now(),
        ];
        DB::table('tienda_eventos_notificacion_pago')->insert($row);
        $this->expectException(QueryException::class);
        DB::table('tienda_eventos_notificacion_pago')->insert($row);
    }

    public function test_reconciliation_can_reference_legacy_bank_result_without_changing_it(): void
    {
        $batch = DB::table('tienda_lotes_conciliacion')->insertGetId([
            'proveedor' => 'interbank', 'canal' => 'bank_import',
        ]);
        $entry = [
            'lote_conciliacion_id' => $batch, 'numero_fila' => 1,
            'legacy_result_id' => 26, 'legacy_recaudacion_id' => 1144,
            'monto_bruto' => 100, 'monto_neto' => 100,
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
            'concepto_catalogo_id' => $item, 'monto' => 100,
            'vigente_desde' => now(),
        ]);
        $order = $this->createOrder();
        DB::table('tienda_detalles_pedido')->insert([
            'pedido_id' => $order, 'concepto_catalogo_id' => $item,
            'legacy_concept_id' => 1, 'legacy_office_id' => 7,
            'codigo_concepto' => 'EJEMPLO', 'concepto_nombre' => 'Concepto ficticio',
            'cantidad' => 1, 'monto_unitario' => 100, 'monto_total' => 100,
            'beneficiario_nombres' => 'Persona',
            'beneficiario_apellidos' => 'Ejemplo',
            'beneficiario_tipo_documento' => 'DNI',
            'beneficiario_numero_documento' => '00000000',
        ]);
        DB::table('tienda_versiones_tarifas')->where('id', $price)->update(['monto' => 120]);
        $this->assertEquals(100, DB::table('tienda_detalles_pedido')->value('monto_unitario'));
    }

    private function createOrder(): int
    {
        $user = User::factory()->create();

        return DB::table('tienda_pedidos')->insertGetId([
            'codigo' => 'TEST-'.uniqid(),
            'usuario_id' => $user->id, 'monto_total' => 100,
            'comprador_nombres' => 'Persona', 'comprador_apellidos' => 'Ejemplo',
            'comprador_tipo_documento' => 'DNI', 'comprador_numero_documento' => '00000000',
            'comprador_correo' => $user->email,
        ]);
    }

    private function transaction(int $order, string $provider, string $ref): int
    {
        return DB::table('tienda_transacciones_pago')->insertGetId([
            'pedido_id' => $order, 'proveedor' => $provider,
            'referencia_transaccion_proveedor' => $ref,
            'monto' => 100, 'estado' => 'confirmed',
            'observado_en' => now(), 'verificado_en' => now(),
        ]);
    }
}
