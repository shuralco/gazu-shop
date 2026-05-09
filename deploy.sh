#!/bin/bash
# SimpleShop Deploy Script
# Usage: ./deploy.sh

set -e

echo "=== SimpleShop Deployment ==="

# Build and start container
echo "Building Docker image..."
docker compose build

echo "Starting container..."
docker compose up -d

# Wait for container to be ready
echo "Waiting for container..."
sleep 10

# Run Laravel optimization commands
echo "Running Laravel optimizations..."
docker exec lionex-simpleshop php artisan config:cache
docker exec lionex-simpleshop php artisan route:cache
docker exec lionex-simpleshop php artisan view:cache
docker exec lionex-simpleshop php artisan storage:link 2>/dev/null || true

echo ""
echo "=== Deployment Complete ==="
echo "Site: https://shop.textory.online"
echo "Admin: https://shop.textory.online/admin"
echo ""
echo "Check logs: docker logs -f lionex-simpleshop"
