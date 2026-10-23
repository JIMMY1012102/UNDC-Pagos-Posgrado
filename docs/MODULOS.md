# Hoja de ruta modular

Todo lo enumerado a continuación está **pendiente de implementación**, salvo el esqueleto y las pruebas iniciales de Laravel.

## M1 Acceso y perfil
- Definir login, sesión, cuenta, perfil comprador y beneficiario.
- Validar campos personales conforme a requisitos.
- Distinguir cuenta autenticada de identidad/documentación comprobada.
- Definir permisos para personal de Tesorería antes de publicar rutas privadas.

## M2 Tienda — primera prioridad funcional
- Catálogo público sin sesión.
- Detalle de conceptos, requisitos y precios institucionalmente aprobados.
- Carrito con agregar/quitar; botón «Pagar» solo con artículos.
- Preservar carrito al identificarse; confirmar beneficiario.
- Recalcular precios en servidor y crear pedido pendiente.

## M3 Pagos
- Intentos vinculados a pedido, estados pendiente/rechazado/confirmado/incierto.
- Adaptador de pasarela intercambiable para futuras decisiones.
- Confirmación del lado del servidor, trazabilidad e idempotencia.
- Manejar reintentos, mensajes duplicados y posibles dobles cargos.
- Pay-me sigue en evaluación: nada contratado o probado por UNDC.

## M4 Tesorería
- Consultas internas autorizadas.
- Filtros, reportes, consolidado y conciliación.
- Adaptación del avance SISPAGOS solo tras acordar esquema y validaciones.
- Mantener intactos los históricos y flujos bancarios vigentes.

## Orden KISS
1. Validar conceptos, roles y esquema de prueba.
2. Catálogo y carrito con datos ficticios.
3. Pedido pendiente y pruebas.
4. Cuenta/perfil (sin OAuth real en HTTP).
5. Simular proveedor de pagos con un adaptador falso para pruebas.
6. Consultas privadas de Tesorería sobre base de prueba.
7. Integración autorizada con servicios institucionales, cuando corresponda.

**Por definir:** tarifas oficiales de Posgrado, comprobación académica, contrato de datos SISPAGOS, proveedores habilitados, cobros inciertos y conciliación. Los S/50 y S/300 mencionados antes no son tarifas aprobadas.
