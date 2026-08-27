#!/usr/bin/env bash
set -e

cd "$(dirname "$0")/.."

echo "Construction et démarrage des conteneurs (mysql, api-go, front-laravel)..."
docker compose up --build -d

echo ""
echo "C'est prêt :"
echo "  - Back-office Laravel : http://localhost:8000/commercants"
echo "  - API Go              : http://localhost:8080/api/commercants"
echo ""
echo "Pour voir les logs : docker compose logs -f"
echo "Pour arrêter        : docker compose down"
