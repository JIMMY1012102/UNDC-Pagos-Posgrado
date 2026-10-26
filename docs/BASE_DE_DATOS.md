# Modelo relacional escalable — tienda de pagos UNDC (v1 propuesta)

**Estado:** migraciones versionadas y pruebas automatizadas de esquema. **No** es una base institucional instalada ni una integración funcional. Pendiente de revisión con OTI, Posgrado y Tesorería.

## Fuentes y criterios

Este diseño toma como referencia el Informe N.° 0217-2025 (validación por Tesorería, registro, reportes y constancia), el flujo de cuatro procesos del proyecto (Acceso, Tienda, Pagos, Tesorería), los manuales de gestión económica como referencias funcionales y las coordinaciones documentadas sobre Interbank, SISPAGOS y Pay-me.

No se presume que los manuales externos sean especificación aprobada, que Pay-me esté contratado, que haya un contrato de API Interbank, ni que la copia local de `tesoreria.sql` sea el esquema final.

## 25 tablas de negocio — 4 grupos

| Grupo | Tablas |
|---|---|
| Identidad, autorizaciones y catálogo (8) | `offices`, `staff_roles`, `staff_role_assignments`, `customer_profiles`, `oauth_identities`, `beneficiaries`, `concepts`, `concept_prices` |
| Compra y pedidos (5) | `carts`, `cart_items`, `orders`, `order_items`, `order_status_history` |
| Pagos e incidencias (6) | `payment_attempts`, `payment_transactions`, `order_payment_claims`, `payment_webhook_events`, `payment_incidents`, `refunds` |
| Tesorería y trazabilidad (6) | `reconciliation_batches`, `reconciliation_entries`, `external_system_links`, `payment_validations`, `validation_certificates`, `audit_events` |

Se reutiliza `users` y el resto de infraestructura estándar de Laravel; **no** se altera ninguna tabla `tm_*` histórica.

### Relaciones y recorrido

```text
users ────── 1:1 ─── customer_profiles ─── 1:N ─── beneficiaries
  │                        │
  ├─ 1:N oauth_identities  └── (datos del comprador; documentos NO únicos)
  ├─ 1:N carts ── 1:N cart_items ────────── concepts ── 1:N concept_prices
  ├─ 1:N orders ─ 1:N order_items ─────────┘
  │              ├── 1:N order_status_history
  │              ├── 1:N payment_attempts
  │              ├── 1:N payment_transactions ── 1:N refunds
  │              │             │
  │              ├── 0..1 order_payment_claims (único por pedido)
  │              ├── 1:N payment_incidents
  │              ├── 1:N payment_validations ── 0..1 validation_certificates
  │              └── 1:N external_system_links (IDs externos, no FK al legado)
  └─ 1:N staff_role_assignments ─── staff_roles + offices

offices ── 1:N concepts, offices ── 1:N order_items
reconciliation_batches ── 1:N reconciliation_entries ── 0..1 payment_transactions
payment_webhook_events ── 0..1 payment_attempts
audit_events ── actor + tipo/ID del recurso (sin datos bancarios sensibles)
```

Un pedido contiene líneas con `office_id`: hoy puede usarse solo Posgrado y mañana varias oficinas, sin agregar columnas por cada oficina. Los precios se versionan por vigencia; las líneas del pedido conservan nombre, código, monto y beneficiario como *snapshot*.

## Reglas funcionales y dónde se hacen cumplir

| Regla | BD | Backend pendiente |
|---|---|---|
| Conceptos independientes por oficina | FK + unicidad (oficina, código/slug) | Visibilidad según permisos y tarifas aprobadas |
| Tarifas históricas | Tabla `concept_prices` e índice por vigencia | Evitar solapamientos y seleccionar precio vigente en transacción |
| Documento del comprador no necesariamente único | Índice no único | Validar formato, consentimiento y elegibilidad real |
| Pedidos con importes congelados | `order_items` con snapshots | Recalcular total, bloquear cambios posteriores, validar suma/currency |
| Canales alternativos de cobro | `channel` y `provider` extensibles | Desactivar cobro adicional tras acreditación; controlar carreras concurrentes |
| Idempotencia de inicio/callback | `idempotency_key` único y (provider, event_key) único | Validar firma/origen, verificar operación remotamente, procesar en transacción |
| Cobro acreditado una vez por pedido | `order_payment_claims.order_id` único | Dentro de una transacción y con bloqueo del pedido, verificar importe, divisa, estado y transacción asociada |
| Segundo cobro real conservado | Múltiples `payment_transactions` + incidentes | Detectar duplicado, alertar a Tesorería y gestionar devolución |
| Conciliación separada de confirmación | Lotes y filas, `settled_at` opcional | Comparar liquidación, comisiones, abono y diferencias |
| Interbank y SISPAGOS preexistentes | Lotes y `external_system_links` | Acuerdo de integración; mapear IDs y evitar doble registro |
| Validación institucional | `payment_validations` | Autorización de Tesorería y requisitos acordados |
| Constancia con código/QR verificable | `validation_certificates` y token guardado como hash | Emitir solo tras validación aprobada; endpoint seguro de consulta y revocación |
| Historial y reportes | Historiales, índices, auditoría y conciliación | Construir consultas según rol, exportaciones y controles de PII |

