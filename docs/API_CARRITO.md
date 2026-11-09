# API JSON de carrito de precompra — fase 2

Este módulo usa las tablas `tienda_carritos` y `tienda_detalles_carrito` existentes, sin modificar migraciones. Es un carrito **anónimo de preparación**, no una orden ni un cobro. Sigue separado en controlador, servicio y rutas del módulo Tienda.

## Rutas

| Método | Ruta | Acceso |
|---|---|---|
| POST | `/api/v1/tienda/carritos` | Crea un carrito y retorna un token bearer aleatorio por única vez |
| GET | `/api/v1/tienda/carritos/actual` | Requiere `Authorization: Bearer <token>` |
| POST | `/api/v1/tienda/carritos/actual/items` | Requiere token y JSON `{"catalog_item_id":101,"quantity":1}` |
| DELETE | `/api/v1/tienda/carritos/actual/items/{line}` | Requiere token; elimina solo líneas de ese carrito |

## Reglas de seguridad

- El token de acceso contiene 256 bits aleatorios y se almacena únicamente como SHA-256 en `tienda_carritos.token_hash`.
- Los carritos se limitan a la sesión portadora del token, permanecen anónimos y expiran en 24 horas.
- GET/POST/DELETE requieren un bearer token válido; el endpoint no admite un ID de carrito arbitrario.
- Para añadir un concepto se comprueban catálogo habilitado, publicación vigente, oficina activa y costo maestro no negativo. Esto **no convierte ese costo en tarifa de cobro aprobada**.
- Cantidades individuales entre 1 y 20, con máximo 20 por concepto de la línea anónima; aún no existe selección de beneficiarios.
- Los resultados llevan `Cache-Control: no-store` para reducir exposición accidental.
- No se muestran montos, datos personales ni operaciones bancarias. Se devuelve `pricing_status: pending_institutional_approval`, `total_amount: null` y `checkout_enabled: false`.
- No se crean pedidos ni se llama a la pasarela. La asociación con una cuenta y la verificación del beneficiario se posponen.

## Contrato JSON

```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "active",
    "currency": "PEN",
    "items": [{"id": 1, "catalog_item_id": 101, "slug": "publicado", "quantity": 2}],
    "items_count": 2,
    "pricing_status": "pending_institutional_approval",
    "total_amount": null,
    "checkout_enabled": false
  },
  "message": "Carrito consultado."
}
```

La respuesta POST de creación tiene estructura `{"success":true,"data":{"token":"<token>","cart":{...}},"message":"Carrito de precompra creado."}`. No se debe registrar ese token en logs ni compartirlo entre usuarios.

## Futuros incrementos

Asociar usuario autenticado, beneficiarios y órdenes pendientes *solo cuando existan decisiones institucionales y validaciones autorizadas*. No se habilita ningún checkout antes de ese momento.

## Pruebas

`tests/Feature/Modules/CartApiTest.php` cubre creación, hash del token, aislamiento, agregado, eliminación, elementos ocultos, límites y expiración con datos ficticios SQLite. El pipeline CI ejecuta además integración independiente de migraciones MariaDB con fixture SISPAGOS; no toca datos reales.
