#!/usr/bin/env bash
set -e

APP_DIR="/home/ubuntu/web/imgdock"

cd "$APP_DIR" || exit 1

touch .env

cat << EOF >> .env
S3_BUCKET_NAME=${S3_BUCKET_NAME}
AWS_REGION=${AWS_REGION}
DB_HOST=${DB_HOST}
DB_USER=${DB_USER}
DB_PASSWORD=${DB_PASSWORD}
DB_NAME=${DB_NAME}
EOF

echo "[INFO] .env has been created."
