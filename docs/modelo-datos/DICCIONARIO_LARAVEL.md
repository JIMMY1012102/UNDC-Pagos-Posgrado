# Diccionario técnico Laravel — 9 tablas

> Fuente exclusiva: migraciones estándar presentes en el repositorio. No se presumen relaciones FK cuando no están declaradas.

## `users`

| Campo | Tipo Laravel | PK / índice / nulabilidad | Función |
|---|---|---|---|
| `id` | `id` | PK | Clave del registro |
| `name` | `string` | requerido | Nombre descriptivo |
| `email` | `string` | requerido, UNIQUE | Correo/identificador de cuenta |
| `email_verified_at` | `timestamp` | nullable | Fecha/hora o marca operacional |
| `password` | `string` | requerido | Hash de credencial |
| `remember_token` | `rememberToken` | nullable | Token de autenticación |
| `created_at / updated_at` | `timestamps` | requerido | Fecha/hora o marca operacional |


## `password_reset_tokens`

| Campo | Tipo Laravel | PK / índice / nulabilidad | Función |
|---|---|---|---|
| `email` | `string` | PK | Correo/identificador de cuenta |
| `token` | `string` | requerido | Token de autenticación |
| `created_at` | `timestamp` | nullable | Fecha/hora o marca operacional |


## `sessions`

| Campo | Tipo Laravel | PK / índice / nulabilidad | Función |
|---|---|---|---|
| `id` | `string` | PK | Clave del registro |
| `user_id` | `foreignId` | nullable, INDEX | Identificador de usuario; en sessions no se declara FK |
| `ip_address` | `string` | nullable | Campo técnico Laravel |
| `user_agent` | `text` | nullable | Campo técnico Laravel |
| `payload` | `longText` | requerido | Datos operacionales internos del framework |
| `last_activity` | `integer` | requerido, INDEX | Campo técnico Laravel |


## `cache`

| Campo | Tipo Laravel | PK / índice / nulabilidad | Función |
|---|---|---|---|
| `key` | `string` | PK | Clave de caché |
| `value` | `mediumText` | requerido | Campo técnico Laravel |
| `expiration` | `bigInteger` | requerido, INDEX | Fecha/hora o marca operacional |


## `cache_locks`

| Campo | Tipo Laravel | PK / índice / nulabilidad | Función |
|---|---|---|---|
| `key` | `string` | PK | Clave de caché |
| `owner` | `string` | requerido | Campo técnico Laravel |
| `expiration` | `bigInteger` | requerido, INDEX | Fecha/hora o marca operacional |


## `jobs`

| Campo | Tipo Laravel | PK / índice / nulabilidad | Función |
|---|---|---|---|
| `id` | `id` | PK | Clave del registro |
| `queue` | `string` | requerido, INDEX | Cola de procesamiento |
| `payload` | `longText` | requerido | Datos operacionales internos del framework |
| `attempts` | `unsignedSmallInteger` | requerido | Fecha/hora o marca operacional |
| `reserved_at` | `unsignedInteger` | nullable | Fecha/hora o marca operacional |
| `available_at` | `unsignedInteger` | requerido | Fecha/hora o marca operacional |
| `created_at` | `unsignedInteger` | requerido | Fecha/hora o marca operacional |


## `job_batches`

| Campo | Tipo Laravel | PK / índice / nulabilidad | Función |
|---|---|---|---|
| `id` | `string` | PK | Clave del registro |
| `name` | `string` | requerido | Nombre descriptivo |
| `total_jobs` | `integer` | requerido | Campo técnico Laravel |
| `pending_jobs` | `integer` | requerido | Campo técnico Laravel |
| `failed_jobs` | `integer` | requerido | Campo técnico Laravel |
| `failed_job_ids` | `longText` | requerido | Campo técnico Laravel |
| `options` | `mediumText` | nullable | Campo técnico Laravel |
| `cancelled_at` | `integer` | nullable | Fecha/hora o marca operacional |
| `created_at` | `integer` | requerido | Fecha/hora o marca operacional |
| `finished_at` | `integer` | nullable | Fecha/hora o marca operacional |


## `failed_jobs`

| Campo | Tipo Laravel | PK / índice / nulabilidad | Función |
|---|---|---|---|
| `id` | `id` | PK | Clave del registro |
| `uuid` | `string` | requerido, UNIQUE | Campo técnico Laravel |
| `connection` | `string` | requerido | Campo técnico Laravel |
| `queue` | `string` | requerido | Cola de procesamiento |
| `payload` | `longText` | requerido | Datos operacionales internos del framework |
| `exception` | `longText` | requerido | Campo técnico Laravel |
| `failed_at` | `timestamp` | requerido | Fecha/hora o marca operacional |


## Consideraciones
- `sessions.user_id` solo tiene índice; la migración no declara FOREIGN KEY a `users`.
- `migrations` figura en el inventario operativo de 9 tablas según informe, pero no aparece en las tres migraciones de Laravel. Es tabla de control del migrador; confirmar DDL real en el entorno autorizado antes de pretender describir cada columna.
- Ocho tablas están expresamente creadas por las tres migraciones mostradas; la novena `migrations` es administrada por el framework.
- `tm_usuario` pertenece a SISPAGOS y no se unifica con `users`.