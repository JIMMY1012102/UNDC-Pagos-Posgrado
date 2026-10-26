# Revisión estructural y decisiones pendientes (sin DDL)

Fuente: migraciones Laravel 000001–000005 y documentación existente `docs/BASE_DE_DATOS.md`. Auditoría estática: **no** se ejecutó consulta sobre Oracle ni sobre la base institucional.

## Revisiones constatadas
- Se preservan las seis tablas maestras `tm_*`; las 23 tablas `store_*` están distribuidas en seis dominios.
- Se identifican las doce FK explícitas de la migración 000005, que enlazan cinco tablas históricas.
- `store_order_payment_claims.order_id` es UNIQUE y `payment_transaction_id` también, para evitar dobles acreditaciones lógicas.
- `store_order_items` conserva una instantánea de nombre de concepto, precio y beneficiario. Es desnormalización intencional para preservar el historial.
- `users` de Laravel corresponde a compradores; `tm_usuario` representa personal de SISPAGOS. No combinar identidades por correo.

## Observaciones priorizadas (propuestas, no cambios aplicados)
1. **Correspondencia de órdenes:** la FK de `store_order_payment_claims` a una orden y a una transacción no obliga por sí sola a que ambas pertenezcan a la misma orden. Exigir validación transaccional y valorar una restricción compuesta tras definir operaciones.
2. **NULL y unicidad externa:** las referencias opcionales `provider_transaction_reference`, `provider_session_reference` y referencias de lotes usan índices únicos compuestos; MariaDB permite múltiples filas con NULL. Definir idempotencia para referencias todavía desconocidas.
3. **Intervalos de precios:** `store_catalog_price_versions` tiene índice temporal, pero no exclusión de intervalos superpuestos. Definir regla de precedencia del costo maestro, precio aprobado y fechas.
4. **Actores administrativos:** `store_payment_incidents.resolved_by` y `store_refunds.requested_by` referencian `users` compradores. Validar si deberán ser identificadores del personal `tm_usuario`, cuentas de servicio u otra estrategia. No cambiar antes de aprobación.
5. **Estados de dominio:** campos de estado son VARCHAR sin máquina de transiciones en esquema. Documentar estados válidos de pedido, operación, conciliación, devolución y validación antes de desarrollar negocio.
6. **Normalización legada:** `tm_recaudacion` utiliza campos de concepto repetidos (concepto1..concepto7). No alterar legado; adaptar consultas y revisar interpretación de cuotas.
7. **Campos financieros:** especificar integridad de cantidad, montos y moneda, además de sumatoria, descuentos o devoluciones cuando OTI/Tesorería apruebe los escenarios.
8. **Índices:** existen índices de estado/fecha y referencias; no añadir índices especulativos hasta observar consultas y planes EXPLAIN.
9. **Roles:** evaluar Spatie Permission después de acordar responsabilidades y permisos. No se ha constatado instalación ni se crean tablas.
10. **DDL actual:** el esquema histórico fue documentado desde el respaldo del 20/02/2026. Compararlo con la versión institucional autorizada sigue pendiente.

## Alcance del diccionario y diagramas
- El diccionario detalla las columnas `store_*` inferidas de migraciones y enumera las PK históricas. **No pretende documentar todas las columnas heredadas ni las nueve tablas técnicas**, que requieren DDL comprobado cuando corresponda.
- En los ER parciales se muestran solo FK internas del módulo. En el índice se enumeran las doce FK físicas con el legado.
- La cardinalidad ER simplificada debe leerse junto con `nullable()` y restricciones SQL; no es prueba de que una tabla contenga registros.
- Convención visual acordada: SISPAGOS gris, tienda verde, seguridad morado e infraestructura neutro. La paleta se aplicará en una visualización física posterior; los diagramas Mermaid son editables y estructurales.

## Puertas de aprobación
- Alfonso/OTI y Tesorería deben confirmar esquema, política de borrado RESTRICT, tarifas, estados, roles y conciliación.
- Cualquier corrección DDL debe justificarse por causa e impacto y presentarse en otra revisión antes de aplicarse.
- Prohibido modificar `tm_*`, desarrollar backend, fusionar a main, migrar Oracle o usar datos personales de producción.

## Control de cierre documental — 26/10/2026

- [x] Inventario visual de las 38 entidades agrupadas: `MAPA_38_TABLAS.md`.
- [x] Diccionario de las ocho tablas creadas por las tres migraciones Laravel; `migrations` se identifica como tabla operativa del framework sin DDL encontrado en esas migraciones.
- [x] Diccionario de las 67 columnas incluidas en el fixture sanitizado `tests/Fixtures/tesoreria_schema_ci.sql`; fuente histórica de CI, pendiente validar DDL institucional actual.
- [x] Aplicación inicial de paleta de dominio a los diagramas Mermaid, sujeta a compatibilidad de renderizado de GitHub.
- [ ] Completar tipos, PK/FK/índices y nullability de **todas** las columnas institucionales cuando se apruebe compartir DDL sanitizado del entorno vigente.
- [ ] Confirmar DDL exacto de `migrations` y comparar contra inventario de MariaDB.
- [x] Elaborar matriz de cardinalidades y diagramas por módulo con FK internas y externas desde el código.
- [ ] Validar manualmente el renderizado de los diagramas Mermaid en GitHub; el mapa general es una agrupación de dominio, no reemplaza un ER físico integral.
- [ ] Registrar decisiones de OTI/Tesorería sobre puntos estructurales antes de cualquier propuesta de migración.

**Nota metodológica:** Esta documentación se deduce del código versionado. La ejecución exitosa de CI anterior no sustituye la aceptación funcional de todas las reglas de negocio. No se ha ejecutado ningún DDL ni añadido backend.

## Acuerdo de alcance actual

**Aplazado explícitamente:** decisiones de tarifas, permisos, roles, reglas de conciliación, validación, constancias y políticas financieras. No bloquear el avance documental por estas definiciones ni tratarlas como aprobadas. La revisión estructural describe riesgos, pero **no** instruye cambiar migraciones. Permanecen como requisitos futuros.

**Sin evidencia de producción:** falta contrastar el fixture CI con el esquema institucional vigente y la estructura exacta de la tabla `migrations` en MariaDB. No afirmar que esta comprobación haya sido realizada.
