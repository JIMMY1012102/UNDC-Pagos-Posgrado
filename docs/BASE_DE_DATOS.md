# UNDC · Modelo de datos compartido SISPAGOS + tienda (propuesta v2)

**Fuente primaria:** `tesoreria.sql` entregado en el proyecto. Exportación phpMyAdmin 5.2.1 del **20/02/2026**, MariaDB **10.4.32**, base `tesoreria`. Se revisaron las declaraciones de tablas, índices, incrementos automáticos y el formato de los registros. **El volcado original incluye datos personales: no se versiona en GitHub.**

**Estado:** adaptación de migraciones Laravel revisable. No se ejecutaron migraciones en servidor, no se intervino en SISPAGOS, no hay Pay-me contratado/implementado. La definición real de acceso y despliegue debe aprobarla OTI/Tesorería.

## 1. Tablas originales que son el punto de partida

| Tabla existente (se conserva) | PK (INT firmado) | Campos relevantes | Responsabilidad |
|---|---|---|---|
| `tm_oficina` | `oficina_id` | `oficina_nom`, `estado` | 37 oficinas, incluidas Posgrado y Tesorería |
| `tm_rol` | `rol_id` | `rol_nom`, `estado` | 4 roles internos |
| `tm_usuario` | `usu_id` | `rol_id`, `oficina_id`, `usu_correo`, `estado`, `fech_elim` | Personal interno de SISPAGOS |
| `tm_conceptos` | `id_concepto` | `codigo_concepto`, `concepto`, `costo`, `centro_costo`, `codigo_banco`, campos contables | Conceptos y costo institucional |
| `tm_recaudacion` | `id_recaudacion` | `codigo_deudor`, `codigo_cuota`, `referencia1/2`, `concepto1..concepto7`, `tipo_operacion` | Cobranza cargada al banco |
| `tm_resultado` | `i_resultado` | `codigo_deudor`, `codigo_cuota`, `numero_operacion`, `importe_pagado`, `fecha_pago`, `hora_pago` | Resultados bancarios importados |

**Hallazgos importantes del SQL real**:
- Solo hay **PRIMARY KEY**, sin declaraciones de FOREIGN KEY ni claves únicas adicionales para `tm_*`. Por ello, correlaciones entre tablas son **lógicas, no garantizadas** por el respaldo.
- `tm_conceptos` ya contiene conceptos de Posgrado. No crear una segunda tabla maestra `concepts`.
- `tm_oficina` ya incluye Posgrado (ID 7) y Tesorería (ID 19) **en esta exportación**; no asumir IDs idénticos en otro ambiente.
- `tm_conceptos.centro_costo` **no tiene FK** a `tm_oficina.oficina_id`; no equipararlos sin confirmación. La nueva tabla de publicación requiere asociación explícita validada.
- `tm_recaudacion` separa los importes en siete columnas; **no** presupone que `codigo_cuota` sea `codigo_concepto`, ni que una sola fila equivalga a un pedido web.
- `tm_resultado.importe_pagado` permite cero y `numero_operacion` es nullable, sin unicidad. No basta `importe_pagado IS NOT NULL` para verificar pagos. Un matching solo por nombre/código/cuota no confirma monto, duplicados ni identidad.
- El volcado **no prueba** que el esquema del servidor actual sea idéntico al de febrero de 2026. Verificar antes de producción.

## 2. Arquitectura definitiva propuesta: una sola base, dos aplicaciones

```text
               Tienda virtual (Laravel)
               usuarios públicos / pedidos
                          │
                          ▼
             MISMA BASE: tesoreria
             ┌──────────────────────────┐
             │ tm_*  = SISPAGOS actual  │
             │ store_* = tienda nueva   │
             │ users  = comprador web   │
             └──────────────────────────┘
                          ▲
                          │
               SISPAGOS (Tesorería)
               validación, reportes, control
```

