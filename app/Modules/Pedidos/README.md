# Pedidos

Responsable de `store_carts`, `store_cart_items`, `store_orders`, `store_order_items`, `store_order_status_history`. Precompra/carrito reales. Usa el contrato `Catalogo/Contracts/CatalogItemLookup`, sin SQL directo a `tm_*`. Aún no implementa la creación de órdenes ni los pagos reales. Las vistas antiguas `modules.tienda.*` siguen como interfaz compatible, no como módulo de negocio.
