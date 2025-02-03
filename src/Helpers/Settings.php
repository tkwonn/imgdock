<?php

namespace Helpers;

use Exception;

class Settings
{
    public static function env(string $key): string
    {
        $val = getenv($key);
        if ($val === false) {
            $config = parse_ini_file(dirname(__FILE__, 3) . '/' . '.env');

            if ($config === false) {
                throw new Exception('Failed to read and parse .env file');
            }

            return $config[$key];
        }

        return $val;
    }
}
