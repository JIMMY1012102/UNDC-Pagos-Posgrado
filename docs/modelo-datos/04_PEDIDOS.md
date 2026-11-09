# 4. Pedidos

Carrito, líneas, órdenes y sus estados. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  tienda_carritos {
    BIGINT id PK
  }
  tienda_detalles_carrito {
    BIGINT id PK
  }
  tienda_pedidos {
    BIGINT id PK
  }
  tienda_detalles_pedido {
    BIGINT id PK
  }
  tienda_historial_estados_pedido {
    BIGINT id PK
  }
  users ||--o{ tienda_carritos : "user_id nullable"
  tienda_carritos ||--o{ tienda_detalles_carrito : "cart_id"
  tienda_conceptos_catalogo ||--o{ tienda_detalles_carrito : "catalog_item_id"
  tienda_beneficiarios ||--o{ tienda_detalles_carrito : "beneficiary_id nullable"
  users ||--o{ tienda_pedidos : "user_id"
  tienda_carritos ||--o{ tienda_pedidos : "cart_id nullable"
  tienda_pedidos ||--o{ tienda_detalles_pedido : "order_id"
  tienda_conceptos_catalogo ||--o{ tienda_detalles_pedido : "catalog_item_id"
  tienda_beneficiarios ||--o{ tienda_detalles_pedido : "beneficiary_id nullable"
  tm_oficina ||--o{ tienda_detalles_pedido : "legacy_office_id"
  tm_conceptos ||--o{ tienda_detalles_pedido : "legacy_concept_id"
  tienda_pedidos ||--o{ tienda_historial_estados_pedido : "order_id"
  users ||--o{ tienda_historial_estados_pedido : "actor_user_id nullable"
  tienda_pedidos ||--o{ tienda_intentos_pago : "order_id"
  tienda_pedidos ||--o{ tienda_transacciones_pago : "order_id"
  tienda_pedidos ||--o| tienda_pagos_aplicados_pedidos : "order_id"
  tienda_pedidos ||--o{ tienda_incidencias_pago : "order_id"
  tienda_pedidos ||--o{ tienda_vinculos_sistemas_externos : "order_id nullable"
  tienda_pedidos ||--o{ tienda_validaciones_pago : "order_id"
  tienda_pedidos ||--o{ tienda_constancias_validacion : "order_id"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
