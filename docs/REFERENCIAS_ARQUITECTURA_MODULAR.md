# Referencias verificadas — monolito modular y DDD ligero

**Fecha:** 08/11/2026. Evaluación de patrones para UNDC Market, no reutilización automática de código ajeno.

## 1. Bagisto — Laravel e-commerce
- Fuente: https://github.com/bagisto/bagisto/tree/master/packages/Webkul
- La carpeta `Checkout/src` incluye contratos, modelos, repositorios, listeners, controladores y proveedores Laravel.
- `Payment/src` incluye abstracción de pagos y proveedores independientes.
- **Aplicación a UNDC Market:** usar servicios y contratos pequeños entre `Catalogo`, `Pedidos` y `Pagos`; mantener rutas y DI de Laravel. **No** importar el sistema completo de checkout ni módulos comerciales no necesarios.

## 2. Medusa — módulos de comercio
- Fuente: https://github.com/medusajs/medusa/tree/develop/packages/modules
- Módulos explícitos: `cart`, `customer`, `order`, `payment`, `pricing`, `product`.
- `payment/src` separa modelos, migraciones, proveedores y servicios.
- **Aplicación:** separar capacidad comercial: catálogo, identidad, pedido y pago. Los módulos colaboran por interfaces; la acreditación se valida en servidor, nunca por JSON del navegador. Medusa usa otra tecnología: **no copiar código TypeScript**.

## 3. Modular Monolith with DDD — referencia de límites
- Fuente: https://github.com/kgrzybek/modular-monolith-with-ddd/tree/master/src/Modules
- Contiene `Payments`, `Registrations`, `UserAccess`, entre otros.
- La carpeta `Payments` separa `Application`, `Domain`, `Infrastructure`, `IntegrationEvents` y `Tests`.
- **Aplicación:** límites de datos, pruebas por módulo, eventos de integración solo cuando existan procesos reales. **No** incorporar una jerarquía pesada de capas en cada módulo sin necesidad.

## Decisión de diseño para UNDC Market

**Monolito modular con DDD ligero: una aplicación Laravel, una MariaDB, siete módulos.**
`Sispagos` (6 tablas heredadas, sin alteraciones), `Catalogo` (3), `Usuarios` (4), `Pedidos` (5), `Pagos` (6), `Tesoreria` (6), `Seguridad` (13). Total **43 tablas**.

- `Pedidos` solicita disponibilidad por `Catalogo\\Contracts\\CatalogItemLookup`, implementado por `CatalogItemLookupService`.
- `Catalogo` obtiene conceptos históricos desde el adaptador de lectura `Sispagos\\Queries\\PublishedCatalogQuery`.
- SISPAGOS es **legado externo al nuevo dominio**, con seis tablas `tm_*` preservadas; **ninguna migración nueva altera estas tablas**.
- No se reparte la misma MariaDB en siete bases ni se comunica SQL con JSON entre tablas.
- No se declaran cobros reales hasta contar con pasarela autorizada y confirmación verificable.

## Comprobaciones de integración

- Mantener las 43 tablas, las FK existentes, las rutas y los nombres públicos.
- Probar `Catalogo -> Sispagos` con lecturas y `Pedidos -> Catalogo` por contrato.
- Ejecutar Laravel PHPUnit, un MariaDB temporal con esquemas ficticios de SISPAGOS y una prueba Chromium de las **pantallas actuales**.
- Las pruebas deben usar datos **ficticios**, nunca la instancia `tesoreria_dev` ni acceso a Oracle.
- No hacer merge o despliegue hasta CI verde y validación OTI.

**KISS:** inspirarse en responsabilidades claras y contratos, no copiar patrones de más.
