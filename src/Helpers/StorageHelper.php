<?php

namespace Helpers;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use Psr\Http\Message\StreamInterface;

class StorageHelper
{
    private static ?S3Client $s3Client = null;

    // TODO: store it in the .env file
    private const BUCKET_NAME = 'images';

    // TODO: prepare for dev and prod environments
    private static function getS3Client(): S3Client
    {
        if (!self::$s3Client) {
            self::$s3Client = new S3Client([
                'version' => 'latest',
                'region' => 'us-east-1',
                'endpoint' => 'http://minio:9000',
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => Settings::env('MINIO_ROOT_USER'),
                    'secret' => Settings::env('MINIO_ROOT_PASSWORD'),
                ],
            ]);
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
                'Bucket' => self::BUCKET_NAME,
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
                'Bucket' => self::BUCKET_NAME,
                'Key' => $s3Key,
            ]);

            return $result['Body'];
        } catch (S3Exception $e) {
            throw new Exception('Failed to get object: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception If the file delete fails
     */
    public static function deleteObject(string $s3Key): void
    {
        try {
            $s3Client = self::getS3Client();

            $s3Client->deleteObject([
                'Bucket' => self::BUCKET_NAME,
                'Key' => $s3Key,
            ]);
        } catch (S3Exception $e) {
            throw new Exception('Failed to delete object from S3: ' . $e->getMessage());
        }
    }
}
