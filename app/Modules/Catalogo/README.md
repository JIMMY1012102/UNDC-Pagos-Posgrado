# Catálogo

Responsable de `tienda_categorias`, `tienda_configuracion_oficinas`, `tienda_conceptos_catalogo`, `tienda_versiones_tarifas`. Ofrece lectura de publicaciones y contrato `CatalogItemLookup` a Pedidos. El adaptador de SISPAGOS lee las oficinas y conceptos institucionales sin duplicarlos ni modificarlos. La existencia de `tm_conceptos.costo` no equivale a una tarifa de checkout aprobada.

Las categorías son registros por oficina; `tienda_conceptos_catalogo.categoria_id` permite seleccionarlas por FK. El campo de texto `category` queda como compatibilidad de publicaciones antiguas. [Uso y despliegue](../../../docs/CATEGORIAS_CATALOGO.md).
