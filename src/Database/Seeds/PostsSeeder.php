<?php

namespace Database\Seeds;

use Carbon\Carbon;
use Database\AbstractSeeder;
use Faker\Factory;
use Helpers\StorageHelper;
use Helpers\UrlSafeString;

class PostsSeeder extends AbstractSeeder
{
    protected ?string $tableName = 'posts';

    private const TEST_IMAGES_DIR = __DIR__ . '/../../../tmp/images';
    private const POSTS_PER_TAG = 4;
    private const TAGS = [
        'Anime', 'Aww', 'Car', 'Cat', 'Coffee', 'Dog', 'Drawing', 'Exhibition',
        'Fanart', 'Fashion', 'Food', 'Funny', 'Health', 'Nature', 'STEM', 'Travel',
    ];

    protected array $tableColumns = [
        ['data_type' => 'string', 'column_name' => 'title'],
        ['data_type' => 'string', 'column_name' => 'description'],
        ['data_type' => 'string', 'column_name' => 'extension'],
        ['data_type' => 'int', 'column_name' => 'size'],
        ['data_type' => 'string', 'column_name' => 's3_key'],
        ['data_type' => 'string', 'column_name' => 'delete_key'],
        ['data_type' => 'int', 'column_name' => 'view_count'],
        ['data_type' => 'string', 'column_name' => 'created_at'],
    ];

    public function createRowData(): array
    {
        $faker = Factory::create();
        $currentTime = Carbon::now();
        $startOfDay = Carbon::now()->startOfDay();
        $rows = [];

        $testImages = glob(self::TEST_IMAGES_DIR . '/*.*');
        if (empty($testImages)) {
            throw new \RuntimeException(
                'No test images found in ' . self::TEST_IMAGES_DIR
            );
        }

        foreach (self::TAGS as $tag) {
            for ($i = 1; $i <= self::POSTS_PER_TAG; $i++) {
                $fileName = "{$tag}{$i}";
                $imagePath = glob(self::TEST_IMAGES_DIR . "/{$fileName}.*")[0];

                $file = [
                    'tmp_name' => $imagePath,
                    'name' => basename($imagePath),
                    'size' => filesize($imagePath),
                    'error' => UPLOAD_ERR_OK,
                ];

                $uniqueString = UrlSafeString::random(8);
                $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                $s3Key = StorageHelper::putObject($file, $uniqueString, $extension);

                $rows[] = [
                    $faker->sentence(3),
                    $faker->optional(0.7, '')->sentence(2),
                    $extension,
                    $file['size'],
                    $s3Key,
                    UrlSafeString::random(16),
                    $faker->numberBetween(0, 50),
                    $faker->dateTimeBetween($startOfDay, $currentTime)->format('Y-m-d H:i:s'),
                ];
            }
        }

        return $rows;
    }
}
