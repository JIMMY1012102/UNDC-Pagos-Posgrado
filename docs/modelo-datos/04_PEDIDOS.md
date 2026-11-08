# 4. Pedidos

Carrito, líneas, órdenes y sus estados. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  store_carts {
    BIGINT id PK
  }
  store_cart_items {
    BIGINT id PK
  }
  store_orders {
    BIGINT id PK
  }
  store_order_items {
    BIGINT id PK
  }
  store_order_status_history {
    BIGINT id PK
  }
  users ||--o{ store_carts : "user_id nullable"
  store_carts ||--o{ store_cart_items : "cart_id"
  store_catalog_items ||--o{ store_cart_items : "catalog_item_id"
  store_beneficiaries ||--o{ store_cart_items : "beneficiary_id nullable"
  users ||--o{ store_orders : "user_id"
  store_carts ||--o{ store_orders : "cart_id nullable"
  store_orders ||--o{ store_order_items : "order_id"
  store_catalog_items ||--o{ store_order_items : "catalog_item_id"
  store_beneficiaries ||--o{ store_order_items : "beneficiary_id nullable"
  tm_oficina ||--o{ store_order_items : "legacy_office_id"
  tm_conceptos ||--o{ store_order_items : "legacy_concept_id"
  store_orders ||--o{ store_order_status_history : "order_id"
  users ||--o{ store_order_status_history : "actor_user_id nullable"
  store_orders ||--o{ store_payment_attempts : "order_id"
  store_orders ||--o{ store_payment_transactions : "order_id"
  store_orders ||--o| store_order_payment_claims : "order_id"
  store_orders ||--o{ store_payment_incidents : "order_id"
  store_orders ||--o{ store_external_system_links : "order_id nullable"
  store_orders ||--o{ store_payment_validations : "order_id"
  store_orders ||--o{ store_validation_certificates : "order_id"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
