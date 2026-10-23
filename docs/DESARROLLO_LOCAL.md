# Desarrollo local con Codex y Git

Decisión vigente: trabajar en laptop Linux; **no Codespaces ni CI/CD de despliegue**.

## Clonar (una vez)

```bash
mkdir -p ~/Proyectos
cd ~/Proyectos
git clone https://github.com/JIMMY1012102/UNDC-Pagos-Posgrado.git
cd UNDC-Pagos-Posgrado
```

Requiere acceso al repositorio privado. Comprobar PHP 8.3, Composer y Node/npm.

## Preparación local

```bash
composer install
cp .env.example .env
php artisan key:generate
npm ci
php artisan test
php artisan serve
```

Usar solo DB local con datos ficticios. No ejecutar migraciones de producción ni importar dumps institucionales. Para estilos/scripts, ejecutar `npm run dev` en otra terminal.

## Trabajo por ramas

```bash
git switch main
git pull --ff-only origin main
git switch -c feat/tienda-catalogo
# trabajar con Codex y probar
php artisan test
git add .
git commit -m "feat(tienda): preparar catalogo"
git push -u origin feat/tienda-catalogo
```

Crear Pull Request a `main`; revisar tests y hacer merge. Una rama por tarea. No confundir el mensaje de ejemplo con una función existente.

Antes de pedir cambios a Codex, leer `AGENTS.md` y `docs/ARQUITECTURA.md`. No subir secretos, credenciales ni `.env`.
