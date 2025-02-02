<?php

namespace Helpers;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use Psr\Http\Message\StreamInterface;

class StorageHelper
{
    private static ?S3Client $s3Client = null;

    private const RELEASE_STAGE_LOCAL = 'local';

    private static function getS3Client(): S3Client
    {
        if (!self::$s3Client) {
            $config = [
                'version' => 'latest',
                'region' => Settings::env('AWS_REGION'),
            ];

            if (Settings::env('APP_ENV') === self::RELEASE_STAGE_LOCAL) {
                $config = array_merge($config, [
                    'endpoint' => Settings::env('MINIO_ENDPOINT'),
                    'use_path_style_endpoint' => true,
                    'credentials' => [
                        'key' => Settings::env('MINIO_ROOT_USER'),
                        'secret' => Settings::env('MINIO_ROOT_PASSWORD'),
                    ],
                ]);
            }

            // No explicit credentials are needed for the production environment since the EC2 instance has an IAM role
            self::$s3Client = new S3Client($config);
        }

        return self::$s3Client;
    }

    /**
     * @throws Exception If the file upload fails
     */
    public static function putObject(array $file, string $uniqueString, string $extension, string $prefix = ''): string
    {
        try {
            $s3Client = self::getS3Client();

            if ($prefix === '') {
                $year = date('Y');
                $month = date('m');
                $s3Key = "$year/$month/$uniqueString.$extension";
            } else {
                $s3Key = "$prefix/$uniqueString.$extension";
            }

            $s3Client->putObject([
                'Bucket' => Settings::env('S3_BUCKET_NAME'),
                'Key' => $s3Key,
                'Body' => fopen($file['tmp_name'], 'rb'), // read binary
            ]);

            return $s3Key;
        } catch (S3Exception $e) {
            throw new Exception('Failed to upload file: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception If the file download fails
     */
    public static function getObject(string $s3Key): StreamInterface
    {
        try {
            $s3Client = self::getS3Client();

            $result = $s3Client->getObject([
                'Bucket' => Settings::env('S3_BUCKET_NAME'),
                'Key' => $s3Key,
            ]);

            return $result['Body'];
        } catch (S3Exception $e) {
            throw new Exception('Failed to get object: ' . $e->getMessage());
        }
    }

    public static function getCdnImageUrl(string $s3Key): string
    {
        $cloudfrontDomain = Settings::env('CLOUDFRONT_DOMAIN');

        return "https://$cloudfrontDomain/$s3Key";
    }

    /**
     * @throws Exception If the file delete fails
     */
    public static function deleteObject(string $s3Key): void
    {
        try {
            $s3Client = self::getS3Client();

            $s3Client->deleteObject([
                'Bucket' => Settings::env('S3_BUCKET_NAME'),
                'Key' => $s3Key,
            ]);
        } catch (S3Exception $e) {
            throw new Exception('Failed to delete object from S3: ' . $e->getMessage());
        }
    }
}
