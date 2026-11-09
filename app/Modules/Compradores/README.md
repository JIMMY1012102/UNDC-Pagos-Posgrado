# Compradores

Dueño de `users`, `tienda_perfiles_compradores`, `tienda_identidades_oauth`, `tienda_beneficiarios`. Cuenta, OAuth, perfil y beneficiarios. URLs existentes `/tienda/login` y `/api/v1/acceso/*` se conservan, aunque la lógica ahora vive en Compradores. No confundir compradores `users` con empleados históricos `tm_usuario`.

## Flujo documentado

[Diagramas de registro, login, Google y perfil, con rutas de archivos y tablas](../../../docs/FLUJO_MODULO_COMPRADORES.md).

Registro, login y perfil probados como usuario según lo reportado por el responsable del proyecto el 9 de noviembre de 2026. Beneficiarios conserva su estructura de datos, pero no se declara como flujo funcional implementado.
