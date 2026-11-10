# Siete módulos de UNDC Market

Los módulos **reales** están en `app/Modules/`, con nombres alineados al [mapa de 44 tablas](./modelo-datos/00_MAPA_GENERAL_43_TABLAS.md).

| Módulo | Tablas | Estado |
|---|---:|---|
| Sispagos | 6 | Módulo de integración, seguridad o funcionalidad aún pendiente |
| Catalogo | 4 | Backend parcial con pruebas |
| Usuarios | 4 | Backend parcial con pruebas |
| Pedidos | 5 | Backend parcial con pruebas |
| Pagos | 6 | Módulo de integración, seguridad o funcionalidad aún pendiente |
| Tesoreria | 6 | Módulo de integración, seguridad o funcionalidad aún pendiente |
| Seguridad | 13 | Módulo de integración, seguridad o funcionalidad aún pendiente |

**KISS:** un módulo, una responsabilidad, relaciones de BD intactas y un contrato explícito cuando otro módulo necesite información. No son siete servidores ni siete bases de datos. Mantener SISPAGOS solo lectura y los pagos reales deshabilitados hasta contar con permisos y pruebas.
