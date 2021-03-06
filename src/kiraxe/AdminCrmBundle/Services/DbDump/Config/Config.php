<?php

namespace kiraxe\AdminCrmBundle\Services\DbDump\Config;

use PDO;

class Config {

    private static $parameters = [
        'mysql' => [
            'host' => '127.0.0.1',
            'port' => null,
            'name' => 'gl',
            'user' => 'root',
            'charset' => 'UTF8',
            'password' => null
        ]
    ];

    private static $option = [
        'mysql' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ];

    public static function getDbParams() : array {
        return self::$parameters;
    }

    public static function getOptions(): array {
        return self::$option;
    }
}