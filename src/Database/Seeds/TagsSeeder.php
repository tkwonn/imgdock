<?php

namespace Database\Seeds;

use Carbon\Carbon;
use Database\AbstractSeeder;
use Helpers\StorageHelper;
use Helpers\UrlSafeString;

class TagsSeeder extends AbstractSeeder
{
    protected ?string $tableName = 'tags';

    private const TEST_TAGS_DIR = __DIR__ . '/../../../tmp/tags';
    private const TAG_DESCRIPTIONS = [
        'Anime' => 'Japanese animation and manga culture',
        'Aww' => 'The cutest and most adorable things on the internet',
        'Car' => 'Vroom vroom!',
        'Cat' => 'Meow meow!',
        'Coffee' => 'The magical potion that turns "leave me alone" into "good morning"',
        'Dog' => 'The best animal ever, the dog!',
        'Drawing' => 'Hand-drawn art, sketches, and illustrations',
        'Exhibition' => 'Where you pretend to understand modern art while nodding thoughtfully',
        'Fanart' => 'Fan-made artwork',
        'Fashion' => 'Show off your style',
        'Food' => 'Warning: Do not browse while hungry',
        'Funny' => 'The funniest content on the internet',
        'Health' => 'Health, fitness, and wellness',
        'Nature' => 'Earth\'s beauty captured - landscapes, wildlife, and natural phenomena',
        'STEM' => 'Science, Technology, Engineering, and Mathematics',
        'Travel' => 'Explore the world through stunning photos and travel experiences',
    ];

    protected array $tableColumns = [
        ['data_type' => 'string', 'column_name' => 'name'],
        ['data_type' => 'string', 'column_name' => 'description'],
        ['data_type' => 'string', 'column_name' => 's3_key'],
        ['data_type' => 'string', 'column_name' => 'created_at'],
    ];

    public function createRowData(): array
    {
        $rows = [];

        $testImages = glob(self::TEST_TAGS_DIR . '/*.*');
        if (empty($testImages)) {
            throw new \RuntimeException(
                'No test tag images found in ' . self::TEST_TAGS_DIR
            );
        }

        foreach ($testImages as $imagePath) {
            $uniqueString = UrlSafeString::random(8);
            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            $fileName = pathinfo($imagePath, PATHINFO_FILENAME);

            $file = [
                'tmp_name' => $imagePath,
                'name' => basename($imagePath),
                'size' => filesize($imagePath),
                'error' => UPLOAD_ERR_OK,
            ];

            $s3Key = StorageHelper::putObject($file, $uniqueString, $extension, 'tags');

            $rows[] = [
                $fileName,
                self::TAG_DESCRIPTIONS[$fileName],
                $s3Key,
                Carbon::now()->format('Y-m-d H:i:s'),
            ];
        }

        return $rows;
    }
}
