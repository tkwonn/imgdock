<?php

namespace Helpers;

use Exception;

class Settings
{
    public static function env(string $key): string
    {

        $val = getenv($key);
        if ($val === false) {
            throw new Exception('Failed to read and parse .env file');
        }

        return $val;
    }
}
