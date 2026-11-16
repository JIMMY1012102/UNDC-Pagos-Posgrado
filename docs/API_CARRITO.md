# API JSON de carrito de precompra

El módulo Pedidos usa `tienda_carritos` y `tienda_detalles_carrito`. El carrito puede ser anónimo o estar vinculado a una cuenta. La migración `2026_11_16_000001` garantiza un solo carrito activo por usuario. Un carrito no acredita un pago.

## Rutas

| Método | Ruta | Acceso |
|---|---|---|
| POST | `/api/v1/tienda/carritos` | Crea un carrito y retorna un token bearer aleatorio por única vez |
| GET | `/api/v1/tienda/carritos/actual` | Token anónimo o sesión del propietario |
| POST | `/api/v1/tienda/carritos/actual/items` | Token anónimo o sesión; JSON `{"catalog_item_id":101,"quantity":1}` |
| DELETE | `/api/v1/tienda/carritos/actual/items/{line}` | Token anónimo o sesión; elimina solo líneas de ese carrito |

## Reglas de seguridad

- El token de acceso contiene 256 bits aleatorios y se almacena únicamente como SHA-256 en `tienda_carritos.token_hash`.
- Los carritos anónimos se limitan al portador del token y expiran en 24 horas. Al vincularse, solo se accede con la sesión del propietario.
- GET/POST/DELETE requieren token válido o sesión propietaria; no admiten un ID de carrito arbitrario.
- Para añadir un concepto se usa la misma cotización de Catalogo que genera los pedidos: publicación disponible, precio positivo inequívoco y sin validación académica pendiente.
- Cantidades individuales entre 1 y 20, con máximo 20 por concepto de la línea anónima; aún no existe selección de beneficiarios.
- Los resultados llevan `Cache-Control: no-store` para reducir exposición accidental.
- Las líneas incluyen nombre e importe oficial de referencia. Se devuelve `pricing_status: official` y `total_amount: null`; el total definitivo se cotiza en servidor. `checkout_enabled` depende de `PAYME_ENABLED`, falso por defecto.
- Estas rutas no crean pedidos ni llaman a la pasarela. La vinculación de cuenta consume el token y fusiona selecciones sin superar 20 unidades por concepto/beneficiario; si excede el límite revierte toda la operación con 422.

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
    "pricing_status": "official",
    "total_amount": null,
    "checkout_enabled": false
  },
  "message": "Carrito consultado."
}
```

La respuesta POST de creación tiene estructura `{"success":true,"data":{"token":"<token>","cart":{...}},"message":"Carrito de precompra creado."}`. No se debe registrar ese token en logs ni compartirlo entre usuarios.

## Continuidad

`POST /api/v1/tienda/precompra/vincular-carrito` vincula o fusiona el carrito; `GET /api/v1/tienda/precompra/resumen` recupera la selección propia. Ver [pedidos](PEDIDOS_Y_PAGO_POSGRADO.md) y [operación previa a Pay-me](REVISION_PRE_PAYME.md). El scheduler retira carritos caducados cada cinco minutos; necesita cron Laravel en el servidor.

## Pruebas

`tests/Feature/Modules/CartApiTest.php` cubre creación, hash del token, aislamiento, agregado, eliminación, elementos ocultos, límites y expiración con datos ficticios SQLite. El pipeline CI ejecuta además integración independiente de migraciones MariaDB con fixture SISPAGOS; no toca datos reales.
