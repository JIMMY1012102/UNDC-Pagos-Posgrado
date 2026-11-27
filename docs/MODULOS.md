# Ocho módulos de UNDC Market

Los módulos **reales** están en `app/Modules/`, con nombres alineados al [mapa de 44 tablas](./modelo-datos/00_MAPA_GENERAL_43_TABLAS.md). La aplicación mantiene una sola base de datos; no todos los módulos necesitan tablas propias.

| Módulo | Tablas | Estado |
|---|---:|---|
| Sispagos | 6 | Adaptador de solo lectura para los maestros históricos |
| Catalogo | 4 | Backend parcial con pruebas |
| Usuarios | 4 | Backend parcial con pruebas |
| Pedidos | 5 | Backend parcial con pruebas |
| Pagos | 6 | Estructura de integración; no hay cobro real Pay-me implementado |
| Tesoreria | 6 | Estructura para procesos internos, pendiente de validación institucional |
| Seguridad | 13 | Roles, permisos, sesiones y soporte de Laravel |
| Asistente | 0 | Endpoint API y proveedor Gemini con respaldo configurable; sin persistencia propia |

**Asistente** es un módulo transversal sin tablas ni migraciones. `AssistantController` valida la entrada, `AssistantService` coordina el caso de uso y `AssistantProvider` desacopla el proveedor externo. La implementación usa Gemini mediante HTTP con modelo principal y modelo alternativo opcional configurados por entorno; las pruebas simulan HTTP y nunca llaman al proveedor real. La ruta se registra bajo middleware API, no bajo CSRF web.

**KISS:** un módulo, una responsabilidad, relaciones de BD intactas y un contrato explícito cuando otro módulo necesite información. No son ocho servidores ni ocho bases de datos. Mantener SISPAGOS solo lectura y los pagos reales deshabilitados hasta contar con permisos y pruebas.
