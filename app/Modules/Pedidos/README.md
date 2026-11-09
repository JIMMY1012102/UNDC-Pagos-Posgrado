# Pedidos

Responsable de `tienda_carritos`, `tienda_detalles_carrito`, `tienda_pedidos`, `tienda_detalles_pedido`, `tienda_historial_estados_pedido`. Precompra/carrito reales. Usa el contrato `Catalogo/Contracts/CatalogItemLookup`, sin SQL directo a `tm_*`. Aún no implementa la creación de órdenes ni los pagos reales. Las vistas antiguas `modules.tienda.*` siguen como interfaz compatible, no como módulo de negocio.
