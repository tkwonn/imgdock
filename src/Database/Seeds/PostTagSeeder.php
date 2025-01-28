<?php

namespace Database\Seeds;

use Database\AbstractSeeder;

class PostTagSeeder extends AbstractSeeder
{
    protected ?string $tableName = 'post_tag';

    private const POSTS_PER_TAG = 4;
    private const TAGS = [
        'Anime', 'Aww', 'Car', 'Cat', 'Coffee', 'Dog', 'Drawing', 'Exhibition',
        'Fanart', 'Fashion', 'Food', 'Funny', 'Health', 'Nature', 'STEM', 'Travel',
    ];

    protected array $tableColumns = [
        ['data_type' => 'int', 'column_name' => 'post_id'],
        ['data_type' => 'int', 'column_name' => 'tag_id'],
    ];

    public function createRowData(): array
    {
        $rows = [];
        $currentPostId = 1;

        foreach (self::TAGS as $index => $tag) {
            $tagId = $index + 1;

            for ($i = 0; $i < self::POSTS_PER_TAG; $i++) {
                $rows[] = [
                    $currentPostId++,  // post_id
                    $tagId,             // tag_id
                ];
            }
        }

        return $rows;
    }
}