Las tablas `tm_*` se mantienen intactas. Se agregan tablas **`store_*` en el mismo esquema** de MariaDB. Dos aplicaciones pueden leer los datos según **privilegios SQL, roles, restricciones y políticas** aprobados; no significa que la tienda deba tener permiso para editar toda la Tesorería.

`users` de Laravel corresponde a cuentas web; `tm_usuario` corresponde a personal interno de SISPAGOS. **No se unifican por correo automáticamente**, porque la autenticación y los privilegios son diferentes.

## 3. Tablas nuevas por proceso (23)

| Grupo | Tablas nuevas |
|---|---|
| Publicación, identidad y precios (6) | `store_office_settings`, `store_catalog_items`, `store_catalog_price_versions`, `store_customer_profiles`, `store_oauth_identities`, `store_beneficiaries` |
| Carrito y compra (5) | `store_carts`, `store_cart_items`, `store_orders`, `store_order_items`, `store_order_status_history` |
| Pasarelas y transacciones (6) | `store_payment_attempts`, `store_payment_transactions`, `store_order_payment_claims`, `store_payment_webhook_events`, `store_payment_incidents`, `store_refunds` |
| Conciliación y Tesorería (6) | `store_reconciliation_batches`, `store_reconciliation_entries`, `store_external_system_links`, `store_payment_validations`, `store_validation_certificates`, `store_audit_events` |

### 3.1 Identificación exacta de maestros

- `store_office_settings.legacy_office_id` = `tm_oficina.oficina_id`; atributos de tienda (slug, publicación), sin duplicar nombres maestros.
- `store_catalog_items.legacy_concept_id` = `tm_conceptos.id_concepto`. `legacy_office_id` apunta explícitamente a la oficina autorizada para ofrecer ese concepto. Los códigos, nombres y costo oficial **se consultan de la tabla original**.
- `store_catalog_price_versions` permite tarifas aprobadas con intervalos, sin reescribir la historia; la regla de precedencia con `tm_conceptos.costo` requiere acuerdo de Tesorería. Un servicio debe impedir periodos activos solapados.
- En `store_order_items` se guarda una **instantánea** de concepto, monto y beneficiario. Cambiar la tarifa o nombre original después NO altera un pedido ya aceptado.

**Nota técnica (migración 000005):** los IDs históricos son `INT(11)` firmados y las columnas nuevas `legacy_*` usan `integer` firmado. La migración de integración exige la presencia del esquema histórico en MariaDB/MySQL y agrega **12 claves foráneas reales** hacia `tm_oficina`, `tm_conceptos`, `tm_usuario`, `tm_recaudacion` y `tm_resultado` con borrado/actualización restringidos. Se omite únicamente en SQLite de pruebas, que no importa las seis tablas originales. Se realiza otra prueba en MariaDB aislada con un fixture que replica las estructuras sin datos reales. No se modifica ninguna `tm_*`. No se fuerza una relación de `tm_conceptos.centro_costo` a oficina: la publicación lo define explícitamente.

### 3.2 Canales: Pay-me e Interbank

- `store_payment_attempts` registra intentos, proveedor, monto esperado, idempotencia y estado.
- `store_payment_transactions` registra las operaciones observadas y su verificación; **no** se borra el segundo cargo.
- `store_order_payment_claims` tiene un índice único por `order_id`; la aplicación solo aplica un cobro a cada pedido, después de validar con el servidor el monto, moneda, orden, proveedor y referencia.
- `store_payment_webhook_events` impide registrar dos veces el mismo identificador de evento por proveedor. No almacenar CVV/PAN, llaves ni payloads sensibles.
- `store_payment_incidents` registra cobro duplicado, importe distinto, resultado incierto, etc.; `store_refunds` registra devoluciones y su resultado.

La unicidad SQL **no puede impedir que dos proveedores externos cobren dinero**: permite detectar, conservar evidencia y **no acreditar dos veces** en la UNDC. Se requieren locks/transacciones y confirmación servidor-servidor.

### 3.3 Unión con archivos de Interbank, SISPAGOS y constancias

