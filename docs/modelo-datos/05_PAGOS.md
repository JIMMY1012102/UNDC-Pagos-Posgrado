# 5. Pagos

Intentos, transacciones, cobro aplicado, webhooks e incidencias. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  store_payment_attempts {
    BIGINT id PK
  }
  store_payment_transactions {
    BIGINT id PK
  }
  store_order_payment_claims {
    BIGINT id PK
  }
  store_payment_webhook_events {
    BIGINT id PK
  }
  store_payment_incidents {
    BIGINT id PK
  }
  store_refunds {
    BIGINT id PK
  }
  store_orders ||--o{ store_payment_attempts : "order_id"
  store_orders ||--o{ store_payment_transactions : "order_id"
  store_payment_attempts ||--o{ store_payment_transactions : "payment_attempt_id nullable"
  store_orders ||--o| store_order_payment_claims : "order_id"
  store_payment_transactions ||--o| store_order_payment_claims : "payment_transaction_id"
  store_payment_attempts ||--o{ store_payment_webhook_events : "payment_attempt_id nullable"
  store_orders ||--o{ store_payment_incidents : "order_id"
  store_payment_transactions ||--o{ store_payment_incidents : "payment_transaction_id nullable"
  users ||--o{ store_payment_incidents : "resolved_by nullable"
  store_payment_transactions ||--o{ store_refunds : "payment_transaction_id"
  users ||--o{ store_refunds : "requested_by nullable"
  store_payment_transactions ||--o{ store_reconciliation_entries : "payment_transaction_id nullable"
  store_payment_transactions ||--o{ store_external_system_links : "payment_transaction_id nullable"
  store_payment_transactions ||--o{ store_payment_validations : "payment_transaction_id"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
