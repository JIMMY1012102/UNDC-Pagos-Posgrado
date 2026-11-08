# 6. Conciliación y Tesorería

Conciliación, validación, certificados y auditoría. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  store_reconciliation_batches {
    BIGINT id PK
  }
  store_reconciliation_entries {
    BIGINT id PK
  }
  store_payment_validations {
    BIGINT id PK
  }
  store_validation_certificates {
    BIGINT id PK
  }
  store_external_system_links {
    BIGINT id PK
  }
  store_audit_events {
    BIGINT id PK
  }
  tm_usuario ||--o{ store_reconciliation_batches : "legacy_importer_id nullable"
  store_reconciliation_batches ||--o{ store_reconciliation_entries : "reconciliation_batch_id"
  store_payment_transactions ||--o{ store_reconciliation_entries : "payment_transaction_id nullable"
  tm_resultado ||--o{ store_reconciliation_entries : "legacy_result_id nullable"
  tm_recaudacion ||--o{ store_reconciliation_entries : "legacy_recaudacion_id nullable"
  store_orders ||--o{ store_external_system_links : "order_id nullable"
  store_payment_transactions ||--o{ store_external_system_links : "payment_transaction_id nullable"
  store_orders ||--o{ store_payment_validations : "order_id"
  store_payment_transactions ||--o{ store_payment_validations : "payment_transaction_id"
  tm_usuario ||--o{ store_payment_validations : "legacy_reviewer_id nullable"
  store_payment_validations ||--o| store_validation_certificates : "payment_validation_id"
  store_orders ||--o{ store_validation_certificates : "order_id"
  tm_usuario ||--o{ store_validation_certificates : "legacy_issuer_id nullable"
  tm_usuario ||--o{ store_validation_certificates : "legacy_revoker_id nullable"
  users ||--o{ store_audit_events : "actor_buyer_user_id nullable"
  tm_usuario ||--o{ store_audit_events : "legacy_staff_id nullable"
``

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
