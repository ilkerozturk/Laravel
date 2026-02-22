#!/usr/bin/env bash
set -euo pipefail

if ! command -v composer >/dev/null 2>&1; then
  echo "composer bulunamadi. Once composer kurun." >&2
  exit 1
fi

if [ ! -f artisan ]; then
  composer create-project laravel/laravel .
fi

if [ -f .env.example ] && [ ! -f .env ]; then
  cp .env.example .env
fi

php artisan key:generate --force
php artisan migrate --force || true

echo "Laravel bootstrap tamamlandi."
