# Usuarios

Dueño de `users`, `tienda_perfiles_compradores`, `tienda_identidades_oauth`, `tienda_beneficiarios`. Cuenta, OAuth, perfil y beneficiarios. URLs existentes `/tienda/login` y `/api/v1/acceso/*` se conservan, aunque la lógica ahora vive en Usuarios. El cambio de nombre del módulo no modifica tablas, URLs ni vistas existentes.

No confundir compradores `users` con empleados históricos `tm_usuario`.
