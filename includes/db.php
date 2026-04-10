<?php

declare(strict_types=1);

function foovia_db(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $connection = new mysqli('127.0.0.1', 'root', '', 'foovia_db');
    $connection->set_charset('utf8mb4');

    return $connection;
}
