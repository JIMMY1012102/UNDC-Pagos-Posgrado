# Bitácora de avances - UNDC MARKET

Fecha de documentación: **5 de noviembre de 2026**. Proyecto OTI - Universidad Nacional de Cañete.

Rama documentada: `feat/undc-market-avance-local`. Este registro resume el trabajo de la conversación y lo relaciona con los commits de la rama, hasta `ee7260d`.

## Cómo interpretar las fechas

- La tabla usa la **fecha del committer registrada por GitHub**, convertida a **America/Lima (UTC-05:00)**. No representa necesariamente el inicio del trabajo, el momento de la conversación, una prueba exitosa ni el despliegue en Oracle.
- Se conserva precisión de segundos porque está disponible en Git. Los cambios divididos en varios archivos pueden tener commits consecutivos.
- Las actividades realizadas fuera del repositorio no tienen una hora verificable en Git; se documentan sin inventarla.
- La fecha de este documento no implica que los cambios de noviembre 3 y 4 se hayan realizado nuevamente.
- No se incluyen credenciales, secretos OAuth, claves privadas ni datos de formularios personales.

## 1. Documentación y decisiones iniciales (sin hora de Git verificable)

1. Ubicar el documento correcto en OTI-UNDC y trabajar en Google Docs, evitando duplicados innecesarios.
2. Establecer 3.1 Arquitectura en versión **0.1 - Borrador** e identificar al Ing. Ricardo Carlos Inquilla Quispe como Jefe de OTI.
3. Preparar 3.2 Diseño de base de datos, 3.3 Mockups y 3.4 Conformidad de diseño junto a 3.1.
4. Completar el mínimo KISS de 3.2 con fuentes: gestor, versión, host, puerto, diccionario, relaciones, procedimientos/vistas y respaldo.
5. Definir Oracle como entorno temporal de pruebas y dejar el traslado a la infraestructura UNDC para una etapa posterior. La documentación no equivale a conformidad institucional.

## 2. Preparación del entorno (sin hora de Git verificable)

- El usuario confirmó MariaDB **10.11.14**, puerto **3306**, y la base `tesoreria_dev`.
- Se inspeccionaron tablas y migraciones. La carpeta `/var/www/undc-pagos` no contenía `.git`, por lo que no podía recibir un pull.
- Tras dificultades con SSH a GitHub, se utilizó HTTPS con acceso limitado al repositorio y se creó el clon `/var/www/undc-pagos-nuevo`.
- Se corrigió la configuración SQLite copiada inicialmente, se configuró MariaDB y se limpió la caché. El usuario mostró la migración de permisos terminada con DONE.
- Se trabajó con el usuario `undcdeploy` y rutas absolutas después de un error de permisos al cambiar de carpeta.
- Se revisó Nginx y se apuntó UNDC Pagos a la carpeta nueva. Los otros proyectos de la VM debían conservarse.
- La VM reportó aproximadamente 1 GB de RAM y 2 GB de swap, sin Node/npm.
- Se encontró en la laptop un avance local aún no publicado; se subió a la rama de esta bitácora.

## 3. Evolución de la tienda

### Identidad, imágenes y adaptación

Se cambió la marca a **UNDC MARKET**. Se corrigieron los fondos cuadriculados que simulaban transparencia, en imágenes de modo claro y oscuro. Hubo ajustes sucesivos de tamaño y posición, eliminación de formas decorativas y adaptación del encabezado y banners a celular y tablet.

### Accesibilidad

Se sustituyó el control aislado de modo oscuro por un panel con tamaño de texto, temas, lectura de selección/página, pausa, detención, voces disponibles, velocidad y restablecimiento. Después se ajustó su diseño y se añadió el desplegable al login. Se decidió conservar la voz del navegador, sin instalar servicios adicionales en Oracle. Su calidad depende del dispositivo y navegador.

### Catálogo, carrito e inicio de sesión

Se retiró el bloque público que solicitaba ID de oficina y la fila de encabezado de categorías indicada por el usuario. Se rediseñaron los estados vacío y con selección del carrito, con resumen e importes referenciales. Se incorporó una pantalla de login con mascota, Google a colores, título con icono de usuario y botón Regresar. Se realizaron ajustes de tamaños, espacios y posición según las capturas.

