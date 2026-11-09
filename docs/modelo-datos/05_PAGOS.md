# 5. Pagos

Intentos, transacciones, cobro aplicado, webhooks e incidencias. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  tienda_intentos_pago {
    BIGINT id PK
  }
  tienda_transacciones_pago {
    BIGINT id PK
  }
  tienda_pagos_aplicados_pedidos {
    BIGINT id PK
  }
  tienda_eventos_notificacion_pago {
    BIGINT id PK
  }
  tienda_incidencias_pago {
    BIGINT id PK
  }
  tienda_reembolsos {
    BIGINT id PK
  }
  tienda_pedidos ||--o{ tienda_intentos_pago : "order_id"
  tienda_pedidos ||--o{ tienda_transacciones_pago : "order_id"
  tienda_intentos_pago ||--o{ tienda_transacciones_pago : "payment_attempt_id nullable"
  tienda_pedidos ||--o| tienda_pagos_aplicados_pedidos : "order_id"
  tienda_transacciones_pago ||--o| tienda_pagos_aplicados_pedidos : "payment_transaction_id"
  tienda_intentos_pago ||--o{ tienda_eventos_notificacion_pago : "payment_attempt_id nullable"
  tienda_pedidos ||--o{ tienda_incidencias_pago : "order_id"
  tienda_transacciones_pago ||--o{ tienda_incidencias_pago : "payment_transaction_id nullable"
  users ||--o{ tienda_incidencias_pago : "resolved_by nullable"
  tienda_transacciones_pago ||--o{ tienda_reembolsos : "payment_transaction_id"
  users ||--o{ tienda_reembolsos : "requested_by nullable"
  tienda_transacciones_pago ||--o{ tienda_detalles_conciliacion : "payment_transaction_id nullable"
  tienda_transacciones_pago ||--o{ tienda_vinculos_sistemas_externos : "payment_transaction_id nullable"
  tienda_transacciones_pago ||--o{ tienda_validaciones_pago : "payment_transaction_id"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
