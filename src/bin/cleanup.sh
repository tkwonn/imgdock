#!/usr/bin/env bash
set -e

APP_DIR="/home/ubuntu/web/imgdock"

cd "$APP_DIR"
echo "[INFO] Attempting to stop running containers..."
CONTAINERS=$(sudo docker ps -q)
if [ -n "$CONTAINERS" ]; then
  sudo docker stop $CONTAINERS
else
  echo "[INFO] No running containers."
fi

echo "[INFO] docker-compose down..."
sudo docker-compose -f compose-prod.yml down || true

echo "[INFO] Removing unused images, containers, networks..."
sudo docker system prune -af

echo "[INFO] Removing unused volumes except 'https-portal'"
volumes_to_delete=$(docker volume ls -q | grep -v 'imgdock_https-portal-data')
for volume in $volumes_to_delete; do
  sudo docker volume rm "$volume"
done

echo "[INFO] Deleting old code directory..."
sudo rm -rf "$APP_DIR"
