# Diagrama editable — control

```mermaid
erDiagram
  store_external_system_links {
BIGINT id PK
VARCHAR system_code
VARCHAR external_entity
VARCHAR external_record_key
INT legacy_record_id
BIGINT order_id FK
BIGINT payment_transaction_id FK
VARCHAR sync_status
TIMESTAMP last_synced_at
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_payment_validations {
BIGINT id PK
BIGINT order_id FK
BIGINT payment_transaction_id FK
INT legacy_reviewer_id FK
VARCHAR status
VARCHAR evidence_reference
VARCHAR decision_reason
TIMESTAMP reviewed_at
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_validation_certificates {
BIGINT id PK
BIGINT payment_validation_id FK
BIGINT order_id FK
VARCHAR certificate_code
CHAR verification_token_hash
VARCHAR status
INT legacy_issuer_id FK
TIMESTAMP issued_at
INT legacy_revoker_id FK
TIMESTAMP revoked_at
VARCHAR revocation_reason
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_audit_events {
BIGINT id PK
BIGINT actor_buyer_user_id FK
INT legacy_staff_id FK
VARCHAR event_name
VARCHAR subject_type
VARCHAR correlation_id
JSON safe_context
TIMESTAMP occurred_at
  }
  store_orders ||--o{ store_external_system_links : "order_id (opcional)"
  store_payment_transactions ||--o{ store_external_system_links : "payment_transaction_id (opcional)"
  store_orders ||--o{ store_payment_validations : "order_id"
  store_payment_transactions ||--o{ store_payment_validations : "payment_transaction_id"
  tm_usuario ||--o{ store_payment_validations : "legacy_reviewer_id (opcional)"
  store_payment_validations ||--o| store_validation_certificates : "payment_validation_id"
  store_orders ||--o{ store_validation_certificates : "order_id"
  tm_usuario ||--o{ store_validation_certificates : "legacy_issuer_id (opcional)"
  tm_usuario ||--o{ store_validation_certificates : "legacy_revoker_id (opcional)"
  users ||--o{ store_audit_events : "actor_buyer_user_id (opcional)"
  tm_usuario ||--o{ store_audit_events : "legacy_staff_id (opcional)"
```

Vista funcional. Consultar el diagrama general para relaciones externas al módulo.


## Convención de cardinalidades
La relación `||--o{` se representa desde el padre hacia cero o muchos hijos; `||--o|` hacia cero o un hijo (FK individual UNIQUE). Cuando la FK del hijo es nullable se señala **(opcional)**; las relaciones a entidades externas al módulo aparecen como nodos de referencia sin alterar sus estructuras. Consultar [matriz completa](./MATRIZ_CARDINALIDADES.md).

