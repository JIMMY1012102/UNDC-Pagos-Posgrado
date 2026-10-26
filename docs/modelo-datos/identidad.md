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
  store_customer_profiles ||--o{ store_beneficiaries : "customer_profile_id"
  style store_customer_profiles fill:#DDD6FE,stroke:#7C3AED,color:#111827
  style store_oauth_identities fill:#DDD6FE,stroke:#7C3AED,color:#111827
  style store_beneficiaries fill:#DDD6FE,stroke:#7C3AED,color:#111827
```

Vista funcional. Consultar el diagrama general para relaciones externas al módulo.
