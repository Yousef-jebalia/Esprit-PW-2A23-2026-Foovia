<?php

declare(strict_types=1);

require_once __DIR__ . '/../env.php';

final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = foovia_env('DB_HOST', '127.0.0.1');
        $port = foovia_env('DB_PORT', '3306');
        $database = foovia_env('DB_NAME', 'foovia_db');
        $charset = foovia_env('DB_CHARSET', 'utf8mb4');
        $user = foovia_env('DB_USER', 'root');
        $password = foovia_env('DB_PASS', '');

        self::$connection = new PDO(
            "mysql:host=$host;port=$port;dbname=$database;charset=$charset",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return self::$connection;
    }
}
