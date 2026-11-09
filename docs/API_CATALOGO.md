# API JSON de catálogo — fase inicial

El backend se desarrolla como monolito modular Laravel, conforme a `docs/ARQUITECTURA.md`. Este primer corte implementa **solo consulta**, sin activar compras, precios, autenticación institucional o integración de pasarelas.

## Endpoint

`GET /api/v1/tienda/catalogo?office_id=7`

- `office_id` es obligatorio y debe ser entero positivo. No se presupone que el ID 7 corresponda al Posgrado real.
- La consulta existente `PublishedCatalogQuery` filtra oficinas activas, catálogos habilitados y conceptos publicados, con costo histórico válido. **No devuelve el costo** como importe cobrable.
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
      "pricing_status": "pending_institutional_approval",
      "checkout_enabled": false
    }
  ],
  "message": "Catálogo consultado correctamente."
}
```

## Separación por capas

- `app/Modules/Tienda/routes.php`: ruta HTTP y limitación de frecuencia.
- `app/Modules/Tienda/Http/Controllers/CatalogController.php`: entrada, respuesta y error 422.
- `app/Modules/Tienda/Services/CatalogService.php`: transformación a contrato público JSON.
- `app/Modules/Tienda/Queries/PublishedCatalogQuery.php`: consulta de solo lectura a maestros SISPAGOS + publicaciones de la tienda.
- `tests/Feature/Modules/CatalogApiTest.php`: prueba entrada, publicación, oficinas inactivas y método GET.

El frontend podrá consumir el JSON. Los demás módulos continúan separados y no se acoplan mediante consultas HTTP internas. No existe API para carrito, pedidos, cobros o conciliación en este PR.

## Antes de uso institucional

Confirmar esquema vigente y qué contenidos están autorizados para difusión pública; obtener tarifas de Posgrado y reglas aprobadas; desplegar solo en entorno autorizado y seguro. No ejecutar migraciones de la tienda ni de SISPAGOS sin autorización. No publicar datos bancarios ni información personal.
