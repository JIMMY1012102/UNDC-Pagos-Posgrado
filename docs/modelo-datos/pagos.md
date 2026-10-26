# Diagrama editable — pagos

```mermaid
erDiagram
  store_payment_attempts {
BIGINT id PK
BIGINT order_id FK
VARCHAR channel
VARCHAR provider
VARCHAR status
VARCHAR idempotency_key
VARCHAR provider_session_reference
DECIMAL requested_amount
CHAR currency
TIMESTAMP started_at
TIMESTAMP finished_at
JSON non_sensitive_metadata
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_payment_transactions {
BIGINT id PK
BIGINT order_id FK
BIGINT payment_attempt_id FK
VARCHAR provider
VARCHAR provider_transaction_reference
VARCHAR status
DECIMAL amount
CHAR currency
VARCHAR verification_source
TIMESTAMP observed_at
TIMESTAMP verified_at
TIMESTAMP settled_at
CHAR evidence_digest
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_order_payment_claims {
BIGINT id PK
BIGINT order_id FK
BIGINT payment_transaction_id FK
TIMESTAMP claimed_at
  }
  store_payment_webhook_events {
BIGINT id PK
VARCHAR provider
VARCHAR event_key
BIGINT payment_attempt_id FK
VARCHAR processing_status
BOOLEAN signature_valid
CHAR payload_digest
TIMESTAMP received_at
TIMESTAMP processed_at
VARCHAR failure_reason
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_payment_incidents {
BIGINT id PK
BIGINT order_id FK
BIGINT payment_transaction_id FK
VARCHAR kind
VARCHAR status
TEXT description
BIGINT resolved_by FK
TIMESTAMP resolved_at
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_refunds {
BIGINT id PK
BIGINT payment_transaction_id FK
VARCHAR provider_refund_reference
DECIMAL amount
CHAR currency
VARCHAR status
VARCHAR reason
BIGINT requested_by FK
TIMESTAMP requested_at
TIMESTAMP completed_at
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_orders ||--o{ store_payment_attempts : "order_id"
  store_orders ||--o{ store_payment_transactions : "order_id"
  store_payment_attempts ||--o{ store_payment_transactions : "payment_attempt_id (opcional)"
  store_orders ||--o| store_order_payment_claims : "order_id"
  store_payment_transactions ||--o| store_order_payment_claims : "payment_transaction_id"
  store_payment_attempts ||--o{ store_payment_webhook_events : "payment_attempt_id (opcional)"
  store_orders ||--o{ store_payment_incidents : "order_id"
  store_payment_transactions ||--o{ store_payment_incidents : "payment_transaction_id (opcional)"
  users ||--o{ store_payment_incidents : "resolved_by (opcional)"
  store_payment_transactions ||--o{ store_refunds : "payment_transaction_id"
  users ||--o{ store_refunds : "requested_by (opcional)"
```

Vista funcional. Consultar el diagrama general para relaciones externas al módulo.


## Convención de cardinalidades
La relación `||--o{` se representa desde el padre hacia cero o muchos hijos; `||--o|` hacia cero o un hijo (FK individual UNIQUE). Cuando la FK del hijo es nullable se señala **(opcional)**; las relaciones a entidades externas al módulo aparecen como nodos de referencia sin alterar sus estructuras. Consultar [matriz completa](./MATRIZ_CARDINALIDADES.md).

