# Diccionario de columnas de tienda

Este documento describe el esquema físico después de la migración
`2026_11_24_000001_rename_store_business_columns_to_spanish.php`.

El alcance se limita a columnas propias de las 23 tablas de negocio `tienda_*`
que ya existían y a la tabla `tienda_categorias` (que no requiere cambios).
No se renombran columnas de las seis tablas históricas `tm_*`, ni de
`users`, tablas de Laravel o Spatie. Tampoco se traducen valores almacenados,
estados, monedas, códigos de proveedor ni claves JSON públicas.

## Equivalencias

| Tabla | Nombre anterior | Nombre físico nuevo |
|---|---|---|
| `tienda_configuracion_oficinas` | `catalog_enabled` | `catalogo_habilitado` |
| | `display_name` | `nombre_visible` |
| `tienda_conceptos_catalogo` | `description` | `descripcion` |
| | `requirements` | `requisitos` |
| | `category` | `categoria_codigo` |
| | `requires_eligibility_check` | `requiere_verificacion_elegibilidad` |
| | `is_published` | `esta_publicado` |
| `tienda_versiones_tarifas` | `catalog_item_id` | `concepto_catalogo_id` |
| | `amount` | `monto` |
| | `currency` | `moneda` |
| | `valid_from` | `vigente_desde` |
| | `valid_until` | `vigente_hasta` |
| | `approval_reference` | `referencia_aprobacion` |
| | `is_approved` | `esta_aprobado` |
| `tienda_perfiles_compradores` | `user_id` | `usuario_id` |
| | `first_names` | `nombres` |
| | `last_names` | `apellidos` |
| | `document_type` | `tipo_documento` |
| | `document_number` | `numero_documento` |
| | `phone` | `telefono` |
| | `contact_email` | `correo_contacto` |
| | `line_1` | `direccion_linea_1` |
| | `line_2` | `direccion_linea_2` |
| | `city` | `ciudad` |
| | `state` | `region` |
| | `country` | `pais` |
| | `phone_country_code` | `codigo_pais_telefono` |
| `tienda_identidades_oauth` | `user_id` | `usuario_id` |
| | `provider` | `proveedor` |
| `tienda_beneficiarios` | `customer_profile_id` | `perfil_comprador_id` |
| | `first_names` | `nombres` |
| | `last_names` | `apellidos` |
| | `document_type` | `tipo_documento` |
| | `document_number` | `numero_documento` |
| | `email` | `correo` |
| | `phone` | `telefono` |
| `tienda_carritos` | `user_id` | `usuario_id` |
| | `currency` | `moneda` |
| | `status` | `estado` |
| | `expires_at` | `vence_en` |
| | `active_user_id` | `usuario_activo_id` |
| `tienda_detalles_carrito` | `cart_id` | `carrito_id` |
| | `catalog_item_id` | `concepto_catalogo_id` |
| | `beneficiary_id` | `beneficiario_id` |
| | `quantity` | `cantidad` |
| `tienda_pedidos` | `code` | `codigo` |
| | `user_id` | `usuario_id` |
| | `cart_id` | `carrito_id` |
| | `status` | `estado` |
| | `currency` | `moneda` |
| | `total_amount` | `monto_total` |
| | `buyer_first_names` | `comprador_nombres` |
| | `buyer_last_names` | `comprador_apellidos` |
| | `buyer_document_type` | `comprador_tipo_documento` |
| | `buyer_document_number` | `comprador_numero_documento` |
| | `buyer_email` | `comprador_correo` |
| | `buyer_phone` | `comprador_telefono` |
| | `expires_at` | `vence_en` |
| | `confirmed_at` | `confirmado_en` |
| | `paid_at` | `pagado_en` |
| | `cancelled_at` | `cancelado_en` |
| | `buyer_address_line_1` | `comprador_direccion_linea_1` |
| | `buyer_address_line_2` | `comprador_direccion_linea_2` |
| | `buyer_address_city` | `comprador_direccion_ciudad` |
| | `buyer_address_state` | `comprador_direccion_region` |
| | `buyer_address_country` | `comprador_direccion_pais` |
| | `buyer_phone_country_code` | `comprador_codigo_pais_telefono` |
| `tienda_detalles_pedido` | `order_id` | `pedido_id` |
| | `catalog_item_id` | `concepto_catalogo_id` |
| | `beneficiary_id` | `beneficiario_id` |
| | `concept_code` | `codigo_concepto` |
| | `concept_name` | `concepto_nombre` |
| | `quantity` | `cantidad` |
| | `unit_amount` | `monto_unitario` |
| | `total_amount` | `monto_total` |
| | `beneficiary_first_names` | `beneficiario_nombres` |
| | `beneficiary_last_names` | `beneficiario_apellidos` |
| | `beneficiary_document_type` | `beneficiario_tipo_documento` |
| | `beneficiary_document_number` | `beneficiario_numero_documento` |
| `tienda_historial_estados_pedido` | `order_id` | `pedido_id` |
| | `actor_user_id` | `usuario_actor_id` |
| | `from_status` | `estado_anterior` |
| | `to_status` | `estado_nuevo` |
| | `reason` | `motivo` |
| | `context` | `contexto` |
| | `occurred_at` | `ocurrido_en` |
| `tienda_intentos_pago` | `order_id` | `pedido_id` |
| | `channel` | `canal` |
| | `provider` | `proveedor` |
| | `status` | `estado` |
| | `provider_session_reference` | `referencia_sesion_proveedor` |
| | `requested_amount` | `monto_solicitado` |
| | `currency` | `moneda` |
| | `started_at` | `iniciado_en` |
| | `finished_at` | `finalizado_en` |
| | `non_sensitive_metadata` | `metadatos_no_sensibles` |
| `tienda_transacciones_pago` | `order_id` | `pedido_id` |
| | `payment_attempt_id` | `intento_pago_id` |
| | `provider` | `proveedor` |
| | `provider_transaction_reference` | `referencia_transaccion_proveedor` |
| | `status` | `estado` |
| | `amount` | `monto` |
| | `currency` | `moneda` |
| | `verification_source` | `fuente_verificacion` |
| | `observed_at` | `observado_en` |
| | `verified_at` | `verificado_en` |
| | `settled_at` | `liquidado_en` |
| `tienda_pagos_aplicados_pedidos` | `order_id` | `pedido_id` |
| | `payment_transaction_id` | `transaccion_pago_id` |
| | `claimed_at` | `aplicado_en` |
| `tienda_eventos_notificacion_pago` | `provider` | `proveedor` |
| | `event_key` | `clave_evento` |
| | `payment_attempt_id` | `intento_pago_id` |
| | `processing_status` | `estado_procesamiento` |
| | `signature_valid` | `firma_valida` |
| | `received_at` | `recibido_en` |
| | `processed_at` | `procesado_en` |
| | `failure_reason` | `motivo_fallo` |
| `tienda_incidencias_pago` | `order_id` | `pedido_id` |
| | `payment_transaction_id` | `transaccion_pago_id` |
| | `kind` | `tipo` |
| | `status` | `estado` |
| | `description` | `descripcion` |
| | `resolved_by` | `resuelto_por_usuario_id` |
| | `resolved_at` | `resuelto_en` |
| `tienda_reembolsos` | `payment_transaction_id` | `transaccion_pago_id` |
| | `provider_refund_reference` | `referencia_reembolso_proveedor` |
| | `amount` | `monto` |
| | `currency` | `moneda` |
| | `status` | `estado` |
| | `reason` | `motivo` |
| | `requested_by` | `solicitado_por_usuario_id` |
| | `requested_at` | `solicitado_en` |
| | `completed_at` | `completado_en` |
| `tienda_lotes_conciliacion` | `provider` | `proveedor` |
| | `channel` | `canal` |
| | `external_batch_reference` | `referencia_lote_externo` |
| | `status` | `estado` |
| | `period_from` | `periodo_desde` |
| | `period_until` | `periodo_hasta` |
| | `reported_total` | `total_reportado` |
| | `currency` | `moneda` |
| | `reconciled_at` | `conciliado_en` |
| `tienda_detalles_conciliacion` | `reconciliation_batch_id` | `lote_conciliacion_id` |
| | `row_number` | `numero_fila` |
| | `payment_transaction_id` | `transaccion_pago_id` |
| | `external_payment_reference` | `referencia_pago_externo` |
| | `debtor_code` | `codigo_deudor` |
| | `concept_code` | `codigo_concepto` |
| | `installment_reference` | `referencia_cuota` |
| | `gross_amount` | `monto_bruto` |
| | `fee_amount` | `monto_comision` |
| | `net_amount` | `monto_neto` |
| | `currency` | `moneda` |
| | `status` | `estado` |
| | `bank_operation_at` | `operacion_bancaria_en` |
| | `matched_at` | `conciliado_en` |
| `tienda_vinculos_sistemas_externos` | `system_code` | `codigo_sistema` |
| | `external_entity` | `entidad_externa` |
| | `external_record_key` | `clave_registro_externo` |
| | `order_id` | `pedido_id` |
| | `payment_transaction_id` | `transaccion_pago_id` |
| | `sync_status` | `estado_sincronizacion` |
| | `last_synced_at` | `ultima_sincronizacion_en` |
| `tienda_validaciones_pago` | `order_id` | `pedido_id` |
| | `payment_transaction_id` | `transaccion_pago_id` |
| | `status` | `estado` |
| | `evidence_reference` | `referencia_evidencia` |
| | `decision_reason` | `motivo_decision` |
| | `reviewed_at` | `revisado_en` |
| `tienda_constancias_validacion` | `payment_validation_id` | `validacion_pago_id` |
| | `order_id` | `pedido_id` |
| | `certificate_code` | `codigo_constancia` |
| | `status` | `estado` |
| | `issued_at` | `emitido_en` |
| | `revoked_at` | `revocado_en` |
| | `revocation_reason` | `motivo_revocacion` |
| `tienda_eventos_auditoria` | `actor_buyer_user_id` | `usuario_comprador_actor_id` |
| | `event_name` | `nombre_evento` |
| | `subject_type` | `tipo_sujeto` |
| | `subject_id` | `sujeto_id` |
| | `correlation_id` | `correlacion_id` |
| | `safe_context` | `contexto_seguro` |
| | `occurred_at` | `ocurrido_en` |

