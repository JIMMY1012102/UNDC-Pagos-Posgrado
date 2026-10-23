# Instrucciones a Codex — UNDC-Pagos-Posgrado

## Principios
Trabajar en laptop, mediante Git y Pull Requests. No configurar Codespaces, despliegue automático ni servicios de Oracle por iniciativa propia. Seguir KISS: cambios pequeños y probados. Revisar `docs/ARQUITECTURA.md` y `docs/MODULOS.md` antes de generar código.

**No instalar paquetes, ejecutar migraciones ni generar credenciales automáticamente.** Comprobar primero el entorno y solicitar autorización si se necesita modificar dependencias o esquema.

## Límites
- `app/Modules/Acceso`: cuenta y perfil; inicio de sesión no acredita identidad académica.
- `app/Modules/Tienda`: catálogo, carrito, validación de precios en servidor y pedidos pendientes.
- `app/Modules/Pagos`: intentos, verificación servidor-servidor, idempotencia, manejo de estados inciertos y trazabilidad.
- `app/Modules/Tesoreria`: consultas internas solo con autenticación/autorización y datos permitidos.
- `app/Integrations/`: adaptadores a terceros, independientes de la lógica del dominio.

Usar `App\\Modules\\...` (PSR-4 ya configurado). Poner las rutas de cada módulo en su `routes.php`; registrar solo operaciones realmente implementadas y probadas. Mantener la portada actual mientras no exista catálogo.

## Prohibiciones y cautelas
- No usar datos personales reales ni pagos reales en la demo HTTP.
- No dar por contratada ni implementada la pasarela Pay-me.
- No suponer disponibles las API bancarias de Interbank.
- No alterar las tablas históricas de SISPAGOS ni migrar contra bases institucionales sin acuerdo.
- No acreditar pagos por la redirección del navegador.
- No versionar `.env`, secretos, llaves SSH ni dumps de producción.

## Antes de merge
Ejecutar `php artisan test`; cuando corresponda `composer validate --strict` y `npm run build`. Incluir pruebas nuevas por módulo. Documentar decisiones pendientes y nunca afirmar que una pantalla equivale a un flujo funcional.
