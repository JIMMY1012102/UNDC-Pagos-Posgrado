# UNDC-Pagos-Posgrado

Proyecto de desarrollo para la tienda virtual de pagos de Posgrado de la Universidad Nacional de Cañete (UNDC), articulada con el sistema de Tesorería SISPAGOS.

## Tecnologías previstas

- PHP 8.3 y Laravel
- Blade, Livewire, Tailwind CSS y Alpine.js
- Spatie Laravel Permission para roles y permisos
- MariaDB/MySQL
- Nginx y PHP-FPM en el entorno de demostración
- GitHub Actions para pruebas y despliegue a un entorno de demostración

## Alcance inicial

1. Portal del pagador: autenticación, catálogo, pedidos y consulta de deudas.
2. Gestión de cobros: integración de pasarela y continuidad del canal Interbank.
3. Tesorería: consultas, reportes, conciliación y comprobantes según reglas institucionales.
4. Soporte a gestión contable: información para los procedimientos autorizados.

## Seguridad y entornos

Este repositorio es privado. No almacenar contraseñas, claves SSH, tokens, archivos `.env` ni información personal real de estudiantes. La instancia de demostración utilizará datos ficticios o anonimizados; la operación con pagos reales requiere autorización y controles institucionales.

## Estado

Repositorio inicializado. Pendiente incorporar el proyecto Laravel, automatización de pruebas y pipeline de despliegue.
