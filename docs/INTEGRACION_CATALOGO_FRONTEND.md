# Catálogo: conexión de tarjetas con la base de datos

## Comportamiento

La pantalla `/tienda` carga las oficinas disponibles mediante `GET /api/v1/tienda/catalogo/oficinas`. Solo se ofrecen oficinas habilitadas, activas y con publicaciones disponibles según la consulta de SISPAGOS de solo lectura.

Con una sola oficina se selecciona automáticamente y se oculta el selector para la etapa de Posgrado. Con varias, el selector vuelve a mostrarse y el comprador elige. Al cargar conceptos se omite el mensaje de éxito; se mantienen los estados de carga, vacío y error, con Reintentar solo ante errores. `?office_id=N` permite seleccionar una oficina de la lista; una selección anterior se conserva en `sessionStorage`. No se supone que la oficina 1 sea Posgrado.

La selección consulta `GET /api/v1/tienda/catalogo?office_id=N`. Las tarjetas muestran los nombres, descripciones, requisitos, códigos y oficina devueltos por la API. Las categorías conocidas son `admision`, `matricula_pensiones`, `certificados_constancias`, `tramites` , `tesis_grados` y `servicios_administrativos`; una categoría vacía o no reconocida aparece en Otros conceptos. No se clasifican conceptos por su precio ni se inventa su vinculación institucional.

El botón Agregar utiliza el identificador numérico real de la publicación y llama a `POST /api/v1/tienda/carritos/actual/items`. La clave del carrito se envía como Bearer; el frontend incluye CSRF en las operaciones. El backend mantiene sus validaciones y contratos existentes.

Se retiran los 29 productos referenciales escritos en JavaScript. Las selecciones antiguas de `undc_tusne_reference_cart` no se leen ni se convierten en compras. No se borran datos del servidor. Los estados vacío y error no usan un catálogo ficticio como respaldo. Hay botón de reintento.

## Archivos

| Archivo | Responsabilidad |
|---|---|
| `resources/views/modules/tienda/partials/catalog.blade.php` | Selector, estado accesible y contenedor de tarjetas. |
| `public/js/undc-tienda.js` | Carga de oficinas y conceptos, búsqueda, tarjetas y llamadas al carrito real. |
| `app/Modules/Catalogo/routes.php` | Direcciones públicas del catálogo. |
| `app/Modules/Catalogo/Http/Controllers/CatalogController.php` | Respuestas JSON. |
| `app/Modules/Catalogo/Services/CatalogService.php` | Presentación de oficinas y publicaciones. |
| `app/Modules/Sispagos/Queries/PublishedCatalogQuery.php` | Consulta de lectura a publicaciones y maestros existentes. |

## Límites

Los importes muestran Por confirmar. No se publica el costo histórico como tarifa pagable ni se habilitan cobros. Las respuestas mantienen `checkout_enabled: false`. La edición de carritos ya vinculados sigue siendo una función futura y la interfaz lo indica. Esta conexión no crea pedidos definitivos ni procesa pagos.

No requiere migraciones ni modifica tablas `tm_*`. El cambio debe revisarse y desplegarse en el servidor para que se vea; un commit en GitHub no actualiza la instancia automáticamente.

## Comprobación manual en desarrollo

1. Abrir `/tienda` y seleccionar una oficina disponible.
2. Comparar los nombres de las tarjetas con `/api/v1/tienda/catalogo?office_id=N`.
3. Si el único registro publicado es `pago-demo-oti`, debe aparecer el nombre que devuelve la API, no los tres conceptos de admisión anteriores.
4. En una sesión con carrito anónimo, agregar un concepto y comprobar su fila en `tienda_detalles_carrito`, vinculada a `tienda_carritos` y a la publicación seleccionada.
5. Abrir el carrito, recargar y retirar el elemento; confirmar el cambio en la base de desarrollo.
6. Continuar con login y perfil para revisar la precompra; comprobar que no se habilitan pagos ni se crean transacciones de cobro.

## Validación del cambio

Se ejecutaron `node --check public/js/undc-tienda.js` y las 12 pruebas de `node --test tests/frontend/market-pages.cjs`, incluyendo carga desde API, ID numérico y Bearer al agregar, eliminación, catálogo vacío, error/reintento visible, selección explícita de oficina, escape de HTML y regresiones de login/búsqueda.

Se añadieron pruebas PHP de filtrado de oficinas y categoría de publicación en `tests/Feature/Modules/CatalogApiTest.php`. No se ejecutaron aquí porque el entorno no dispone de PHP ni Composer. La suite Laravel y la prueba de navegador contra el servidor quedan pendientes de ejecución antes de integrar/desplegar.

## Navegación de Posgrado

La barra superior y el menú móvil ofrecen Para ti y seis categorías: Admisión (`admision`), Matrícula y pensiones (`matricula_pensiones`), Trámites académicos (`tramites`), Certificados y constancias (`certificados_constancias`), Tesis y grados (`tesis_grados`) y Servicios administrativos (`servicios_administrativos`). Se conservan los ID de las publicaciones; los códigos de categoría se traducen al español. Seleccionar una categoría filtra los datos de la API; una categoría sin publicaciones muestra el estado vacío. Para ti restablece todos los conceptos y la portada. Una lupa junto a Servicios administrativos abre el buscador bajo las categorías y enfoca el campo. La búsqueda abarca todo el catálogo. Pulsar otra vez la lupa o Escape cierra el campo y devuelve el foco a la lupa. Funciona en escritorio y celular; los enlaces con `?q=` abren el buscador con la consulta. No se crean conceptos ni se cambia su agrupación; una migración traduce los códigos existentes.


## Actualización de categorías al español

La traducción inicial conservó `tienda_conceptos_catalogo.category`. La evolución posterior agrega `tienda_categorias` y `categoria_id` para seleccionar registros predefinidos; [ver guía](./CATEGORIAS_CATALOGO.md).

| Código anterior | Código actual |
|---|---|
| `admission` | `admision` |
| `tuition` | `matricula_pensiones` |
| `transfers` | `tramites` |
| `certificates` | `certificados_constancias` |
| `degrees` | `tesis_grados` |
| `administrative` | `servicios_administrativos` |

La migración `2026_11_10_000001_translate_catalog_categories_to_spanish.php` actualiza únicamente los seis valores conocidos en todas las oficinas. Conserva categorías personalizadas, valores vacíos, ID, relaciones y publicación. No asigna categorías a conceptos sin clasificar ni inserta los 27 conceptos del PDF. `down()` devuelve los seis códigos al inglés, incluyendo publicaciones creadas después de migrar.

La API normaliza valores antiguos durante la transición y el frontend acepta enlaces anteriores. Los enlaces nuevos usan español. Los textos de la interfaz y su traducción al inglés se conservan.

En Oracle, después de descargar el código y revisar las migraciones pendientes:

```bash
git pull --ff-only origin main
php artisan migrate:status
php artisan migrate --force
php artisan view:clear
```

`git pull` solo descarga código; `migrate` aplica la traducción en la base configurada. El script público lleva una versión basada en su fecha de modificación para renovar la caché del navegador.