### Reglas críticas de dinero

1. **Nunca marcar pagado por volver del navegador.** Validar la notificación autenticada y consultar el estado según contrato del proveedor.
2. En la transacción de aplicación, bloquear el pedido (`SELECT ... FOR UPDATE` / `lockForUpdate`), verificar total, moneda, estado, pertenencia del intento y unicidad del identificador externo.
3. Insertar `payment_transactions`; para aplicar al pedido intentar insertar `order_payment_claims`. La restricción `UNIQUE(order_id)` impide acreditar dos transacciones diferentes al mismo pedido incluso entre Pay-me e Interbank.
4. Si llega un segundo cobro, **no borrarlo**: persistir como transacción e incidencia `duplicate_charge`, sin reclamar el pedido de nuevo. No asumir que impedir el segundo registro evita un cobro externo real.
5. Un estado `confirmed` de pasarela **no** implica `settled` bancario ni constancia aprobada por Tesorería.
6. Las devoluciones van a `refunds`; no se eliminan ni se sobrescriben los cobros históricos.
7. No aceptar montos o moneda desde el frontend como fuente de verdad, ni permitir que una línea cambie después de confirmarse el pedido.

### Restricciones que aún no impone la BD

Las columnas `status`, `provider` y `channel` son cadenas deliberadamente extensibles en vez de ENUM. El servicio debe definir máquinas de estados y validaciones. La BD por sí sola **no** garantiza: importes positivos, igualdad de sumas, no solapamiento de tarifas, pertenencia de beneficiarios/cobros al comprador, unicidad de una constancia activa por pedido, valor único de pedidos pendientes, ni autorización de roles. Añadir pruebas específicas al implementar cada servicio. No basta con las pruebas de migraciones.

## Integración segura con SISPAGOS e Interbank

- `external_system_links` registra `system_code`, entidad externa y clave externa; permite relacionar un pedido o pago local sin imponer FK sobre la base histórica.
- `reconciliation_batches` y `reconciliation_entries` preservan entradas importadas, código de deudor, concepto, cuota, referencia, montos bruto/neto, comisiones y estado de cotejo.
- No copiar ciegamente `tm_recaudacion`/`tm_resultado` al nuevo modelo. Se necesita un **mapeo aprobado** con la versión real de SISPAGOS, y reglas de lectura/escritura autorizadas.
- Reporte al día siguiente por Interbank y confirmación en línea por pasarela deben coexistir. No hay pruebas de API/SOAP/servicio bancario contratado en esta rama.

## Privacidad, seguridad y operaciones

- Solo datos ficticios hasta disponer de HTTPS, control de acceso y entorno institucional autorizado.
- Tokens de carrito y constancias guardados como **hash**, no token original; no almacenar PAN/CVV, secretos OAuth, payloads bancarios completos ni documentos en auditoría.
- Proteger DNI, nombres, teléfono y correo con controles institucionales, permisos por oficina, cifrado y política de retención a definir.
- Toda acción financiera requiere auditoría y permisos de servidor, no solo visibilidad de interfaz.
- Evaluar respaldos, restauración, índices y crecimiento tras conocer volumen real. No hacer borrado en cascada de pedidos, pagos o conciliación.

## Instalación SOLO en entorno de prueba autorizado

Migraciones incluidas en `database/migrations/2026_10_25_00000{1,2,3,4}_*.php`; no contienen datos reales ni alteraciones históricas. GitHub CI las prueba usando SQLite en memoria a través de `RefreshDatabase`. Para probar MariaDB local por separado:

```bash
# Requiere disponer de una base NUEVA de prueba, nunca una institucional
php artisan migrate:status
php artisan migrate --pretend
# Solo tras revisar credenciales y nombre de BD de prueba:
php artisan migrate
php artisan test
```

No lanzar `migrate:fresh` contra bases existentes: elimina tablas. No ejecutar migraciones desde GitHub Actions a Oracle.

## Pendientes de decisión institucional

- Catálogo oficial y normas de aprobación de tarifas; fechas de vigencia y restricciones de conceptos.
- Datos obligatorios de Posgrado, identidad verificada, requisitos académicos y beneficiario.
- Esquema y acceso real de SISPAGOS; significado de códigos, cuotas, estado de pago y validación.
- Acuerdo de medios Pay-me, comisiones, callbacks, referencias, reintentos, devoluciones y liquidación.
- Especificaciones de conciliación Interbank y del archivo bancario, o acceso a su interfaz autorizada.
- Quién aprueba la constancia, si es obligatoria en todo caso, formato oficial, QR y condiciones de revocación.
- Políticas de retención, auditoría, permisos, cifrado y control de datos personales.

## Evolución prevista

Se puede agregar una oficina y conceptos sin cambiar las tablas existentes. Nuevos proveedores se resuelven en `app/Integrations/`; cualquier nuevo dato específico de un proveedor debe ir en una tabla de extensión y no añadir decenas de columnas nulas a `orders`. Si más adelante se incorporan comprobantes tributarios, inscripción/matrícula, fraccionamientos, becas o devoluciones parciales complejas, crear módulos y migraciones **nuevas** después de formalizar sus reglas. No se afirma que estos futuros requisitos estén ya implementados.