- `store_reconciliation_batches` agrupa lotes de resultados de pasarela o importaciones bancarias.
- `store_reconciliation_entries.legacy_result_id` identifica, cuando corresponda, `tm_resultado.i_resultado`; `legacy_recaudacion_id` identifica `tm_recaudacion.id_recaudacion`. Campos nullable: no se debe inventar una coincidencia.
- `store_external_system_links` guarda `system_code`, `external_entity`, `external_record_key` e ID externo para otros mapeos; sin necesidad de modificar registros históricos.
- `store_payment_validations` separa la decisión de Tesorería de la confirmación de la pasarela. `legacy_reviewer_id` se refiere a personal de `tm_usuario`.
- `store_validation_certificates` prepara código y hash de token QR para una futura constancia aprobada; **la emisión no está implementada**.
- `store_audit_events` registra actor comprador/funcionario y evento sin datos de tarjeta ni secretos.

## 4. Reglas que exige el backend antes de producción

1. El catálogo muestra solo oficinas activas, conceptos originales válidos, publicaciones aprobadas y tarifas vigentes (verificación **servidor**).
2. El carrito permite seleccionar concepto/beneficiario; el pedido congela precio, moneda, cantidad y datos sin depender de valores recibidos del navegador.
3. Nunca confirmar un pago solo por una redirección; verificar operación y firma según especificación del proveedor.
4. Para acreditar un pago: transacción DB + bloqueo del pedido + comprobación de moneda, monto, estado, referencia, beneficiario y idempotencia; crear `store_order_payment_claims`.
5. El informe bancario no convierte una operación cero/null/no conciliada en `pagado`; matching debe resolver diferencias y duplicados con reglas acordadas y registro de evidencia.
6. Tesorería solo visualiza, aprueba y exporta información de acuerdo con autorización institucional; los usuarios de `tm_usuario` deben comprobar `estado`, `fech_elim` y rol/oficina. No vincularlos implícitamente con `users`.
7. Constancia y QR solamente después de la aprobación real de Tesorería; mecanismos de revocación y control de duplicados.
8. Mantener fuentes de auditoría y políticas de retención, seguridad de PII y permisos mínimos.

Las cadenas de estado son deliberadamente extensibles, pero **las migraciones por sí mismas no implementan máquinas de estados, importadores, vistas de SISPAGOS, control de saldos, reglas bancarias ni autorización**.

## 5. Preparación y pruebas sin tocar producción

```bash
# Verificar seis tablas y columnas en la conexión activa, sin escrituras:
php artisan treasury:schema-check

# Validar estado de migraciones:
php artisan migrate:status
php artisan migrate --pretend

# Pruebas SQLite en memoria, datos ficticios:
php artisan test

# Ejecutar migraciones SOLAMENTE después de configurar una copia de desarrollo
# de MariaDB y acordar el esquema con la OTI/Tesorería:
php artisan migrate
```

Las primeras cuatro migraciones crean `store_*`, y la quinta aplica restricciones a esas nuevas tablas para referenciar registros `tm_*` existentes. Ninguna llama `ALTER TABLE tm_*`, inserta pagos ni configura conectores externos. Un `php artisan migrate` sobre una base nueva sin las seis tablas históricas **no crea por arte de magia la base compartida**; primero debe existir copia de **estructura del legado** en el entorno de pruebas, sin trasladar datos personales de producción.

Antes de utilizarla en la UNDC: verificar conexiones de ambas aplicaciones hacia el mismo esquema, permisos por cuenta SQL, compatibilidad con la versión actual y validación de **lectura desde SISPAGOS** de las nuevas tablas mediante consultas o vistas autorizadas.

## 6. Información pendiente de confirmación con Alfonso y Tesorería

