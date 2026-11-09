# Acceso, identidad y gestión de permisos — fase 3

## Fuente y alcance

De acuerdo con el informe y los cuatro procesos del proyecto, el recorrido empieza en **Tienda** (catálogo público), pasa por **Acceso** al requerir comprador identificado, regresa a **Tienda** y posteriormente pasa a **Pagos**. Tesorería mantiene su recorrido independiente.

Alfonso planteó una tienda central extensible por oficinas y una base compartida con SISPAGOS. Se mantiene la distinción entre compradores externos (tabla `users`) y funcionarios internos (tabla histórica `tm_usuario`). **No equiparar una cuenta Google con identidad documental validada, rol de alumno o condición académica.**

## Funcionalidad incluida

- `GET /api/v1/acceso/perfil` — solo sesión autenticada; devuelve únicamente el perfil del propio comprador.
- `PUT /api/v1/acceso/perfil` — solo sesión autenticada; recibe nombres, apellidos, tipo/número de documento y datos de contacto; no admite elevar privilegios ni indicar verificación.
- Respuestas JSON con `success`, `data` y `message`; errores `422` con `errors`.
- Sin endpoints de creación de cuentas, inicio de sesión por contraseña, tokens de acceso o OAuth todavía. Los tests simulan una sesión autenticada con `actingAs`.
- Sin API de personal Tesorería; nunca leer ni editar `tm_usuario` desde estas rutas.

## Spatie: diseño de integración (aprobación y bloqueo de cambios pendientes)

Se instaló **spatie/laravel-permission ^8.0** con Composer en GitHub Actions, se registraron sus dependencias reales en `composer.lock` y se publicó la configuración y migración oficiales del proveedor. `App\\Models\\User` ya usa `HasRoles`; el guard es `web`. No se han sembrado permisos ni roles institucionales ni ejecutado migraciones en producción. La instalación debe validarse mediante CI antes de merge.

`Spatie` administrará las relaciones RBAC de Laravel en tablas propias de la aplicación. **No alterar ni reemplazar** la tabla `tm_rol`, ni asumir que los roles históricos de SISPAGOS coinciden con roles del nuevo sistema. No se asignan roles administrativos ni permisos de Tesorería por defecto, ni se crean usuarios privilegiados.

Contratos de acceso a concretar con Alfonso/OTI: roles autorizados, alcance por oficina, quién los asigna, permiso de consultar/conciliar, aprobación/revocación, guard `web`, auditoría y segregación de funciones. La autorización deberá realizarse del lado servidor con Gate/Policy y middleware tras instalar Spatie, nunca depender de controles del frontend.

## OAuth Google

La implementación Google se ha preparado con **Laravel Socialite** instalado mediante Composer. Las rutas `/auth/google/redirect` y `/auth/google/callback` usan estado OAuth y sesión web; **están deshabilitadas por defecto** (`GOOGLE_LOGIN_ENABLED=false`) y exigen HTTPS más `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` y `GOOGLE_REDIRECT_URI` institucionales. El callback no vincula automáticamente identidades distintas que compartan correo y no asigna roles. No se han probado credenciales reales. Admitir correos personales e institucionales porque un comprador de Posgrado podría ser postulante no matriculado. No usar únicamente el dominio de correo para probar la pertenencia académica.

## Requisitos de seguridad

Las nuevas rutas son protegidas por `auth` y `throttle`. No exponen funciones de cobro, Tesorería ni datos de otros compradores. Los perfiles declarados `identity_verified=false` y `student_status_verified=false` son **indicadores conservadores de ausencia de validación realizada por este módulo**, no certificaciones institucionales.

**Sin cambios de producción, migraciones ni `tm_*`**. Todas las pruebas deben correr con SQLite de desarrollo y CI; no usar datos personales reales.

## Registro técnico de implementación (rama dependiente)

- Spatie y Socialite instalados mediante GitHub Actions y fijados en Composer; migración oficial de RBAC publicada, pero no ejecutada en producción.
- Acceso con Google deshabilitado hasta aprobación de OTI y configuración HTTPS. El callback requiere identidad de correo verificada en la respuesta del proveedor y protege contra vinculación de cuentas únicamente por correo.
- Autorización por defecto denegada; sin seeding de funcionarios ni personal Tesorería. `tm_usuario` y `tm_rol` no se modifican.
- Pruebas de Spatie y del servicio Google con datos ficticios, sin contacto con Google real.
- **Pendientes**: aprovisionamiento de credenciales oficiales, revisión de dominios y callback, permisos institucionales/segregación por oficina, condiciones de enlace de cuentas, despliegue y verificación end-to-end con HTTPS.
