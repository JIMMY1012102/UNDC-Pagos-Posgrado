# 3. Compradores

Cuentas web, perfiles, identidades OAuth y beneficiarios. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  users {
    BIGINT id PK
  }
  store_customer_profiles {
    BIGINT id PK
  }
  store_oauth_identities {
    BIGINT id PK
  }
  store_beneficiaries {
    BIGINT id PK
  }
  users ||--o| store_customer_profiles : "user_id"
  users ||--o{ store_oauth_identities : "user_id"
  store_customer_profiles ||--o{ store_beneficiaries : "customer_profile_id"
  users ||--o{ store_carts : "user_id nullable"
  store_beneficiaries ||--o{ store_cart_items : "beneficiary_id nullable"
  users ||--o{ store_orders : "user_id"
  store_beneficiaries ||--o{ store_order_items : "beneficiary_id nullable"
  users ||--o{ store_order_status_history : "actor_user_id nullable"
  users ||--o{ store_payment_incidents : "resolved_by nullable"
  users ||--o{ store_refunds : "requested_by nullable"
  users ||--o{ store_audit_events : "actor_buyer_user_id nullable"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
