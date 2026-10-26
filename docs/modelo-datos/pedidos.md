# Diagrama editable — pedidos

```mermaid
erDiagram
  store_carts {
BIGINT id PK
BIGINT user_id FK
CHAR token_hash
CHAR currency
VARCHAR status
TIMESTAMP expires_at
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_cart_items {
BIGINT id PK
BIGINT cart_id FK
BIGINT catalog_item_id FK
BIGINT beneficiary_id FK
INT quantity
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_orders {
BIGINT id PK
VARCHAR code
BIGINT user_id FK
BIGINT cart_id FK
VARCHAR status
CHAR currency
DECIMAL total_amount
VARCHAR buyer_first_names
VARCHAR buyer_last_names
VARCHAR buyer_document_type
VARCHAR buyer_document_number
VARCHAR buyer_email
VARCHAR buyer_phone
TIMESTAMP expires_at
TIMESTAMP confirmed_at
TIMESTAMP paid_at
TIMESTAMP cancelled_at
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_order_items {
BIGINT id PK
BIGINT order_id FK
BIGINT catalog_item_id FK
INT legacy_concept_id FK
INT legacy_office_id FK
BIGINT beneficiary_id FK
VARCHAR concept_code
VARCHAR concept_name
INT quantity
DECIMAL unit_amount
DECIMAL total_amount
VARCHAR beneficiary_first_names
VARCHAR beneficiary_last_names
VARCHAR beneficiary_document_type
VARCHAR beneficiary_document_number
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_order_status_history {
BIGINT id PK
BIGINT order_id FK
BIGINT actor_user_id FK
VARCHAR from_status
VARCHAR to_status
VARCHAR reason
JSON context
TIMESTAMP occurred_at
  }
  users ||--o{ store_carts : "user_id (opcional)"
  store_carts ||--o{ store_cart_items : "cart_id"
  store_catalog_items ||--o{ store_cart_items : "catalog_item_id"
  store_beneficiaries ||--o{ store_cart_items : "beneficiary_id (opcional)"
  users ||--o{ store_orders : "user_id"
  store_carts ||--o{ store_orders : "cart_id (opcional)"
  store_orders ||--o{ store_order_items : "order_id"
  store_catalog_items ||--o{ store_order_items : "catalog_item_id"
  store_beneficiaries ||--o{ store_order_items : "beneficiary_id (opcional)"
  tm_oficina ||--o{ store_order_items : "legacy_office_id"
  tm_conceptos ||--o{ store_order_items : "legacy_concept_id"
  store_orders ||--o{ store_order_status_history : "order_id"
  users ||--o{ store_order_status_history : "actor_user_id (opcional)"
```

Vista funcional. Consultar el diagrama general para relaciones externas al módulo.


## Convención de cardinalidades
La relación `||--o{` se representa desde el padre hacia cero o muchos hijos; `||--o|` hacia cero o un hijo (FK individual UNIQUE). Cuando la FK del hijo es nullable se señala **(opcional)**; las relaciones a entidades externas al módulo aparecen como nodos de referencia sin alterar sus estructuras. Consultar [matriz completa](./MATRIZ_CARDINALIDADES.md).

