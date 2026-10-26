# Diagrama editable — conciliacion

```mermaid
erDiagram
  store_reconciliation_batches {
BIGINT id PK
VARCHAR provider
VARCHAR channel
VARCHAR external_batch_reference
CHAR source_file_digest
VARCHAR status
DATE period_from
DATE period_until
DECIMAL reported_total
CHAR currency
INT legacy_importer_id FK
TIMESTAMP reconciled_at
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_reconciliation_entries {
BIGINT id PK
BIGINT reconciliation_batch_id FK
INT row_number
INT legacy_result_id FK
INT legacy_recaudacion_id FK
BIGINT payment_transaction_id FK
VARCHAR external_payment_reference
VARCHAR debtor_code
VARCHAR concept_code
VARCHAR installment_reference
DECIMAL gross_amount
DECIMAL fee_amount
DECIMAL net_amount
CHAR currency
VARCHAR status
TIMESTAMP bank_operation_at
TIMESTAMP matched_at
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_reconciliation_batches ||--o{ store_reconciliation_entries : "reconciliation_batch_id"
  style store_reconciliation_batches fill:#BBF7D0,stroke:#15803D,color:#111827
  style store_reconciliation_entries fill:#BBF7D0,stroke:#15803D,color:#111827
```

Vista funcional. Consultar el diagrama general para relaciones externas al módulo.
