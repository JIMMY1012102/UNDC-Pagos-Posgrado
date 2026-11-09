# 1. SISPAGOS (legado)

Seis maestros históricos de SISPAGOS (sin cambios). Tablas del módulo en el esquema MariaDB compartido.

```mermaid
erDiagram
  tm_oficina {
    INT oficina_id PK
  }
  tm_rol {
    INT rol_id PK
  }
  tm_usuario {
    INT usu_id PK
  }
  tm_conceptos {
    INT id_concepto PK
  }
  tm_recaudacion {
    INT id_recaudacion PK
  }
  tm_resultado {
    INT i_resultado PK
  }
  tm_oficina ||--o| tienda_configuracion_oficinas : "legacy_office_id"
  tm_oficina ||--o{ tienda_conceptos_catalogo : "legacy_office_id"
  tm_conceptos ||--o{ tienda_conceptos_catalogo : "legacy_concept_id"
  tm_oficina ||--o{ tienda_detalles_pedido : "legacy_office_id"
  tm_conceptos ||--o{ tienda_detalles_pedido : "legacy_concept_id"
  tm_usuario ||--o{ tienda_lotes_conciliacion : "legacy_importer_id nullable"
  tm_resultado ||--o{ tienda_detalles_conciliacion : "legacy_result_id nullable"
  tm_recaudacion ||--o{ tienda_detalles_conciliacion : "legacy_recaudacion_id nullable"
  tm_usuario ||--o{ tienda_validaciones_pago : "legacy_reviewer_id nullable"
  tm_usuario ||--o{ tienda_constancias_validacion : "legacy_issuer_id nullable"
  tm_usuario ||--o{ tienda_constancias_validacion : "legacy_revoker_id nullable"
  tm_usuario ||--o{ tienda_eventos_auditoria : "legacy_staff_id nullable"
```

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
