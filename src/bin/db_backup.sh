#!/usr/bin/env bash
set -e

export TZ=America/Los_Angeles
ENV_FILE="/home/ubuntu/web/imgdock/.env"
if [ -f "$ENV_FILE" ]; then
  export $(grep "$ENV_FILE" | xargs)
else
  exit 1
fi

DATE=$(date +"%Y%m%d%H%M")

S3_BUCKET=${S3_BUCKET_NAME}
DATABASE_HOST=${DATABASE_HOST}
DATABASE_NAME=${DATABASE_NAME}
DATABASE_USER=${DATABASE_USER}
DATABASE_USER_PASSWORD=${DATABASE_USER_PASSWORD}

BACKUP_DIR="/home/ubuntu/web/imgdock/tmp"
mkdir -p "$BACKUP_DIR"
BACKUP_FILE="${BACKUP_DIR}/db_backup_${DATE}.sql"

mysqldump -h "$DATABASE_HOST" -u "$DATABASE_USER" -p"$DATABASE_USER_PASSWORD" \
  --single-transaction --set-gtid-purged=OFF "$DATABASE_NAME" \
  > "$BACKUP_FILE"

aws s3 cp "$BACKUP_FILE" "s3://$S3_BUCKET/backup/db_backup_${DATE}.sql"

rm "$BACKUP_FILE"

OLDER_THAN=$(date -d "7 days ago" +%s)

aws s3 ls "s3://$S3_BUCKET/backup/" | grep "db_backup_" | while read -r line; do
  fileName=$(echo "$line" | awk '{print $4}')
  if [[ -n "$fileName" ]]; then
    createDate=$(echo "$line" | awk '{print $1" "$2}')
    createEpoch=$(date -d"$createDate" +%s)

    if (( createEpoch < OLDER_THAN )); then
      aws s3 rm "s3://$S3_BUCKET/backup/$fileName"
    fi
  fi
done

exit 0
