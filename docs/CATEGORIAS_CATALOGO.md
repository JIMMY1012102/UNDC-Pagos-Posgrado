# Categorías predefinidas del catálogo

Las seis categorías son opciones organizativas propuestas para UNDC Market, no una subdivisión oficial del PDF. Ahora se guardan como registros en `tienda_categorias` antes de añadir conceptos. No se insertan conceptos ni se publican los 27 procedimientos.

| Código | Nombre |
|---|---|
| `admision` | Admisión |
| `matricula_pensiones` | Matrícula y pensiones |
| `tramites` | Trámites académicos |
| `certificados_constancias` | Certificados y constancias |
| `tesis_grados` | Tesis y grados |
| `servicios_administrativos` | Servicios administrativos |

Por decisión del proyecto, **6.22 Constancia de similitud de Posgrado (Turnitin)** se elegirá en **Certificados y constancias** al cargar ese concepto. El orden 6.22 del documento no se utiliza para asignar categorías automáticamente a un registro desconocido.

## Modelo y crecimiento

`tienda_categorias` tiene `id`, `legacy_office_id`, `codigo`, `nombre`, `orden` y fechas. El código es único dentro de cada oficina. Los nombres pueden repetirse en oficinas distintas.

`tienda_conceptos_catalogo.categoria_id` es una FK nullable hacia `tienda_categorias.id`. Una FK simple permite seleccionar valores relacionados en phpMyAdmin y una segunda FK compuesta obliga a que la categoría y el concepto publicado pertenezcan a la misma oficina. No se puede borrar una categoría utilizada. Los ID `legacy_office_id` siguen siendo INT firmados.

La consulta del catálogo usa el código y nombre de la categoría seleccionada. Si no hay `categoria_id`, conserva la lectura de `category` y sus códigos anteriores por compatibilidad. Cuando se edite en phpMyAdmin se debe usar **`categoria_id`**; el campo anterior no es el selector. El catálogo sigue sin habilitar cobros.

La migración y el seeder reconocen oficinas cuyo nombre contiene Posgrado o Postgrado, sin suponer un ID fijo ni crear oficinas. Crean las seis opciones para cada oficina de Posgrado existente y vinculan publicaciones que ya tienen uno de los seis códigos conocidos. Conservan los conceptos sin clasificar y sus campos. La carga es repetible, conserva las ediciones manuales de las categorías y no modifica `tm_*`.

Para futuras oficinas, se pueden crear sus propias categorías con su `legacy_office_id`, sin replicar conceptos maestros. El frontend actual reconoce los seis códigos anteriores; las categorías nuevas que no pertenezcan a ese vocabulario seguirán en Otros conceptos hasta ampliar su presentación.

## Actualizar Oracle

Como `undcdeploy`, después de integrar en main:

```bash
cd /var/www/undc-pagos-nuevo
git pull --ff-only origin main
php artisan migrate --force
php artisan optimize:clear
```

La migración agrega la tabla, las relaciones y las seis opciones si ya existe la oficina. Para repetir la carga o preparar opciones cuando la oficina se haya creado después:

```bash
php artisan db:seed --class=CategoriasPosgradoSeeder --force
```

Este seeder no crea usuarios de prueba ni conceptos. Si no encuentra la oficina por nombre lo informa y no inventa registros.

## Elegir en phpMyAdmin

1. En `tienda_categorias` → Examinar deben verse las seis opciones y la oficina real.
2. En `tienda_categorias` → Estructura → Vista de relaciones, elegir **`nombre`** como campo para mostrar, si la configuración de almacenamiento de phpMyAdmin ofrece esa opción. Esto añade el nombre legible a los valores relacionados.
3. En `tienda_conceptos_catalogo` → Insertar o Editar, utilizar **`categoria_id`**. La FK permite seleccionar los valores relacionados; según la configuración de phpMyAdmin se ofrece un desplegable o el enlace para examinar esos valores. Con seis filas, normalmente se muestra como desplegable.
4. Elegir la categoría de la misma oficina. Las opciones de otra oficina son rechazadas por la base de datos.

El desplegable es un control de phpMyAdmin, no un tipo de columna SQL. Su presentación depende de la configuración del servidor phpMyAdmin; la relación física queda creada por la migración. No se han cambiado las preferencias privadas de phpMyAdmin ni se ha construido un formulario de administración en la tienda.

Para comprobar registros y relación sin modificar nada:

```sql
SELECT id, legacy_office_id, codigo, nombre
FROM tienda_categorias
ORDER BY legacy_office_id, orden;

SELECT p.id, p.slug, p.legacy_office_id, p.categoria_id, c.nombre
FROM tienda_conceptos_catalogo AS p
LEFT JOIN tienda_categorias AS c ON c.id = p.categoria_id;
```

La reversión copia los códigos de las categorías seleccionadas al campo anterior antes de quitar las relaciones, la columna y la tabla. Las categorías personalizadas deben exportarse antes de revertir si se quieren conservar también sus nombres y orden.
