# Roles y permisos de la Tienda UNDC (Spatie v8)

## Alcance y dependencia

Esta implementación aplica únicamente a las cuentas del modelo Laravel App\Models\User.
No reemplaza las tablas históricas SISPAGOS tm_usuario/tm_rol.
No asocia automáticamente funcionarios de Tesorería con compradores web.

Branch: feat/spatie-roles-permisos-v1 (depende de feat/backend-catalogo-v1).
No hay rutas públicas de administración, cuentas privilegiadas asignadas ni despliegues.

## Instalación y estructura

Dependencia resuelta por Composer en composer.json y composer.lock:
spatie/laravel-permission:^8.3

El esquema oficial publicado por Spatie crea cinco tablas (roles, permissions,
model_has_roles, model_has_permissions, role_has_permissions). Son distintas de
los maestros tm_* de SISPAGOS. Usamos guard web y el modelo User con HasRoles.

Configuración oficial: config/permission.php; teams=false, sin bypass global
automático; alias role, permission y role_or_permission en bootstrap/app.php.

## Matriz provisional (NO oficial)

Rol | Permisos iniciales
--- | ---
comprador | pedidos.crear, pedidos.ver-propios
administrador-oficina | catalogo.gestionar, pedidos.ver-oficina
tesoreria-consulta | pagos.ver-tesoreria
superadministrador-tienda | catalogo.gestionar, pedidos.ver-oficina, roles.gestionar

Estos permisos son una propuesta de desarrollo. No se asigna automáticamente
ninguno a cuentas reales y no ejecutan ninguna acción hasta crear rutas,
políticas y casos de uso aprobados.

Importante:
- administrador-oficina requiere una política de pertenencia a oficina,
  además del permiso global de Spatie, antes de consultar pedidos de una oficina.
- tesoreria-consulta NO habilita acceso a SISPAGOS ni concede validación de cobros.
- superadministrador-tienda NO puede confirmar pagos ni saltarse validaciones bancarias.
- El comprador puede ser externo a la UNDC; autenticación Google e identidad
  documental son controles independientes aún pendientes.
- Nunca asignar roles privilegiados por datos recibidos del navegador ni por email.

## Preparación SOLO en entorno de desarrollo autorizado

1. Configurar una base de datos local/sintética; NO usar Oracle ni base institucional.
2. Ejecutar composer install.
3. Revisar el esquema y ejecutar php artisan migrate en la base de desarrollo.
4. Ejecutar php artisan db:seed --class=StoreRolesPermissionsSeeder.
5. Ejecutar php artisan test --filter=StoreRolesPermissionsTest.
6. Usar middleware auth + permission al crear futuras rutas protegidas.

No se crea ningún usuario superadministrador ni se asignan roles de manera
automática. La autorización de administradores reales requerirá una operación
segura, autenticada, auditada y aprobada por la OTI/Tesorería.

El backend sigue el principio KISS: HasRoles, middleware y Gates de Laravel;
sin tabla paralela de roles ni servicio RBAC construido a mano.

## Pendientes

- Validar con Alfonso/OTI/Tesorería nombres y alcances de roles.
- Implementar políticas por oficina y propiedad de pedidos.
- Acordar identidad y roles del personal SISPAGOS antes de exponer consulta.
- Revisar el PR dependiente y CI antes de incorporar en main.
