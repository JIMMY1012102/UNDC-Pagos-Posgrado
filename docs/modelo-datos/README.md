# Modelo relacional de UNDC Market

**Actualización conceptual:** 44 tablas en siete módulos reales de Laravel. [Ver el mapa general coloreado](./00_MAPA_GENERAL_43_TABLAS.md).

| N.° | Módulo | Tablas | Diagrama ER |
|---:|---|---:|---|
| 1 | SISPAGOS (legado) | 6 | [Abrir módulo](./01_SISPAGOS.md) |
| 2 | Catálogo | 4 | [Abrir módulo](./02_CATALOGO.md) |
| 3 | Usuarios | 4 | [Abrir módulo](./03_USUARIOS.md) |
| 4 | Pedidos | 5 | [Abrir módulo](./04_PEDIDOS.md) |
| 5 | Pagos | 6 | [Abrir módulo](./05_PAGOS.md) |
| 6 | Conciliación y Tesorería | 6 | [Abrir módulo](./06_TESORERIA.md) |
| 7 | Seguridad y Laravel | 13 | [Abrir módulo](./07_SEGURIDAD.md) |

**Importante:** las seis `tm_*` y las tablas de soporte Laravel/Spatie se conservan. Las 23 tablas propias pasan de `store_*` a `tienda_*` mediante una migración incremental; las columnas, datos y relaciones se conservan. En MariaDB `SCHEMA` y `DATABASE` son equivalentes: estos módulos son límites de dominio y código, no subesquemas independientes.


[Equivalencias inglés → español](../NOMBRES_TABLAS_ESPANOL.md).
