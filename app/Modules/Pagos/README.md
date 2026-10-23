# Pagos

**Estado: preparado para desarrollo, no implementado.**

## Responsabilidad
Iniciar intentos ligados a pedidos, mantener estados, validar confirmación del lado del servidor, gestionar incertidumbre, reintentos e incidencias.

## Restricción principal
No marcar pagado con una redirección; prevenir doble cobro y duplicación de notificaciones.

## Ubicaciones
- `routes.php`: rutas propias del módulo, todavía sin endpoints.
- `Contracts/`: código pendiente.
- `Http/Controllers/`: código pendiente.
- `Services/`: código pendiente.
- Vistas: `resources/views/modules/pagos/`.

Primero acordar requisitos, esquema, reglas de autorización y pruebas; después implementar controlador/servicio/ruta. La carpeta no acredita que la función exista.
