<?php

namespace Database;

use Helpers\Settings;

class MemcachedWrapper
{
    private static ?\Memcached $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): \Memcached
    {
        if (self::$instance === null) {
            self::$instance = new \Memcached();

            $host = Settings::env('MEMCACHED_HOST');
            $port = (int) Settings::env('MEMCACHED_PORT');

            self::$instance->addServer($host, $port);
        }

        return self::$instance;
    }
}
