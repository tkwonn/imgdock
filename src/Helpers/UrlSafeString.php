<?php

namespace Helpers;

use InvalidArgumentException;

class UrlSafeString
{
    private const RANDOM_BYTES_LENGTH = 32;
    private const URL_SAFE_CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';

    public static function random(int $length): string
    {
        if ($length < 1 || $length > self::RANDOM_BYTES_LENGTH) {
            throw new InvalidArgumentException(
                'Length must be between 1 and ' . self::RANDOM_BYTES_LENGTH . ". Given: {$length}"
            );
        }

        // 1. Generate 256 bits (32 bytes) of random data
        $randomBytes = random_bytes(self::RANDOM_BYTES_LENGTH);
        $charLen = strlen(self::URL_SAFE_CHARS); // 64
        $uniqueString = '';

        // 2. Convert random bytes to URL-safe characters
        for ($i = 0; $i < self::RANDOM_BYTES_LENGTH; $i++) {
            $value = ord($randomBytes[$i]);
            $uniqueString .= self::URL_SAFE_CHARS[$value & ($charLen - 1)];
        }

        return substr($uniqueString, 0, $length);
    }
}
