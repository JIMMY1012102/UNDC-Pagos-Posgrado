#!/usr/bin/env bash
set -Eeuo pipefail

app_dir=$1
revision=$2
archive=$3

[[ "$app_dir" = /* ]]
[[ "$revision" =~ ^[0-9a-f]{40}$ ]]
[[ "$archive" = /tmp/undc-build-*.tgz ]]

cd -- "$app_dir"
[[ -f .env && -d storage && -d vendor ]]

exec 9>storage/oracle-deploy.lock
flock -n 9 || { echo 'Otro despliegue está en ejecución'; exit 1; }

[[ -z "$(git status --porcelain --untracked-files=no)" ]]
[[ "$(git branch --show-current)" = main ]]

git fetch origin main
[[ "$(git rev-parse origin/main)" = "$revision" ]]
git merge-base --is-ancestor HEAD "$revision"

if ! git diff --quiet HEAD "$revision" -- database/migrations; then
  echo 'Hay migraciones nuevas: revisar y publicar manualmente.'
  exit 1
fi

command -v composer >/dev/null
command -v php >/dev/null
[[ -s "$archive" ]]

trap 'echo "Falló el despliegue. Revisar antes de quitar mantenimiento." >&2' ERR

php artisan down --retry=60
git merge --ff-only "$revision"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

mkdir -p public/build
tar -xzf "$archive" -C public/build

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up

rm -- "$archive"
echo "Publicado: $revision"
