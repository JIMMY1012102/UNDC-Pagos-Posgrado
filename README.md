# UNDC Market — Pagos de Posgrado

Tienda virtual de pagos para Posgrado de la Universidad Nacional de Cañete (UNDC), vinculada al trabajo de Tesorería/SISPAGOS.

**Estado en `main`:** monolito modular con siete módulos de backend en una aplicación Laravel y un esquema MariaDB compartido. Hay implementación parcial de catálogo, cuentas y perfiles, carrito y precompra. La creación de pedidos, los cobros reales y el flujo completo de validación de Tesorería continúan pendientes. La fusión en GitHub no acredita despliegue en Oracle ni aprobación institucional.

## Siete módulos de backend

| Módulo | Carpeta | Responsabilidad y avance |
|---|---|---|
| Sispagos | `app/Modules/Sispagos` | Adaptador de lectura de conceptos y oficinas históricos para el catálogo; integración institucional completa pendiente. |
| Catalogo | `app/Modules/Catalogo` | Consulta de publicaciones del catálogo y contrato de disponibilidad; backend parcial con pruebas. |
| Compradores | `app/Modules/Compradores` | Registro e inicio de sesión, perfiles e identidades Google OAuth; backend parcial con pruebas. |
| Pedidos | `app/Modules/Pedidos` | Carrito y resumen de precompra; creación de pedidos pendiente. |
| Pagos | `app/Modules/Pagos` | Estructura de datos para intentos, transacciones e incidencias; cobro real pendiente. |
| Tesoreria | `app/Modules/Tesoreria` | Estructura de datos para conciliación, validación, constancias y auditoría; flujo institucional completo pendiente. |
| Seguridad | `app/Modules/Seguridad` | Agrupación de responsabilidades de permisos Spatie y soporte Laravel; sin endpoints administrativos implementados en el módulo. |

Las siete carpetas se registran desde [`routes/web.php`](routes/web.php). El reparto de tablas y las relaciones están documentados en el [mapa del modelo de datos](docs/modelo-datos/00_MAPA_GENERAL_43_TABLAS.md).

Los nombres `acceso` y `tienda` que permanecen en `resources/views/modules/` corresponden a vistas conservadas por compatibilidad; no son módulos adicionales de backend.

Los directorios `app/Integrations/Sispagos`, `app/Integrations/Payme` y `app/Integrations/Interbank` contienen documentación de integración. Su existencia no significa que haya una API institucional autorizada, una pasarela contratada o un canal de cobro operativo.

## Recorrido implementado y límites

- Consulta de catálogo mediante API; creación, consulta y modificación del carrito.
- Registro e inicio de sesión, perfil del comprador y rutas de Google OAuth sujetas a configuración autorizada.
- Vinculación del carrito a una cuenta y consulta del resumen de precompra.
- La precompra mantiene `checkout_enabled=false`, `total_amount=null` y las tarifas pendientes de aprobación institucional. Una pantalla de pago no confirma un cobro.

## Tecnologías

El manifiesto [`composer.json`](composer.json) declara PHP `^8.3`, Laravel `^13.17`, Laravel Socialite `^5.0` y Spatie Laravel Permission `^8.0`. La presentación utiliza Blade, CSS y JavaScript; [`package.json`](package.json) declara Vite y Tailwind CSS. Livewire no figura como dependencia en el manifiesto revisado.

Estas declaraciones describen las dependencias del proyecto; no certifican las versiones instaladas en el servidor.

## Documentación

- [Arquitectura](docs/ARQUITECTURA.md)
- [Siete módulos y estado](docs/MODULOS.md)
- [Modelo de datos y diagramas por módulo](docs/modelo-datos/README.md)
- [Diseño de base de datos](docs/BASE_DE_DATOS.md)
- [API del catálogo](docs/API_CATALOGO.md)
- [API del carrito](docs/API_CARRITO.md)
- [Bitácora de avances](docs/BITACORA_AVANCES_UNDC_MARKET.md)
- [Desarrollo local con Codex](docs/DESARROLLO_LOCAL.md)
- [Actualización manual de Oracle](docs/DESPLIEGUE.md)
- [Instrucciones a Codex](AGENTS.md)

## Flujo de trabajo y validación

**Rama Git → Pull Request → pruebas → merge a `main` → actualización manual de Oracle cuando se autorice.** GitHub Actions comprueba Laravel, integración con MariaDB y navegación con Chromium, además de validar Composer y compilar el frontend; no despliega en Oracle. Los resultados de pruebas no sustituyen la aceptación del área usuaria.

Mantener los entornos de prueba con datos ficticios, las tablas históricas de SISPAGOS sin alteraciones y los pagos reales deshabilitados hasta contar con autorizaciones y validaciones institucionales. No versionar credenciales, llaves ni datos personales reales.