`tienda_categorias` ya tenía sus columnas de negocio en español (`codigo`,
`nombre`, `orden`, `legacy_office_id`; no se altera.

## Columnas que se conservan

Se mantienen `id`, `created_at`, `updated_at`, `slug`, hashes, claves de
idempotencia, digests y referencias técnicas `legacy_*`. También se conserva
`provider_subject` y cualquier identificador de integración que no sea una
columna propia de negocio.

## Consideraciones importantes

- `tienda_conceptos_catalogo.categoria_codigo` es un código de clasificación;
  `categoria_id` sigue siendo la FK a `tienda_categorias.id`. Son conceptos
  distintos y ambos se conservan.
- El índice único conserva el nombre técnico `uq_cart_active_user`, aunque su columna asociada ahora se llama `usuario_activo_id`.\n- `tienda_carritos.usuario_activo_id` sigue siendo una columna generada:
  `CASE WHEN estado = 'active' THEN usuario_id ELSE NULL END`. El valor
  almacenado `active` no se traduce.
- Estados como `pending`, `paid`, `cancelled`, `expired`, `active`,
  monedas como `PEN` y códigos externos no cambian.
- Las claves JSON/API y del frontend siguen en inglés cuando así lo define el
  contrato. La aplicación usa alias/mapeos al leer y escribir; por ejemplo
  `total_amount`, `concept_name`, `category` y `description` no cambian.
- En JOIN entre `tienda_*` y `tm_*`, calificar con alias los nombres que
  pueden coincidir en ambos esquemas, como `estado`, `moneda`,
  `codigo_concepto` y `codigo_deudor`.
- La migración nueva tiene `down()` simétrico. No edita migraciones
  anteriores ni modifica las tablas históricas de SISPAGOS.