### Separación KISS de páginas

Se mantuvo una sola aplicación Laravel con layout y componentes compartidos, separando `/tienda`, `/tienda/carrito` y `/tienda/login`. Se conservó la selección al navegar. El carrito recibió un encabezado simplificado, la mascota con carrito y un resumen lateral.

### Cuenta y datos

Se implementaron registro con correo/contraseña, ingreso con contraseña y formulario de perfil: nombres, apellidos, tipo y número de documento, teléfono opcional y correo de cuenta. Google lleva al formulario si faltan datos y al carrito si el perfil está completo. Se añadieron validaciones, hash, CSRF, límites de intentos y regeneración de sesión. El login no acredita identidad académica.

### HTTPS y Google (configuración manual)

El **4 de noviembre de 2026**, según la conversación, el usuario ejecutó Certbot y recibió confirmación de HTTPS y renovación programada. Se configuró Google Auth Platform para UNDC MARKET con público externo en prueba, cliente OAuth web y callback `/auth/google/callback`. El usuario confirmó selector de cuentas y continuidad por el perfil al carrito. No hay hora exacta verificable en Git para esas acciones de consola. Un secreto expuesto accidentalmente se indicó reemplazar; no se almacena aquí.

### Nombre y navegación móvil

El encabezado pasó a mostrar el nombre de la cuenta autenticada. Se creó un panel lateral móvil con navegación, idioma, accesibilidad, cuenta y carrito; en la página del carrito se limitaron las opciones a esa sección. La cabecera móvil se compactó para mostrar solo marca y hamburguesa.

### Revisión antes del pago

Se añadió una pantalla posterior a **Realizar pago** con datos, conceptos y total referencial, y retorno para modificar la selección. No se habilitó pasarela, cobro real ni confirmación de pago.

### Búsqueda y sugerencias

Se amplió la búsqueda a todas las categorías, con coincidencias por nombre/código y normalización de tildes, mayúsculas y espacios. Enter o la flecha muestran resultados. Después se añadió un desplegable de hasta seis coincidencias con nombre/precio, selección mediante clic o flechas y Enter, y cierre con Escape o al salir del control.

### Cierre de sesión y errores corregidos

1. Se añadió una flecha junto al nombre para desplegar **Cerrar sesión**, también dentro del menú móvil.
2. El usuario detectó retorno a la portada Laravel: se cambió el destino a `/tienda`.
3. Regresar desde el perfil incompleto causaba un retorno al mismo formulario: se cambió a cancelación por POST con CSRF, cierre de sesión y destino fijo `/tienda/login`, sin guardar el formulario.
4. El usuario aportó el log **Route [login] not defined** al salir desde móvil. Se configuró `redirectGuestsTo` hacia `tienda.login` y se añadieron pruebas de logout sin sesión y API no autenticada. Se indicó actualizar Oracle y limpiar cachés. No consta confirmación posterior del resultado.

## 4. Registro cronológico de commits

Hora de Perú, UTC-05:00. Fuente: historial de commits leído de la rama. Se incluyen todos los 78 commits desde la publicación del avance local del 3 de noviembre hasta la última corrección del 4 de noviembre; no se mezclan los commits base de octubre con trabajo nuevo de este chat.