- Versión **vigente** de la BD SISPAGOS, mapeo exacto de códigos de cuota/deudor/concepto, permisos, roles y relaciones reales.
- Qué conceptos de Posgrado se venderán, precios aprobados, tarifas, diferencias entre matrícula, pensión, prospecto y derecho de examen.
- Qué lectura/escritura requerirá la tienda sobre `tm_recaudacion` y `tm_resultado`; quién sube el archivo bancario y cuándo se concilia.
- Proveedor de pasarela, referencias, integración y reglas de cobro y devolución; Pay-me continúa candidato.
- Quién valida, cuándo se emite la constancia, tratamiento de comprobante oficial/QR y conexión con trámites académicos.
- Escenarios de doble pago Interbank/pasarela, anulaciones, pagos parciales y continuidad para futuros conceptos y oficinas.

## 7. Alcance de esta corrección

Se corrigió el borrador del PR #2 que proponía duplicar `offices`, `staff_roles`, `concepts`. Ahora el **catálogo institucional tiene una sola fuente maestra: SISPAGOS**. La nueva tienda conserva sus propias entidades transaccionales sin copiar históricos. El diseño es preparado para crecer, pero todavía debe validarse contra la base activa y las reglas acordadas con el equipo.

**Prohibición operacional:** no subir `tesoreria.sql` con registros reales, `.env`, credenciales, llaves privadas o datos bancarios identificables al repositorio.

## 8. Integración física y crecimiento (actualización del PR)

La migración `database/migrations/2026_10_25_000005_add_store_legacy_foreign_keys.php` vincula los registros de la tienda a maestros históricos a nivel InnoDB:

| Tabla nueva | Columna | Maestro original |
|---|---|---|
| `store_office_settings` | `legacy_office_id` | `tm_oficina.oficina_id` |
| `store_catalog_items` | `legacy_office_id`, `legacy_concept_id` | `tm_oficina`, `tm_conceptos` |
| `store_order_items` | `legacy_office_id`, `legacy_concept_id` | `tm_oficina`, `tm_conceptos` |
| `store_reconciliation_batches` | `legacy_importer_id` | `tm_usuario.usu_id` |
| `store_reconciliation_entries` | `legacy_result_id`, `legacy_recaudacion_id` | `tm_resultado`, `tm_recaudacion` |
| `store_payment_validations` | `legacy_reviewer_id` | `tm_usuario.usu_id` |
| `store_validation_certificates` | `legacy_issuer_id`, `legacy_revoker_id` | `tm_usuario.usu_id` |
| `store_audit_events` | `legacy_staff_id` | `tm_usuario.usu_id` |

**Lectura conectada:** `app/Modules/Tienda/Queries/PublishedCatalogQuery.php` realiza JOIN desde `store_catalog_items` a `tm_conceptos`, `tm_oficina` y `store_office_settings` (solo oficinas activas/publicadas), retornando el **costo original** como importe de referencia. No publica endpoint HTTP hasta aprobar tarifas, reglas académicas y protección de datos. `database/queries/sispagos_store_payments_readonly.sql` muestra en sentido inverso los pedidos de tienda para SISPAGOS.

**Pruebas reales:** `.github/workflows/ci.yml` incorpora un trabajo MariaDB 10.11 con esquema original **ficticio/sin estudiantes**, ejecuta las cinco migraciones y verifica las 12 claves foráneas y el rechazo de referencias inexistentes. El job SQLite mantiene pruebas de estructura y regresión sin almacenar `tesoreria.sql`.

**Extensibilidad:** nuevas oficinas se habilitan en `tm_oficina` + `store_office_settings`, los conceptos en `tm_conceptos` + `store_catalog_items`. Nuevos flujos internos se agregan en módulos y migraciones nuevos con pruebas, no alterando tablas originales arbitrariamente. Proveedores se incorporan mediante adaptadores independientes y tablas de extensión si requieren información adicional. Las tablas de auditoría, estados y reportes permiten evolución, pero no sustituyen especificación, seguridad ni pruebas del dominio.

**Precaución:** en una base real los cambios de DDL deben acordarse en ventana autorizada, revisando copias, privilegios, motores, compatibilidad, referencias y posibles bloqueos de metadatos. Aunque la migración solo altera tablas nuevas, una FK restringe el borrado/actualización de un maestro histórico referenciado: es un cambio conductual que debe aprobar Tesorería.
