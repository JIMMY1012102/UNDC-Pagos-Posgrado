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

Se adopta **spatie/laravel-permission** para gestión futura de permisos y roles del backend Laravel. La versión 8.x es compatible con PHP 8.3 y Laravel 13 según documentación vigente; **debe instalarse y bloquearse en composer.lock mediante Composer real** antes de habilitar middleware o migraciones de Spatie. No crear archivos lock ficticios ni copiar migraciones de otra versión.

`Spatie` administrará las relaciones RBAC de Laravel en tablas propias de la aplicación. **No alterar ni reemplazar** la tabla `tm_rol`, ni asumir que los roles históricos de SISPAGOS coinciden con roles del nuevo sistema. No se asignan roles administrativos ni permisos de Tesorería por defecto, ni se crean usuarios privilegiados.

Contratos de acceso a concretar con Alfonso/OTI: roles autorizados, alcance por oficina, quién los asigna, permiso de consultar/conciliar, aprobación/revocación, guard `web`, auditoría y segregación de funciones. La autorización deberá realizarse del lado servidor con Gate/Policy y middleware tras instalar Spatie, nunca depender de controles del frontend.

## OAuth Google

La integración Google real exige credenciales, dominio seguro HTTPS, callback y políticas de seguridad validadas; se deja pendiente. Admitir correos personales e institucionales porque un comprador de Posgrado podría ser postulante no matriculado. No usar únicamente el dominio de correo para probar la pertenencia académica.

## Requisitos de seguridad

Las nuevas rutas son protegidas por `auth` y `throttle`. No exponen funciones de cobro, Tesorería ni datos de otros compradores. Los perfiles declarados `identity_verified=false` y `student_status_verified=false` son **indicadores conservadores de ausencia de validación realizada por este módulo**, no certificaciones institucionales.

**Sin cambios de producción, migraciones ni `tm_*`**. Todas las pruebas deben correr con SQLite de desarrollo y CI; no usar datos personales reales.