| Fecha y hora (Lima) | Cambio registrado | Evidencia |
| --- | --- | --- |
| 2026-11-03 14:52:34 | feat: actualizar interfaz de UNDC Market con avance local | [1dff423](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/1dff423de275e6553e9091dd60f0ac43532ea6bb) |
| 2026-11-03 15:03:41 | fix(ui): cambiar marca a UNDC MARKET | [ab70867](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/ab70867961291ecbcb2762c71b9ab41cef5b7cda) |
| 2026-11-03 15:04:10 | fix(asset): reemplazar cuadriculado por transparencia real en tramiteN | [d2c1741](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/d2c174142001f5b9fdbc692fceea572d1b22256f) |
| 2026-11-03 15:13:22 | fix(asset): transparencia real en imagen de tramites del modo claro | [016e5ca](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/016e5ca74c6ac27d0ff7c4ab441623f453e75aae) |
| 2026-11-03 15:15:13 | fix(ui): reducir imagen de tramites un 15% en ambos temas | [982658a](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/982658ace22c4a2bfe78a01599026241ed73fe88) |
| 2026-11-03 15:16:24 | fix(ui): reducir ligeramente imagen de tramites en ambos temas | [a4e4199](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/a4e41991ad3dca0f30f7d99a25055e23980e2859) |
| 2026-11-03 15:17:48 | fix(ui): subir imagen de tramites 20px en ambos temas | [b4e172a](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/b4e172a9fc521f8ea60c5eccc19958566d518e77) |
| 2026-11-03 15:20:13 | fix(ui): quitar formas decorativas detras de imagen de tramites | [6ae8909](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/6ae89096ed411ecf7ed12eb88f6731e90dede758) |
| 2026-11-03 15:21:41 | fix(ui): reducir imagen de catalogos al 80% en ambos modos | [27db06c](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/27db06c57fa287a9e135cf60021cdc69d49ed6fb) |
| 2026-11-03 15:24:16 | fix(ui): ajustar imagen de catalogos al 90% en ambos modos | [701530c](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/701530c4c7fc02012c938b3d148421ed6a5a4c51) |
| 2026-11-03 15:29:19 | fix(ui): adaptar encabezado y banners a celulares y tablets | [486fab2](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/486fab222e13e50ef136bd293c0f2f23aad45cc5) |
| 2026-11-03 15:57:13 | feat(accessibility): panel de texto, temas y lectura en voz alta | [d3a28a3](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/d3a28a3565c12de0ad452fecf16e36bb4e14cca4) |
| 2026-11-03 15:57:14 | feat(accessibility): panel de texto, temas y lectura en voz alta | [a74cad8](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/a74cad80e42d36e58b227680c4d68246762bda97) |
| 2026-11-03 15:57:16 | feat(accessibility): panel de texto, temas y lectura en voz alta | [bf10150](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/bf1015069e629ec9a1211675c22b097ddaceeb79) |
| 2026-11-03 16:02:37 | fix(ui): retirar bloque de consulta del catalogo institucional | [d6bc836](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/d6bc83637312a28a8d4dbc4a699541d0aa09f2bc) |
| 2026-11-03 16:02:38 | fix(ui): retirar bloque de consulta del catalogo institucional | [51a49c9](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/51a49c93bed3a8748de5014f5613efeb1a1a8f25) |
| 2026-11-03 16:09:21 | feat(ui): redisenar carrito y resumen de seleccion | [ce8a97a](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/ce8a97aec875ad5008f8fd52a464b1d795f705ac) |
| 2026-11-03 16:09:22 | feat(ui): redisenar carrito y resumen de seleccion | [e5fc550](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/e5fc550cd7521c4efff9fc14597e26f7b8fa5d60) |
| 2026-11-03 16:09:23 | feat(ui): redisenar carrito y resumen de seleccion | [d88793a](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/d88793a195a8854b12712b2afc4114b0a86c2720) |
| 2026-11-03 16:15:06 | feat(ui): carrito lleno con importes y resumen referencial | [ae28b36](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/ae28b36d90ad1fffd45a675293ae5d54e32a261a) |
| 2026-11-03 16:15:07 | feat(ui): carrito lleno con importes y resumen referencial | [cbb34f1](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/cbb34f1faaaa2d8b426ce57a4898cb60782ea6ca) |
| 2026-11-03 16:15:09 | feat(ui): carrito lleno con importes y resumen referencial | [6f3795e](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/6f3795e8b2eab2ba991052a58e19022e0007d9d7) |
| 2026-11-03 16:20:41 | feat(ui): pantalla de inicio de sesion UNDC Market con mascota | [ef90af6](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/ef90af665b84fdfeb6b33ed9cbd07d89b713c329) |
| 2026-11-03 16:22:39 | fix(ui): ocultar cabecera de tienda en inicio de sesion | [bb130ac](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/bb130ac18ab22fb84727090b8484a1083aa22eeb) |
| 2026-11-03 16:24:36 | fix(ui): subir 80px marca robot y beneficios del login | [cf2c32c](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/cf2c32c56a6ea436c9e5fc3d5d5609794e5452d2) |
| 2026-11-03 16:26:20 | fix(ui): reducir mascota del login al 90 por ciento | [281abb4](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/281abb49844c4828f5eaad531a3631b836caaaff) |
| 2026-11-03 16:27:44 | fix(ui): subir marca y mascota del login 15px adicionales | [216cc32](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/216cc323056a45a40bd6f3abad027b151defc6d1) |
| 2026-11-03 16:29:06 | fix(ui): subir beneficios del login 10px | [acba83f](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/acba83f3e7e12396098f62034db12402d9834751) |
| 2026-11-03 16:30:10 | fix(ui): subir beneficios del login otros 20px | [732e144](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/732e144d9778157a2b8754849a3135eb5ae9314e) |
| 2026-11-03 16:32:46 | fix(ui): compactar formulario y mostrar Google a color | [e036271](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/e03627108668e8ea8bbdbbe968fe057ed27863ab) |
| 2026-11-03 16:32:47 | fix(ui): compactar formulario y mostrar Google a color | [29b8ee9](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/29b8ee96380cafe4ac3b54fdaa8d723d70169d5d) |
| 2026-11-03 16:35:22 | fix(ui): alinear login sin recortes y ajustar distribucion responsive | [d61e147](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/d61e147ee08308660aeeeb6adad0c151bec06dac) |
| 2026-11-03 16:37:04 | fix(ui): quitar avisos de disponibilidad del formulario de ingreso | [22ef433](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/22ef4334319f9652c1516eebea50231ecbb196ed) |
| 2026-11-03 16:38:35 | fix(ui): quitar fila de encabezado de todas las categorias | [f8a19be](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/f8a19be9b119234819ec4b5108030234a8c1b5a6) |
| 2026-11-03 16:44:42 | feat(cart): pedir inicio de sesion y adaptar accion segun autenticacion | [00d445d](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/00d445d1d86b799b4422546a83b9032c956047da) |
| 2026-11-03 16:47:32 | fix(ui): mostrar boton Regresar en inicio de sesion | [0b6fa0c](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/0b6fa0ca5640ac219720ccca77f0334afff1c02d) |
| 2026-11-03 16:51:42 | feat(accessibility): redisenar panel compacto con estado y pausa de lectura | [5d59275](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/5d592755353457954ff806ba3586cab1afb9c7c7) |
| 2026-11-03 16:51:44 | feat(accessibility): redisenar panel compacto con estado y pausa de lectura | [7c5b5eb](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/7c5b5eb29b366d756dcb7a9155a925ff688f199f) |
| 2026-11-03 16:51:45 | feat(accessibility): redisenar panel compacto con estado y pausa de lectura | [3ba2b2b](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/3ba2b2b2f1360d676a9a2d8ee85ad1c8374460bc) |
| 2026-11-03 17:07:41 | feat(accessibility): boton desplegable en pantalla de inicio de sesion | [9f6829e](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/9f6829eaff756a1df0625221973b756f148d310b) |
| 2026-11-03 17:07:43 | feat(accessibility): boton desplegable en pantalla de inicio de sesion | [423eea3](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/423eea33be24ac37e9859c60a3a576cb9861a596) |
| 2026-11-03 17:09:02 | fix(ui): subir bloque izquierdo del login 20px sin recortar marca | [1abca64](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/1abca644ff44fcd5129103afa54c8690c98a78dc) |
| 2026-11-03 17:09:11 | fix(ui): conservar posicion del formulario al subir bloque izquierdo | [36aec0f](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/36aec0f9ebe31377a3f9386dbad4d5c1bfa6d1bd) |
| 2026-11-03 17:11:16 | fix(ui): subir bloque izquierdo y tarjeta de login 30px | [7a6ef93](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/7a6ef9307abb63a5aa8a83e069ae64cffcc02c4f) |
| 2026-11-03 17:14:04 | fix(ui): titulo de login centrado con icono de usuario | [0e3849d](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/0e3849da2ae50e4736fbbadd8398bdd68d567d95) |
| 2026-11-03 17:14:06 | fix(ui): colocar simbolo de usuario encima del titulo de ingreso | [6c6b8c0](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/6c6b8c0fa54564813b48c74b9e8e0a893c65c48c) |
| 2026-11-03 17:16:17 | fix(ui): quitar subtitulo del inicio de sesion | [a4e7539](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/a4e75393017fc9dc25f0eee64182b30b6867e5a5) |
| 2026-11-03 17:16:18 | fix(ui): bajar icono y titulo al espacio del subtitulo eliminado | [db6cf6e](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/db6cf6ea3248917bd22150089ae080b64d867ff3) |
| 2026-11-04 14:23:18 | fix(ui): subir contenido del formulario quitando espacio superior | [7e81c97](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/7e81c97288bac63e585fcc25b06c373774a71ace) |
| 2026-11-04 14:27:01 | fix(ui): mejorar apariencia y foco del boton Regresar | [2d5274c](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/2d5274c55e733894ea392172f29f363ce0cce21f) |
| 2026-11-04 14:39:53 | fix(ui): separar accesibilidad y ordenar tarjeta de inicio de sesion | [494a834](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/494a834e169d3a5133538deffb67ad0b49a5427f) |
| 2026-11-04 14:44:18 | style(login): subir tarjeta de ingreso 25px en escritorio | [bfbb975](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/bfbb975ebd999fcdf0b709a175161b13bd545d5a) |
| 2026-11-04 14:50:46 | refactor(market): separar tienda carrito y login con vistas y rutas compartidas | [041e355](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/041e355c96bb53e15f11914e78b12aaec86de5fd) |
| 2026-11-04 15:03:05 | style(cart): simplificar cabecera y agregar robot y resumen lateral | [afe8f33](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/afe8f33d4b23c556503e6d559752b10b7d5c95a7) |
| 2026-11-04 15:34:43 | feat(acceso): registro con perfil e ingreso con contraseña y completar datos tras Google | [7c60317](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/7c60317c9a6956a33d4b61b4673b7f46c25d7c85) |
| 2026-11-04 16:22:03 | fix(header): mostrar nombre de cuenta autenticada en escritorio y movil | [f8c2d03](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/f8c2d03508ddd9c71e6749d846a4554861bc574d) |
| 2026-11-04 16:30:19 | feat(mobile): menu lateral con idioma accesibilidad cuenta y carrito | [89f7f94](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/89f7f94a3a12f97c3599bcdc78d2057657241b91) |
| 2026-11-04 16:35:31 | style(cart): adaptar opciones del menu lateral al carrito | [6f7ff87](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/6f7ff87c5d274233b4aad8d9dfbf9db1ff7b6dcd) |
| 2026-11-04 16:35:32 | fix(cart): habilitar hamburguesa en celular | [101d5b3](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/101d5b36bc8cca15a90207d95d88abb0aafdfbb8) |
| 2026-11-04 16:40:05 | fix(mobile): cabecera solo marca y hamburguesa en una fila | [4af5101](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/4af51013fd112a455833ad54bb5c5ab3e538125c) |
| 2026-11-04 16:44:34 | feat(checkout): pantalla de revision del pago sin cobros | [cc475f6](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/cc475f69415a236d6eb9694e57394a09b335d352) |
| 2026-11-04 16:44:35 | feat(cart): incorporar revision de pago al recorrido | [6677758](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/6677758c04ba37254c6057e9495584ffbd46899f) |
| 2026-11-04 16:44:54 | feat(cart): abrir resumen referencial al pulsar Realizar pago | [2e04118](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/2e041186754a0865493a91cdea83f1d11de5d1d1) |
| 2026-11-04 16:58:02 | fix(search): buscar conceptos en todas las categorías y mostrar resultados | [a6176a0](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/a6176a0d3be962b222486419a94933a77a1e107c) |
| 2026-11-04 16:58:04 | test(search): verificar tildes, categorías, códigos y búsquedas vacías | [60a6051](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/60a6051309cac8be71d24c7d4a6220afbedf2939) |
| 2026-11-04 17:02:47 | feat(search): sugerencias de productos accesibles mientras se escribe | [13c5a9c](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/13c5a9c6b65a03084c793ae6990474455abb564d) |
| 2026-11-04 17:02:49 | feat(search): sugerencias de productos accesibles mientras se escribe | [0753bb4](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/0753bb468db95a39323c83f1c407c9e0b47bc86c) |
| 2026-11-04 17:02:50 | feat(search): sugerencias de productos accesibles mientras se escribe | [227800c](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/227800c822a8b825fa3b4770a6fe18cda44f24fb) |
| 2026-11-04 17:02:51 | feat(search): sugerencias de productos accesibles mientras se escribe | [23f262f](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/23f262f25f5521d24373db252c432b1a9f21bc9a) |
| 2026-11-04 17:07:29 | feat(account): desplegar cerrar sesión junto al nombre del usuario | [189fe39](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/189fe394f50115b5412b341b07dbf374a8794b7d) |
| 2026-11-04 17:07:31 | feat(account): desplegar cerrar sesión junto al nombre del usuario | [9469eea](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/9469eea60735177a38ce1dedbc88ea7e39d9750d) |
| 2026-11-04 17:07:32 | feat(account): desplegar cerrar sesión junto al nombre del usuario | [60db1ce](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/60db1ce048f9569a3163233cd9f192807a04ad1a) |
| 2026-11-04 17:09:15 | fix(auth): volver a la tienda al cerrar sesión | [81d5d26](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/81d5d268da4f66e23f9a5dbc6193d399a6e78f20) |
| 2026-11-04 17:13:18 | fix(auth): cancelar perfil sin guardar y regresar al inicio de sesión | [ff3231c](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/ff3231c6413673b39e0b9b1f8a3dc94d995d81f8) |
| 2026-11-04 17:13:20 | fix(auth): cancelar perfil sin guardar y regresar al inicio de sesión | [539b81e](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/539b81e9c7015932c4534a8b75bd9154518cc037) |
| 2026-11-04 17:13:21 | fix(auth): cancelar perfil sin guardar y regresar al inicio de sesión | [a273793](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/a27379397ebace6ca24ce2fcee570b97f96bb39c) |
| 2026-11-04 17:54:52 | fix(auth): dirigir sesiones ausentes al login de UNDC MARKET | [c504f0a](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/c504f0a5ec41b8bf9163f250e948ce31e61e540a) |
| 2026-11-04 17:54:53 | test(auth): cubrir logout sin sesión y API no autenticada | [ee7260d](https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado/commit/ee7260da59f29ffaaea0cb0f0e537efaec1c95bd) |

