# Registro y perfil del comprador

- `/tienda/registro`: datos personales, correo, contraseña y confirmación en un formulario. Cuenta y perfil se guardan en una transacción; no se conceden roles ni identidad académica verificada.
- `/tienda/login`: acceso mediante correo y contraseña. Regenera la sesión y vuelve al carrito si el perfil está completo.
- Google conserva su validación de proveedor y requisito de HTTPS/configuración. Tras el callback, abre `/tienda/perfil` si faltan datos; precarga los nombres que Google proporcione. El correo procede de la cuenta autenticada.
- El carrito anónimo conserva su token en sessionStorage y la selección referencial en localStorage. Se vincula con el flujo existente de precompra.
- Formularios con CSRF, validación del servidor y límites de intentos; las contraseñas se guardan mediante el cast hashed de User.
- No se incluyen recuperación de contraseña, verificación de correo ni vinculación automática de cuentas por correo. El acceso Google sigue deshabilitado sin configuración segura.

## Validación

Ejecutar `php artisan test`, incluyendo `AccountRegistrationTest`, en un entorno de pruebas con dependencias de desarrollo y base aislada. No ejecutar pruebas contra tesoreria_dev ni bases institucionales.
