# Flujo de acceso y registro único — tienda UNDC

## Referencia funcional y alcance

El prototipo observado en SanMarket permite autenticar con una cuenta Google personal o institucional y **solicitar los datos personales solamente la primera vez**. Esta decisión se adopta como comportamiento objetivo de la tienda UNDC. No equivale a autorización institucional de precios, condiciones de pago ni acceso de funcionarios.

### Recorrido objetivo

1. El visitante navega el catálogo de Posgrado sin cuenta e incorpora conceptos al carrito anónimo.
2. Al intentar continuar a la etapa de confirmación/pago, la aplicación pide Google si no hay sesión; se aceptarán cuentas personales e institucionales tras configurar credenciales oficiales de OTI.
3. Después del callback, la sesión consulta `GET /api/v1/acceso/estado`.
4. Si `profile_complete=false`, la interfaz muestra una sola vez el formulario con nombres, apellidos, tipo/número de documento y contacto; guarda mediante `PUT /api/v1/acceso/perfil`.
5. Si `profile_complete=true`, **no vuelve a pedir el registro**: muestra los datos guardados para revisión opcional y continúa a Tienda. El usuario puede editar sus datos cuando lo desee, sin crear otra ficha.
6. El registro de datos no certifica identidad real ni condición académica; la validación del beneficiario, importe y requisitos siguen pendientes.
7. La pasarela, pedidos y checkout permanecen desactivados hasta aprobación institucional.

## Contrato de backend implementado

- `GET /api/v1/acceso/estado` requiere sesión autenticada, devuelve `authenticated=true`, `profile_complete`, `next_step` (`complete_profile` o `tienda`) y `profile` del usuario autenticado.
- `PUT /api/v1/acceso/perfil` crea o actualiza una única fila para `user_id` en `store_customer_profiles`.
- Se considera completo al contar con nombres, apellidos, tipo y número documental no vacíos. Correo de contacto y teléfono se almacenan cuando se suministran, pero no se usan como barrera porque no se definió obligatoriedad con Posgrado.
- Después del callback Google se redirige provisionalmente al inicio con `registration=pending|complete`; esto **no es todavía una pantalla real de onboarding**.
- El frontend debe consumir el estado JSON y decidir qué pantalla mostrar, evitando depender únicamente del parámetro URL.

## Límite de implementación

Este incremento **no implementa aún** recuperación del carrito anónimo después de autenticación ni la interfaz final de formulario; aún no debe prometer una continuidad completa de checkout. Se requiere un traspaso seguro de carrito asociado a usuario con token de un solo uso, reglas de concurrencia y pruebas, sin asumir tarifas. El flujo de pago real, emisión de comprobantes, conexión con SISPAGOS y validación académica continúan pendientes.

## Seguridad

No se aceptan `user_id` ni roles/privilegios desde JSON. Spatie conserva denegación por defecto para roles administrativos. Google OAuth no permite autovinculación por correo ni habilita perfiles de personal de Tesorería. Las credenciales oficiales siguen ausentes, `GOOGLE_LOGIN_ENABLED=false`, y ningún cambio fue desplegado en entornos institucionales.

## Verificación

`tests/Feature/Modules/BuyerOnboardingFlowTest.php` cubre primer registro, persistencia en nueva sesión, registros incompletos, usuarios separados y acceso anónimo.
