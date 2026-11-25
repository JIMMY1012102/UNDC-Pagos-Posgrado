# 3. Usuarios

Cuentas web, perfiles, identidades OAuth y beneficiarios. Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  users {
    BIGINT id PK
  }
  tienda_perfiles_compradores {
    BIGINT id PK
  }
  tienda_identidades_oauth {
    BIGINT id PK
  }
  tienda_beneficiarios {
    BIGINT id PK
  }
  users ||--o| tienda_perfiles_compradores : "usuario_id"
  users ||--o{ tienda_identidades_oauth : "usuario_id"
  tienda_perfiles_compradores ||--o{ tienda_beneficiarios : "perfil_comprador_id"
  users ||--o{ tienda_carritos : "usuario_id nullable"
  tienda_beneficiarios ||--o{ tienda_detalles_carrito : "beneficiary_id nullable"
  users ||--o{ tienda_pedidos : "usuario_id"
  tienda_beneficiarios ||--o{ tienda_detalles_pedido : "beneficiary_id nullable"
  users ||--o{ tienda_historial_estados_pedido : "actor_usuario_id nullable"
  users ||--o{ tienda_incidencias_pago : "resolved_by nullable"
  users ||--o{ tienda_reembolsos : "requested_by nullable"
  users ||--o{ tienda_eventos_auditoria : "actor_buyer_usuario_id nullable"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
