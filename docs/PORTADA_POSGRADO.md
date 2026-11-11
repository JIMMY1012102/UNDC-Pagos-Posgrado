# Portada de Posgrado

Para ti usa la fotografía panorámica aportada por el usuario, optimizada a WebP, con texto HTML y degradado CSS independientes. El entorno ampliado de la foto procede de una edición con IA; no es un registro documental exacto del campus.

En escritorio, la imagen llena el banner con `object-fit: cover`; puede recortarse el entorno. Hasta 900 px se presenta completa, proporcionalmente, y el contenido pasa debajo para conservar al grupo. No se fija una altura al texto.

El carrusel presenta tres mensajes generales sobre servicios existentes, sin fechas ni anuncios oficiales inventados. Avanza cada siete segundos; dispone de flechas, selección directa y pausa. Detiene el avance con foco de teclado, movimiento reducido, pestaña inactiva o sección oculta. Con JavaScript deshabilitado permanece visible el primer mensaje y se ocultan los controles.

Validación local: 32 pruebas de frontend aprobadas; sintaxis JavaScript y `git diff --check` correctos. PHP y dependencias Vite no están disponibles en el entorno local; Laravel, compilación y pruebas de navegador quedan para CI. Se agregan comprobaciones del carrusel y de proporción de foto móvil a la prueba de navegador existente.
