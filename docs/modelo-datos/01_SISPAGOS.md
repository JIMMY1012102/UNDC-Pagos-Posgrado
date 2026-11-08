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
  tm_oficina ||--o| store_office_settings : "legacy_office_id"
  tm_oficina ||--o{ store_catalog_items : "legacy_office_id"
  tm_conceptos ||--o{ store_catalog_items : "legacy_concept_id"
  tm_oficina ||--o{ store_order_items : "legacy_office_id"
  tm_conceptos ||--o{ store_order_items : "legacy_concept_id"
  tm_usuario ||--o{ store_reconciliation_batches : "legacy_importer_id nullable"
  tm_resultado ||--o{ store_reconciliation_entries : "legacy_result_id nullable"
  tm_recaudacion ||--o{ store_reconciliation_entries : "legacy_recaudacion_id nullable"
  tm_usuario ||--o{ store_payment_validations : "legacy_reviewer_id nullable"
  tm_usuario ||--o{ store_validation_certificates : "legacy_issuer_id nullable"
  tm_usuario ||--o{ store_validation_certificates : "legacy_revoker_id nullable"
  tm_usuario ||--o{ store_audit_events : "legacy_staff_id nullable"
``

Las tablas externas enlazadas aparecen solo como referencias para interpretar claves foráneas; la propiedad permanece en su módulo correspondiente. Los campos completos deben revisarse en las migraciones y en MariaDB; este ER resume las PK y relaciones, no todos los atributos. 

[Volver al mapa general](./00_MAPA_GENERAL_43_TABLAS.md)
