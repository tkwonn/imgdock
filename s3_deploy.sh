#!/bin/bash

# プロファイル名を設定
AWS_PROFILE="tkwon"

# バケット名と assets ディレクトリパスを設定
BUCKET="imgdock-prod-storage"
ASSETS_DIR="public/assets"
S3_ASSETS_PATH="assets"

# セッションの確認
echo "Checking AWS credentials..."
if ! aws sts get-caller-identity --profile ${AWS_PROFILE} > /dev/null 2>&1; then
    echo "Error: Unable to validate AWS credentials. Please check your AWS configuration."
    exit 1
fi

# ビルド
echo "Building assets..."
npm run build

# assets ディレクトリの内容を同期
echo "Syncing assets to S3..."
aws s3 sync ${ASSETS_DIR}/js "s3://${BUCKET}/${S3_ASSETS_PATH}/js" --profile ${AWS_PROFILE}
aws s3 sync ${ASSETS_DIR}/css "s3://${BUCKET}/${S3_ASSETS_PATH}/css" --profile ${AWS_PROFILE}
aws s3 sync ${ASSETS_DIR}/fonts "s3://${BUCKET}/${S3_ASSETS_PATH}/fonts" --profile ${AWS_PROFILE}

# CloudFrontのキャッシュを無効化
echo "Invalidating CloudFront cache..."
aws cloudfront create-invalidation \
    --distribution-id E102X0WSNWYNOP \
    --paths "/assets/*" \
    --profile ${AWS_PROFILE}

echo "Deployment completed!"