## 5. Validación y límites

- Confirmaciones de terminal del usuario: gestor/puerto de MariaDB, migración pendiente y emisión del certificado HTTPS.
- Confirmación funcional del usuario: selección de cuenta Google y continuación por datos personales.
- Se informó ejecución satisfactoria de **9 pruebas JavaScript** de navegación, persistencia de selección y búsqueda/sugerencias. No son pruebas de pasarela.
- Las pruebas PHP de cuenta, cancelación y sesión se agregaron, pero **no se ejecutaron en el entorno de asistencia por falta de PHP**.
- No consta validación visual automatizada integral en navegador para todos los dispositivos.
- No consta confirmación de despliegue exitoso de la última corrección del error 500.
- Los tests de integración deben utilizar una base aislada; no ejecutar pruebas destructivas contra `tesoreria_dev` ni bases institucionales.

## 6. Pendientes al cierre

- Comprobar en móvil cerrar sesión, sesión vencida y cancelación de perfil sin guardar.
- Revisar búsqueda, sugerencias, menú y carrito en modos claro/oscuro y distintos anchos.
- Validar tarifas institucionales, pedidos, pasarela, confirmación de cobro y conciliación antes de habilitar pagos.
- Completar recuperación de contraseña y verificación de correo.
- Definir traslado al servidor UNDC, respaldos y conformidades documentales.

## 7. Continuidad documental

El 5 de noviembre se preparó el PDF **Línea de tiempo UNDC MARKET**, con 34 hitos y 8 páginas, y se guardó en **OTI-UNDC / Fuentes**:
[Ver PDF en Drive](https://drive.google.com/file/d/16dlsYmUMgm8cw6YZ8oLOykfyankSW8MX/view).

Esta bitácora añade las horas verificables de Git; no modifica retroactivamente las fechas del PDF. Para próximos avances, registrar fecha/hora de Lima, descripción, commit y validación real, diferenciando implementación, prueba y despliegue.
