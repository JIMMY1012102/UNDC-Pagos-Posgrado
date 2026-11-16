# API JSON de catálogo

El backend se desarrolla como monolito modular Laravel, conforme a `docs/ARQUITECTURA.md`. El módulo Catalogo expone publicaciones e importes oficiales; Pedidos administra las compras pendientes y Pagos conserva la integración de pasarela por implementar.

## Endpoint

`GET /api/v1/tienda/catalogo?office_id=7`

- `office_id` es obligatorio y debe ser entero positivo. No se presupone que el ID 7 corresponda al Posgrado real.
- La consulta existente `PublishedCatalogQuery` filtra oficinas activas, catálogos habilitados y conceptos publicados, con costo histórico válido. La cotización usa el costo institucional o una versión PEN aprobada vigente, con validación en servidor.
- El endpoint devuelve JSON con `success`, `data` y `message`.
- El resultado vacío es `200` con `data: []`; entrada incorrecta retorna `422` con `errors`.
- Límite básico por ruta: 60 solicitudes/minuto, sujeto a configuración del entorno.

Ejemplo exclusivamente ficticio:

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "slug": "demo",
      "code": "DEMO-OTI-001",
      "name": "Concepto ficticio",
      "description": null,
      "requirements": null,
      "requires_eligibility_check": false,
      "office": {"id": 7, "name": "Posgrado ficticio"},
      "reference_amount": "10.00",
      "orderable": true,
      "orderable_reason": null,
      "pricing_status": "official",
      "checkout_enabled": false
    }
  ],
  "message": "Catálogo consultado correctamente."
}
```

## Separación por capas

- `app/Modules/Catalogo/routes.php`: ruta HTTP y limitación de frecuencia.
- `app/Modules/Catalogo/Http/Controllers/CatalogController.php`: entrada, respuesta y error 422.
- `app/Modules/Catalogo/Services/CatalogService.php`: transformación a contrato público JSON.
- `app/Modules/Sispagos/Queries/PublishedCatalogQuery.php`: consulta de solo lectura a maestros SISPAGOS + publicaciones de la tienda.
- `tests/Feature/Modules/CatalogApiTest.php`: prueba entrada, publicación, oficinas inactivas y método GET.

El frontend consume este JSON. `orderable=false` informa `eligibility_required` o `invalid_or_ambiguous_price`; el carrito rechaza estos conceptos. `PricingPolicy` separa `pricing_status=official` de `checkout_enabled`, controlado por `PAYME_ENABLED` (falso por defecto). Una tarifa oficial no implica una pasarela funcional. Ver [pedidos](PEDIDOS_Y_PAGO_POSGRADO.md).

## Antes de uso institucional

Confirmar esquema vigente y qué contenidos están autorizados para difusión pública; obtener tarifas de Posgrado y reglas aprobadas; desplegar solo en entorno autorizado y seguro. No ejecutar migraciones de la tienda ni de SISPAGOS sin autorización. No publicar datos bancarios ni información personal.
