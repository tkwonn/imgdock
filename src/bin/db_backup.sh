#!/usr/bin/env bash
set -e

echo "Script started at $(date)"
export TZ=America/Los_Angeles
ENV_FILE="/home/ubuntu/web/imgdock/.env"
if [ -f "$ENV_FILE" ]; then
  export $(cat "$ENV_FILE" | xargs)
else
  exit 1
fi

DATE=$(date +"%Y%m%d%H%M")

BACKUP_DIR="/home/ubuntu/web/imgdock/tmp"
sudo mkdir -p "$BACKUP_DIR"
sudo chown -R ubuntu:ubuntu "$BACKUP_DIR"
sudo chmod -R 755 "$BACKUP_DIR"
BACKUP_FILE="${BACKUP_DIR}/db_backup_${DATE}.sql"

mysqldump -h "$DATABASE_HOST" -u "$DATABASE_USER" -p"$DATABASE_USER_PASSWORD" \
  --single-transaction --set-gtid-purged=OFF "$DATABASE_NAME" \
  > "$BACKUP_FILE"

aws s3 cp "$BACKUP_FILE" "s3://$S3_BACKUP_BUCKET_NAME/db_backup_${DATE}.sql"

rm "$BACKUP_FILE"

OLDER_THAN=$(date -d "7 days ago" +%s)

aws s3 ls "s3://$S3_BACKUP_BUCKET_NAME" | grep "db_backup_" | while read -r line; do
  fileName=$(echo "$line" | awk '{print $4}')
  if [[ -n "$fileName" ]]; then
    createDate=$(echo "$line" | awk '{print $1" "$2}')
    createEpoch=$(date -d"$createDate" +%s)

    if (( createEpoch < OLDER_THAN )); then
      aws s3 rm "s3://$S3_BACKUP_BUCKET_NAME/$fileName"
    fi
  fi
done

echo "Script completed successfully"
exit 0
