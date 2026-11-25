# 6. Conciliación y Tesorería

Conciliación, validación, certificados y auditoría. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  tienda_lotes_conciliacion {
    BIGINT id PK
  }
  tienda_detalles_conciliacion {
    BIGINT id PK
  }
  tienda_validaciones_pago {
    BIGINT id PK
  }
  tienda_constancias_validacion {
    BIGINT id PK
  }
  tienda_vinculos_sistemas_externos {
    BIGINT id PK
  }
  tienda_eventos_auditoria {
    BIGINT id PK
  }
  tm_usuario ||--o{ tienda_lotes_conciliacion : "legacy_importer_id nullable"
  tienda_lotes_conciliacion ||--o{ tienda_detalles_conciliacion : "lote_conciliacion_id"
  tienda_transacciones_pago ||--o{ tienda_detalles_conciliacion : "transaccion_pago_id nullable"
  tm_resultado ||--o{ tienda_detalles_conciliacion : "legacy_result_id nullable"
  tm_recaudacion ||--o{ tienda_detalles_conciliacion : "legacy_recaudacion_id nullable"
  tienda_pedidos ||--o{ tienda_vinculos_sistemas_externos : "pedido_id nullable"
  tienda_transacciones_pago ||--o{ tienda_vinculos_sistemas_externos : "transaccion_pago_id nullable"
  tienda_pedidos ||--o{ tienda_validaciones_pago : "pedido_id"
  tienda_transacciones_pago ||--o{ tienda_validaciones_pago : "transaccion_pago_id"
  tm_usuario ||--o{ tienda_validaciones_pago : "legacy_reviewer_id nullable"
  tienda_validaciones_pago ||--o| tienda_constancias_validacion : "validacion_pago_id"
  tienda_pedidos ||--o{ tienda_constancias_validacion : "pedido_id"
  tm_usuario ||--o{ tienda_constancias_validacion : "legacy_issuer_id nullable"
  tm_usuario ||--o{ tienda_constancias_validacion : "legacy_revoker_id nullable"
  users ||--o{ tienda_eventos_auditoria : "usuario_comprador_actor_id nullable"
  tm_usuario ||--o{ tienda_eventos_auditoria : "legacy_staff_id nullable"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
