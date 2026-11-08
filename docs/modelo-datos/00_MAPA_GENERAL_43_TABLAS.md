# Diagrama general por siete módulos — 43 tablas

**Monolito modular + DDD ligero.** Un único esquema MariaDB y siete propietarios lógicos. Cada bloque representa un límite de responsabilidad; los colores no representan bases independientes. Las flechas representan claves foráneas documentadas, no mensajes JSON.

```mermaid
flowchart LR
  subgraph Sispagos["SISPAGOS (legado) · 6"]
    tm_oficina["tm_oficina"]
    tm_rol["tm_rol"]
    tm_usuario["tm_usuario"]
    tm_conceptos["tm_conceptos"]
    tm_recaudacion["tm_recaudacion"]
    tm_resultado["tm_resultado"]
  end
  style Sispagos fill:#E1E6ED,stroke:#64748b,stroke-width:2px,color:#111827
  subgraph Catalogo["Catálogo · 3"]
    store_office_settings["store_office_settings"]
    store_catalog_items["store_catalog_items"]
    store_catalog_price_versions["store_catalog_price_versions"]
  end
  style Catalogo fill:#DDF4E5,stroke:#64748b,stroke-width:2px,color:#111827
  subgraph Compradores["Compradores · 4"]
    users["users"]
    store_customer_profiles["store_customer_profiles"]
    store_oauth_identities["store_oauth_identities"]
    store_beneficiaries["store_beneficiaries"]
  end
  style Compradores fill:#ECE2F9,stroke:#64748b,stroke-width:2px,color:#111827
  subgraph Pedidos["Pedidos · 5"]
    store_carts["store_carts"]
    store_cart_items["store_cart_items"]
    store_orders["store_orders"]
    store_order_items["store_order_items"]
    store_order_status_history["store_order_status_history"]
  end
  style Pedidos fill:#FDEACC,stroke:#64748b,stroke-width:2px,color:#111827
  subgraph Pagos["Pagos · 6"]
    store_payment_attempts["store_payment_attempts"]
    store_payment_transactions["store_payment_transactions"]
    store_order_payment_claims["store_order_payment_claims"]
    store_payment_webhook_events["store_payment_webhook_events"]
    store_payment_incidents["store_payment_incidents"]
    store_refunds["store_refunds"]
  end
  style Pagos fill:#F9E0E0,stroke:#64748b,stroke-width:2px,color:#111827
  subgraph Tesoreria["Conciliación y Tesorería · 6"]
    store_reconciliation_batches["store_reconciliation_batches"]
    store_reconciliation_entries["store_reconciliation_entries"]
    store_payment_validations["store_payment_validations"]
    store_validation_certificates["store_validation_certificates"]
    store_external_system_links["store_external_system_links"]
    store_audit_events["store_audit_events"]
  end
  style Tesoreria fill:#DDEFFC,stroke:#64748b,stroke-width:2px,color:#111827
  subgraph Seguridad["Seguridad y Laravel · 13"]
    password_reset_tokens["password_reset_tokens"]
    sessions["sessions"]
    cache["cache"]
    cache_locks["cache_locks"]
    jobs["jobs"]
    job_batches["job_batches"]
    failed_jobs["failed_jobs"]
    migrations["migrations"]
    roles["roles"]
    permissions["permissions"]
    model_has_roles["model_has_roles"]
    model_has_permissions["model_has_permissions"]
    role_has_permissions["role_has_permissions"]
  end
  style Seguridad fill:#EAEAEF,stroke:#64748b,stroke-width:2px,color:#111827
  users --> store_customer_profiles
  users --> store_oauth_identities
  store_customer_profiles --> store_beneficiaries
  tm_oficina --> store_office_settings
  tm_oficina --> store_catalog_items
  tm_conceptos --> store_catalog_items
  store_catalog_items --> store_catalog_price_versions
  users --> store_carts
  store_carts --> store_cart_items
  store_catalog_items --> store_cart_items
  store_beneficiaries --> store_cart_items
  users --> store_orders
  store_carts --> store_orders
  store_orders --> store_order_items
  store_catalog_items --> store_order_items
  store_beneficiaries --> store_order_items
  tm_oficina --> store_order_items
  tm_conceptos --> store_order_items
  store_orders --> store_order_status_history
  users --> store_order_status_history
  store_orders --> store_payment_attempts
  store_orders --> store_payment_transactions
  store_payment_attempts --> store_payment_transactions
  store_orders --> store_order_payment_claims
  store_payment_transactions --> store_order_payment_claims
  store_payment_attempts --> store_payment_webhook_events
  store_orders --> store_payment_incidents
  store_payment_transactions --> store_payment_incidents
  users --> store_payment_incidents
  store_payment_transactions --> store_refunds
  users --> store_refunds
  tm_usuario --> store_reconciliation_batches
  store_reconciliation_batches --> store_reconciliation_entries
  store_payment_transactions --> store_reconciliation_entries
  tm_resultado --> store_reconciliation_entries
  tm_recaudacion --> store_reconciliation_entries
  store_orders --> store_external_system_links
  store_payment_transactions --> store_external_system_links
  store_orders --> store_payment_validations
  store_payment_transactions --> store_payment_validations
  tm_usuario --> store_payment_validations
  store_payment_validations --> store_validation_certificates
  store_orders --> store_validation_certificates
  tm_usuario --> store_validation_certificates
  tm_usuario --> store_validation_certificates
  users --> store_audit_events
  tm_usuario --> store_audit_events
  roles --> model_has_roles
  permissions --> model_has_permissions
  roles --> role_has_permissions
  permissions --> role_has_permissions
``

> **Lectura:** las relaciones se basan en migraciones y en la documentación existente. `tm_rol` y otras columnas históricas no llevan FK inventadas. Las referencias polimórficas de Spatie no se dibujan como FK SQL a `users`. GitHub genera el diseño gráfico automáticamente y puede cruzar algunas líneas en una vista de 43 entidades.

| Módulo | Tablas | Responsabilidad |
|---|---:|---|
| SISPAGOS (legado) | 6 | Seis maestros históricos de SISPAGOS (sin cambios) |
| Catálogo | 3 | Oficinas publicadas, conceptos y versiones de tarifa |
| Compradores | 4 | Cuentas web, perfiles, identidades OAuth y beneficiarios |
| Pedidos | 5 | Carrito, líneas, órdenes y sus estados |
| Pagos | 6 | Intentos, transacciones, cobro aplicado, webhooks e incidencias |
| Conciliación y Tesorería | 6 | Conciliación, validación, certificados y auditoría |
| Seguridad y Laravel | 13 | Roles y permisos Spatie, sesiones y soporte de Laravel |

[Diagramas por módulo](./README.md) · [Arquitectura del backend](../ARQUITECTURA.md)
