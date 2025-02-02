#!/usr/bin/env bash
set -e

APP_DIR="/home/ubuntu/web/imgdock"

if [ -d "$APP_DIR" ]; then
  cd "$APP_DIR"
  echo "[INFO] Stopping running containers..."
  sudo docker stop $(sudo docker ps -q) || true

  echo "[INFO] docker-compose down..."
  sudo docker compose -f compose-prod.yml down || true

  echo "[INFO] Removing unused images, containers, networks..."
  sudo docker system prune -af

  echo "[INFO] Removing unused volumes except 'https-portal'"
  for vol in $(sudo docker volume ls -q); do
    if [[ "$vol" != *"https-portal"* ]]; then
      sudo docker volume rm "$vol" || true
    fi
  done

  echo "[INFO] Deleting old code directory..."
  sudo rm -rf "$APP_DIR"
fi
