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
| Asistente | 0 | Endpoint Laravel + agente FastAPI/Gemini/MCP; sin persistencia propia |

**Asistente** es un módulo transversal sin tablas ni migraciones. Laravel conserva el endpoint API y valida el mensaje; `AgentAssistantProvider` comunica con un agente FastAPI protegido por token interno. El agente usa Gemini con cadena ordenada de modelos y cliente MCP. El servidor MCP expone herramientas de lectura del catálogo a través de la API pública existente de Catalogo; no accede directamente a `tm_*`, no crea pedidos y no procesa pagos. El código Python y PHP permanece agrupado en `app/Modules/Asistente/`, aunque Python se ejecuta como proceso independiente. Las dependencias Python se aíslan en `Agent/requirements.txt`; no se agregan dependencias Composer. Mientras no se configure `ASSISTANT_AGENT_TOKEN`, se mantiene el adaptador HTTP Gemini anterior para compatibilidad. La ruta Laravel continúa bajo middleware API, no CSRF web.

**KISS:** un módulo, una responsabilidad, relaciones de BD intactas y un contrato explícito cuando otro módulo necesite información. No son ocho servidores ni ocho bases de datos. Mantener SISPAGOS solo lectura y los pagos reales deshabilitados hasta contar con permisos y pruebas.
