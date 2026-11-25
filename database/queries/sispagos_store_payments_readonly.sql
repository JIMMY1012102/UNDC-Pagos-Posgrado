-- CONSULTA DE REFERENCIA PARA SISPAGOS (SOLO LECTURA).
-- Requiere en la MISMA BD: 6 tablas tm_* originales + migraciones originales y renombrado a tienda_*.
-- No modifica datos, no reemplaza el módulo actual, no valida un pago por sí sola.
-- La aplicación que ejecute la consulta DEBE verificar permisos internos por oficina.
--
-- Muestra UNA fila por línea de pedido. El importe de la transacción es del
-- PEDIDO completo, NO de cada línea. NO sumar payment_amount por líneas.

SELECT
    o.id AS store_order_id,
    o.codigo AS store_order_code,
    o.created_at AS order_created_at,
    o.estado AS order_status,
    oi.id AS store_order_item_id,
    oi.legacy_concept_id AS id_concepto,
    c.codigo_concepto AS codigo_concepto_actual,
    oi.codigo_concepto AS codigo_concepto_al_comprar,
    oi.concepto_nombre AS concepto_al_comprar,
    oi.cantidad AS cantidad,
    oi.monto_unitario AS precio_unitario_al_comprar,
    oi.monto_total AS importe_linea,
    oi.legacy_office_id AS oficina_id,
    ofi.oficina_nom AS oficina_actual,
    oi.beneficiario_tipo_documento AS beneficiario_documento_tipo,
    oi.beneficiario_numero_documento AS beneficiario_documento_numero,
    oi.beneficiario_nombres AS beneficiario_nombres,
    oi.beneficiario_apellidos AS beneficiario_apellidos,
    o.monto_total AS importe_pedido,
    o.moneda AS moneda_pedido,
    p.id AS store_payment_transaction_id,
    p.proveedor AS proveedor_cobro,
    p.referencia_transaccion_proveedor AS referencia_proveedor,
    p.monto AS importe_transaccion_pedido,
    p.moneda AS moneda_transaccion,
    p.verificado_en AS verificado_en,
    CASE
        WHEN applied.transaccion_pago_id IS NOT NULL
            AND p.verificado_en IS NOT NULL
            AND p.estado = 'confirmed' THEN 'COBRO_APLICADO'
        WHEN applied.transaccion_pago_id IS NOT NULL THEN 'REVISAR_COBRO'
        ELSE 'SIN_COBRO_APLICADO'
    END AS estado_cobro_tienda
FROM tienda_pedidos AS o
INNER JOIN tienda_detalles_pedido AS oi ON oi.pedido_id = o.id
LEFT JOIN tm_conceptos AS c ON c.id_concepto = oi.legacy_concept_id
LEFT JOIN tm_oficina AS ofi ON ofi.oficina_id = oi.legacy_office_id
LEFT JOIN tienda_pagos_aplicados_pedidos AS applied ON applied.pedido_id = o.id
LEFT JOIN tienda_transacciones_pago AS p
       ON p.id = applied.transaccion_pago_id
-- Filtros de ejemplo (parametrizarlos en SISPAGOS; no concatenar desde navegador):
-- WHERE oi.legacy_office_id = :office_id
--   AND o.created_at >= :from_date
--   AND o.created_at < :until_date
ORDER BY o.created_at DESC, o.id DESC, oi.id ASC;

-- Importante:
-- 1. Un cobro aplicado NO significa dinero liquidado por el banco ni validado
--    por Tesorería. Consultar conciliación y tienda_validaciones_pago aparte.
-- 2. Comparar importe/moneda reales e idempotencia ANTES de crear applied.
-- 3. No escribir automáticamente en tm_resultado: corresponde a resultados
--    bancarios de otro canal, según el archivo entregado.
-- 4. Resolver autorización de personal tm_usuario antes de exponer el reporte.
-- 5. Consultar campos sensibles solo con mínimo privilegio y máscara por rol.
