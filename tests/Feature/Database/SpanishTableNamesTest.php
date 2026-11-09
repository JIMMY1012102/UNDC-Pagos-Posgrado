<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class SpanishTableNamesTest extends TestCase
{
    use RefreshDatabase;

    private const TABLES = [
        'store_office_settings' => 'tienda_configuracion_oficinas',
        'store_catalog_items' => 'tienda_conceptos_catalogo',
        'store_catalog_price_versions' => 'tienda_versiones_tarifas',
        'store_customer_profiles' => 'tienda_perfiles_compradores',
        'store_oauth_identities' => 'tienda_identidades_oauth',
        'store_beneficiaries' => 'tienda_beneficiarios',
        'store_carts' => 'tienda_carritos',
        'store_cart_items' => 'tienda_detalles_carrito',
        'store_orders' => 'tienda_pedidos',
        'store_order_items' => 'tienda_detalles_pedido',
        'store_order_status_history' => 'tienda_historial_estados_pedido',
        'store_payment_attempts' => 'tienda_intentos_pago',
        'store_payment_transactions' => 'tienda_transacciones_pago',
        'store_order_payment_claims' => 'tienda_pagos_aplicados_pedidos',
        'store_payment_webhook_events' => 'tienda_eventos_notificacion_pago',
        'store_payment_incidents' => 'tienda_incidencias_pago',
        'store_refunds' => 'tienda_reembolsos',
        'store_reconciliation_batches' => 'tienda_lotes_conciliacion',
        'store_reconciliation_entries' => 'tienda_detalles_conciliacion',
        'store_external_system_links' => 'tienda_vinculos_sistemas_externos',
        'store_payment_validations' => 'tienda_validaciones_pago',
        'store_validation_certificates' => 'tienda_constancias_validacion',
        'store_audit_events' => 'tienda_eventos_auditoria',
    ];

    public function test_all_business_tables_are_in_spanish_and_round_trip_preserves_rows_and_foreign_keys(): void
    {
        $user = User::factory()->create();
        $profile = DB::table('tienda_perfiles_compradores')->insertGetId([
            'user_id' => $user->id, 'document_type' => 'DNI', 'document_number' => '00000000',
        ]);
        $columns = [];
        foreach (self::TABLES as $old => $new) {
            $this->assertTrue(Schema::hasTable($new));
            $this->assertFalse(Schema::hasTable($old));
            $columns[$new] = Schema::getColumnListing($new);
        }

        $migration = require database_path('migrations/2026_11_09_000001_rename_store_business_tables_to_spanish.php');
        $migration->down();
        foreach (self::TABLES as $old => $new) {
            $this->assertTrue(Schema::hasTable($old));
            $this->assertFalse(Schema::hasTable($new));
            $this->assertSame($columns[$new], Schema::getColumnListing($old));
        }
        $this->assertSame($profile, (int) DB::table('store_customer_profiles')->value('id'));
        $this->assertContains('store_customer_profiles', array_column(Schema::getForeignKeys('store_beneficiaries'), 'foreign_table'));

        $migration->up();
        $this->assertSame($profile, (int) DB::table('tienda_perfiles_compradores')->value('id'));
        $this->assertSame('00000000', DB::table('tienda_perfiles_compradores')->value('document_number'));
        $this->assertContains('tienda_perfiles_compradores', array_column(Schema::getForeignKeys('tienda_beneficiarios'), 'foreign_table'));
        foreach (self::TABLES as $old => $new) {
            $this->assertSame($columns[$new], Schema::getColumnListing($new));
            $this->assertFalse(Schema::hasTable($old));
        }
    }

    public function test_a_destination_collision_is_rejected_before_any_table_is_renamed(): void
    {
        $migration = require database_path('migrations/2026_11_09_000001_rename_store_business_tables_to_spanish.php');
        $migration->down();
        // Colisión en la ÚLTIMA tabla: la validación debe proteger también la primera.
        Schema::create('tienda_eventos_auditoria', function (Blueprint $table): void {
            $table->id();
        });

        try {
            $migration->up();
            $this->fail('El renombrado debía rechazar la tabla de destino existente.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('store_audit_events', $e->getMessage());
            foreach (self::TABLES as $old => $new) {
                $this->assertTrue(Schema::hasTable($old));
                if ($new !== 'tienda_eventos_auditoria') {
                    $this->assertFalse(Schema::hasTable($new));
                }
            }
        } finally {
            Schema::dropIfExists('tienda_eventos_auditoria');
            $migration->up();
        }
    }
}
