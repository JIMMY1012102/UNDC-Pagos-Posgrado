<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SpanishColumnNamesTest extends TestCase
{
    use RefreshDatabase;
    public function test_business_columns_are_renamed_without_touching_public_legacy_tables(): void
    {
        $expected = [
            'tienda_configuracion_oficinas' => ['catalogo_habilitado', 'nombre_visible'],
            'tienda_conceptos_catalogo' => ['descripcion', 'requisitos', 'categoria_codigo', 'requiere_verificacion_elegibilidad', 'esta_publicado'],
            'tienda_versiones_tarifas' => ['concepto_catalogo_id', 'monto', 'moneda', 'vigente_desde', 'vigente_hasta', 'referencia_aprobacion', 'esta_aprobado'],
            'tienda_perfiles_compradores' => ['usuario_id', 'nombres', 'apellidos', 'tipo_documento', 'numero_documento', 'telefono', 'correo_contacto', 'direccion_linea_1', 'direccion_linea_2', 'ciudad', 'region', 'pais', 'codigo_pais_telefono'],
            'tienda_identidades_oauth' => ['usuario_id', 'proveedor'],
            'tienda_beneficiarios' => ['perfil_comprador_id', 'nombres', 'apellidos', 'tipo_documento', 'numero_documento', 'correo', 'telefono'],
            'tienda_carritos' => ['usuario_id', 'moneda', 'estado', 'vence_en', 'usuario_activo_id'],
            'tienda_detalles_carrito' => ['carrito_id', 'concepto_catalogo_id', 'beneficiario_id', 'cantidad'],
            'tienda_pedidos' => ['codigo', 'usuario_id', 'carrito_id', 'estado', 'moneda', 'monto_total', 'comprador_nombres', 'comprador_apellidos', 'comprador_tipo_documento', 'comprador_numero_documento', 'comprador_correo', 'comprador_telefono', 'vence_en', 'confirmado_en', 'pagado_en', 'cancelado_en', 'comprador_direccion_linea_1', 'comprador_direccion_linea_2', 'comprador_direccion_ciudad', 'comprador_direccion_region', 'comprador_direccion_pais', 'comprador_codigo_pais_telefono'],
            'tienda_detalles_pedido' => ['pedido_id', 'concepto_catalogo_id', 'beneficiario_id', 'codigo_concepto', 'concepto_nombre', 'cantidad', 'monto_unitario', 'monto_total', 'beneficiario_nombres', 'beneficiario_apellidos', 'beneficiario_tipo_documento', 'beneficiario_numero_documento'],
            'tienda_historial_estados_pedido' => ['pedido_id', 'usuario_actor_id', 'estado_anterior', 'estado_nuevo', 'motivo', 'contexto', 'ocurrido_en'],
            'tienda_intentos_pago' => ['pedido_id', 'canal', 'proveedor', 'estado', 'referencia_sesion_proveedor', 'monto_solicitado', 'moneda', 'iniciado_en', 'finalizado_en', 'metadatos_no_sensibles'],
            'tienda_transacciones_pago' => ['pedido_id', 'intento_pago_id', 'proveedor', 'referencia_transaccion_proveedor', 'estado', 'monto', 'moneda', 'fuente_verificacion', 'observado_en', 'verificado_en', 'liquidado_en'],
            'tienda_pagos_aplicados_pedidos' => ['pedido_id', 'transaccion_pago_id', 'aplicado_en'],
            'tienda_eventos_notificacion_pago' => ['proveedor', 'clave_evento', 'intento_pago_id', 'estado_procesamiento', 'firma_valida', 'recibido_en', 'procesado_en', 'motivo_fallo'],
            'tienda_incidencias_pago' => ['pedido_id', 'transaccion_pago_id', 'tipo', 'estado', 'descripcion', 'resuelto_por_usuario_id', 'resuelto_en'],
            'tienda_reembolsos' => ['transaccion_pago_id', 'referencia_reembolso_proveedor', 'monto', 'moneda', 'estado', 'motivo', 'solicitado_por_usuario_id', 'solicitado_en', 'completado_en'],
            'tienda_lotes_conciliacion' => ['proveedor', 'canal', 'referencia_lote_externo', 'estado', 'periodo_desde', 'periodo_hasta', 'total_reportado', 'moneda', 'conciliado_en'],
            'tienda_detalles_conciliacion' => ['lote_conciliacion_id', 'numero_fila', 'transaccion_pago_id', 'referencia_pago_externo', 'codigo_deudor', 'codigo_concepto', 'referencia_cuota', 'monto_bruto', 'monto_comision', 'monto_neto', 'moneda', 'estado', 'operacion_bancaria_en', 'conciliado_en'],
            'tienda_vinculos_sistemas_externos' => ['codigo_sistema', 'entidad_externa', 'clave_registro_externo', 'pedido_id', 'transaccion_pago_id', 'estado_sincronizacion', 'ultima_sincronizacion_en'],
            'tienda_validaciones_pago' => ['pedido_id', 'transaccion_pago_id', 'estado', 'referencia_evidencia', 'motivo_decision', 'revisado_en'],
            'tienda_constancias_validacion' => ['validacion_pago_id', 'pedido_id', 'codigo_constancia', 'estado', 'emitido_en', 'revocado_en', 'motivo_revocacion'],
            'tienda_eventos_auditoria' => ['usuario_comprador_actor_id', 'nombre_evento', 'tipo_sujeto', 'sujeto_id', 'correlacion_id', 'contexto_seguro', 'ocurrido_en'],
        ];

        foreach ($expected as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "{$table}.{$column} debe existir");
            }
        }

        $this->assertTrue(Schema::hasColumn('tienda_conceptos_catalogo', 'categoria_id'));
        $this->assertFalse(Schema::hasColumn('tienda_conceptos_catalogo', 'category'));
        $this->assertFalse(Schema::hasColumn('tienda_conceptos_catalogo', 'categoria'));
        $this->assertFalse(Schema::hasColumn('tienda_eventos_auditoria', 'subject_id'));
        $this->assertFalse(Schema::hasColumn('tienda_eventos_auditoria', 'correlation_id'));
    }

    public function test_generated_active_cart_owner_tracks_renamed_source_columns(): void
    {
        $user = User::factory()->create();
        $cartId = DB::table('tienda_carritos')->insertGetId([
            'usuario_id' => $user->id,
            'estado' => 'active',
            'moneda' => 'PEN',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame($user->id, (int) DB::table('tienda_carritos')->where('id', $cartId)->value('usuario_activo_id'));

        DB::table('tienda_carritos')->where('id', $cartId)->update(['estado' => 'expired']);
        $this->assertNull(DB::table('tienda_carritos')->where('id', $cartId)->value('usuario_activo_id'));
    }
}
