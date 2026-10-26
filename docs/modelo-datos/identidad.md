# Diagrama editable — identidad

```mermaid
erDiagram
  store_customer_profiles {
BIGINT id PK
BIGINT user_id FK
VARCHAR first_names
VARCHAR last_names
VARCHAR document_type
VARCHAR document_number
VARCHAR phone
VARCHAR contact_email
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_oauth_identities {
BIGINT id PK
BIGINT user_id FK
VARCHAR provider
VARCHAR provider_subject
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  store_beneficiaries {
BIGINT id PK
BIGINT customer_profile_id FK
VARCHAR first_names
VARCHAR last_names
VARCHAR document_type
VARCHAR document_number
VARCHAR email
VARCHAR phone
TIMESTAMP created_at
TIMESTAMP updated_at
  }
  users ||--o| store_customer_profiles : "user_id"
  users ||--o{ store_oauth_identities : "user_id"
  store_customer_profiles ||--o{ store_beneficiaries : "customer_profile_id"
```

Vista funcional. Consultar el diagrama general para relaciones externas al módulo.


## Convención de cardinalidades
La relación `||--o{` se representa desde el padre hacia cero o muchos hijos; `||--o|` hacia cero o un hijo (FK individual UNIQUE). Cuando la FK del hijo es nullable se señala **(opcional)**; las relaciones a entidades externas al módulo aparecen como nodos de referencia sin alterar sus estructuras. Consultar [matriz completa](./MATRIZ_CARDINALIDADES.md).

