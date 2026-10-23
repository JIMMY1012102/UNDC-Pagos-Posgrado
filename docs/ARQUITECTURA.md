# Arquitectura modular — UNDC Pagos Posgrado

**Estado: propuesta estructurada, funcionalidades no implementadas.** Una sola aplicación Laravel, sin dividir en microservicios.

## Cuatro procesos documentados

| Proceso | Módulo | Alcance |
|---|---|---|
| Acceso y datos personales | Acceso | Acceso, cuenta, perfil comprador/beneficiario |
| Compra en la tienda | Tienda | Catálogo, carrito, datos, pedido pendiente |
| Pago mediante pasarela | Pagos | Intento, respuesta verificada, estados, reintentos |
| Consulta de Tesorería | Tesoreria | Consultas autorizadas, conciliación, reportes |

Recorrido: **Tienda → Acceso (si es necesario) → Tienda/pedido pendiente → Pagos**. Tesorería es un flujo independiente de personal autorizado.

## Estructura

```text
app/
  Modules/
    Acceso/{Http/Controllers,Services,routes.php}
    Tienda/{Http/Controllers,Models,Services,routes.php}
    Pagos/{Contracts,Http/Controllers,Services,routes.php}
    Tesoreria/{Http/Controllers,Queries,Services,routes.php}
  Integrations/{Sispagos,Payme,Interbank}
routes/web.php                  # Cargador de rutas modulares
resources/views/modules/       # Vistas por responsabilidad
tests/Feature/Modules/         # Verificaciones de arquitectura/flujo
```

Cada módulo registra sus propias rutas cuando tenga código real y pruebas. Laravel `App\\` ya mapea a `app/`; no se necesitan paquetes de modularización.

## Límites

- Acceso no cobra y el login de Google no prueba condición académica.
- Tienda valida catálogo, precios y totales **en el servidor**, no declara un cobro confirmado.
- Pagos registra intentos y confirma resultados solo tras validación confiable; no confundir pago confirmado con dinero ya conciliado.
- Tesorería no expone rutas públicas ni modifica tablas del sistema existente por defecto.
- Integrations encapsula infraestructura; Pay-me continúa **en evaluación** y SISPAGOS requiere contrato de datos validado.
- El modelo SQL preparado aparte no es automáticamente la base de esta aplicación.

## Flujo Git

Desarrollo local con Codex → rama → PR → CI → merge `main` → actualización manual de Oracle. No se configura despliegue por GitHub Actions.

## Seguridad

Demo en HTTP únicamente con datos ficticios. OAuth real, pagos reales, webhooks, secretos y consulta de base institucional requieren un entorno autorizado y seguro.
