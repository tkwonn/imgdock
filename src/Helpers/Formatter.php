<?php

namespace Helpers;

use Carbon\Carbon;

class Formatter
{
    private const BYTE_UNITS = ['B', 'KiB', 'MiB', 'GiB'];
    private const TIMEZONE = 'America/Los_Angeles';

    public static function formatPosts(array $posts): array
    {
        foreach ($posts as &$post) {
            $post['created_at'] = self::toRelativeTime($post['created_at']);
            $post['size'] = self::bytesToHumanReadable($post['size']);
            $urlParts = self::getPostUrl($post['s3_key']);
            $post['post_url'] = "/{$urlParts['extension']}/{$urlParts['unique_string']}";
        }
        unset($post);

        return $posts;
    }

    public static function bytesToHumanReadable(int $bytes): string
    {
        $exp = floor(log($bytes, 1024));
        $exp = min($exp, count(self::BYTE_UNITS) - 1);

        $size = $bytes / pow(1024, $exp);

        return sprintf('%.1f %s', $size, self::BYTE_UNITS[$exp]);
    }

    public static function toRelativeTime(string $datetime): string
    {
        return Carbon::parse($datetime)
            ->setTimezone(self::TIMEZONE)
            ->diffForHumans();
    }

    private static function getPostUrl(string $s3Key): array
    {
        $pathInfo = pathinfo($s3Key);
        $uniqueString = basename($pathInfo['filename']);
        $extension = $pathInfo['extension'];

        return [
            'unique_string' => $uniqueString,
            'extension' => $extension,
        ];
    }
}
