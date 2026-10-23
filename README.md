# UNDC-Pagos-Posgrado

Tienda virtual de pagos para Posgrado de la Universidad Nacional de Cañete (UNDC), vinculada conceptualmente al trabajo de Tesorería/SISPAGOS.

**Estado:** Laravel 13 inicializado; estructura modular preparada; funcionalidades de negocio e integraciones aún pendientes. No confundir el avance de SISPAGOS compartido por Alfonso con esta nueva aplicación.

## Módulos de negocio

| Proceso | Carpeta | Responsabilidad |
|---|---|---|
| Acceso y datos personales | `app/Modules/Acceso` | Cuenta, perfil, datos de comprador/beneficiario |
| Compra en tienda | `app/Modules/Tienda` | Catálogo, carrito, precios, pedidos pendientes |
| Pago | `app/Modules/Pagos` | Intentos, verificación, estados, reintentos |
| Consulta Tesorería | `app/Modules/Tesoreria` | Consultas autorizadas, conciliación, reportes |

Los adaptadores externos están separados en `app/Integrations/` (Sispagos, Payme, Interbank). Sus carpetas no significan que estén implementados.

## Documentación

- [Arquitectura](docs/ARQUITECTURA.md)
- [Plan de módulos](docs/MODULOS.md)
- [Desarrollo local con Codex](docs/DESARROLLO_LOCAL.md)
- [Actualización manual de Oracle](docs/DESPLIEGUE.md)
- [Instrucciones a Codex](AGENTS.md)

## Flujo de trabajo

**Laptop con Codex → rama Git → push → Pull Request → merge a main → actualización manual de Oracle cuando se autorice.** El CI de GitHub solo ejecuta pruebas y compilación; **no despliega**.

Tecnología instalada: Laravel 13, PHP compatible con 8.3, Blade, Vite. Livewire, Spatie y demás componentes están previstos, no necesariamente instalados.

Seguridad: la demo alojada en HTTP es para **datos ficticios**. Nunca publicar datos reales, credenciales, Google OAuth real, SISPAGOS productivo o pasarelas de pago reales sin autorización y controles institucionales.
