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
  users ||--o{ tienda_carritos : "usuario_id nullable"
  tienda_carritos ||--o{ tienda_detalles_carrito : "carrito_id"
  tienda_conceptos_catalogo ||--o{ tienda_detalles_carrito : "concepto_catalogo_id"
  tienda_beneficiarios ||--o{ tienda_detalles_carrito : "beneficiario_id nullable"
  users ||--o{ tienda_pedidos : "usuario_id"
  tienda_carritos ||--o{ tienda_pedidos : "carrito_id nullable"
  tienda_pedidos ||--o{ tienda_detalles_pedido : "pedido_id"
  tienda_conceptos_catalogo ||--o{ tienda_detalles_pedido : "concepto_catalogo_id"
  tienda_beneficiarios ||--o{ tienda_detalles_pedido : "beneficiario_id nullable"
  tm_oficina ||--o{ tienda_detalles_pedido : "legacy_office_id"
  tm_conceptos ||--o{ tienda_detalles_pedido : "legacy_concept_id"
  tienda_pedidos ||--o{ tienda_historial_estados_pedido : "pedido_id"
  users ||--o{ tienda_historial_estados_pedido : "actor_usuario_id nullable"
  tienda_pedidos ||--o{ tienda_intentos_pago : "pedido_id"
  tienda_pedidos ||--o{ tienda_transacciones_pago : "pedido_id"
  tienda_pedidos ||--o| tienda_pagos_aplicados_pedidos : "pedido_id"
  tienda_pedidos ||--o{ tienda_incidencias_pago : "pedido_id"
  tienda_pedidos ||--o{ tienda_vinculos_sistemas_externos : "pedido_id nullable"
  tienda_pedidos ||--o{ tienda_validaciones_pago : "pedido_id"
  tienda_pedidos ||--o{ tienda_constancias_validacion : "pedido_id"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
