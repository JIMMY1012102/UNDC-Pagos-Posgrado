# 2. Catálogo

Oficinas publicadas, conceptos y versiones de tarifa. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  store_office_settings {
    BIGINT id PK
  }
  store_catalog_items {
    BIGINT id PK
  }
  store_catalog_price_versions {
    BIGINT id PK
  }
  tm_oficina ||--o| store_office_settings : "legacy_office_id"
  tm_oficina ||--o{ store_catalog_items : "legacy_office_id"
  tm_conceptos ||--o{ store_catalog_items : "legacy_concept_id"
  store_catalog_items ||--o{ store_catalog_price_versions : "catalog_item_id"
  store_catalog_items ||--o{ store_cart_items : "catalog_item_id"
  store_catalog_items ||--o{ store_order_items : "catalog_item_id"
``

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
