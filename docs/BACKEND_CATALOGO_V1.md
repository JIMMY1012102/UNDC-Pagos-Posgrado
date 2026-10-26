# Backend del catálogo — primera iteración (rama dependiente)

## Estado y dependencia Git

La rama `feat/backend-catalogo-v1` deriva de `feat/base-datos-escalable-v1`, cuyo PR #2 continúa en borrador. Estos cambios NO están en `main`, ni desplegados en Oracle, ni validados para producción.

## Código de esta iteración

- `PublishedCatalogQuery` (de la rama base): consulta SISPAGOS y publicaciones de la tienda en la misma base de datos.
- `CatalogService`: devuelve un contrato estructurado de lectura por oficina, sin permitir compras. Usa exclusivamente el costo maestro como **referencia no aprobada para checkout**.
- `CatalogController`: prepara respuesta JSON y valida el límite, pero NO tiene ruta HTTP registrada.
- `CatalogServiceTest`: pruebas con esquema histórico sintético, sin datos personales.

### Contrato interno de lectura (NO endpoint publicado)

```json
{
  "legacy_office_id": 7,
  "pricing_status": "reference_only",
  "purchase_enabled": false,
  "count": 1,
  "items": [
    {
      "id": 1,
      "slug": "visible",
      "legacy_concept_id": 12,
      "code": "DEMO-001",
      "name": "Concepto ficticio",
      "description": null,
      "requirements": null,
      "requires_eligibility_check": false,
      "legacy_base_amount_reference": "10",
      "office": {"id": 7, "name": "Posgrado DEMO"}
    }
  ]
}
```

Todos los datos del ejemplo son ficticios; la representación decimal concreta depende del driver. No se realizan cálculos monetarios.

## Reglas pendientes antes de publicar rutas

1. Validar con OTI/Tesorería si los conceptos de Posgrado y los importes pueden mostrarse públicamente.
2. Confirmar tarifa aplicable y prioridad entre `tm_conceptos.costo` y `store_catalog_price_versions`.
3. Definir autorización, límites de acceso y privacidad, además de códigos HTTP para oficinas inexistentes.
4. Definir compras, elegibilidad y validación de identidad; **ningún precio enviado por el navegador será aceptado como total**.
5. Separar métodos internos de gestión de Tesorería de las consultas públicas.

## Ejecución de pruebas

```bash
php artisan test --filter=CatalogServiceTest
php artisan test
```

No ejecutar `php artisan migrate` contra servidores institucionales sin revisión y respaldo autorizados.

## Integración Git

Revisar primero PR #2. Para esta rama, preparar PR con base en `feat/base-datos-escalable-v1` mientras PR #2 esté pendiente; después de aprobar la base, actualizar la dependencia y verificar CI antes de fusionar a `main`.
