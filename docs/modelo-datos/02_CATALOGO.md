# 2. Catálogo

Oficinas publicadas, conceptos y versiones de tarifa. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  tienda_configuracion_oficinas {
    BIGINT id PK
  }
  tienda_conceptos_catalogo {
    BIGINT id PK
  }
  tienda_versiones_tarifas {
    BIGINT id PK
  }
  tm_oficina ||--o| tienda_configuracion_oficinas : "legacy_office_id"
  tm_oficina ||--o{ tienda_conceptos_catalogo : "legacy_office_id"
  tm_conceptos ||--o{ tienda_conceptos_catalogo : "legacy_concept_id"
  tienda_conceptos_catalogo ||--o{ tienda_versiones_tarifas : "catalog_item_id"
  tienda_conceptos_catalogo ||--o{ tienda_detalles_carrito : "catalog_item_id"
  tienda_conceptos_catalogo ||--o{ tienda_detalles_pedido : "catalog_item_id"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
