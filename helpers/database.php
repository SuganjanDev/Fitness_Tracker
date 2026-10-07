<?php

declare(strict_types=1);

function db(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $config = require __DIR__ . '/../config/database.php';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $connection = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            (int) $config['port']
        );
        $connection->set_charset($config['charset']);
    } catch (mysqli_sql_exception $exception) {
        http_response_code(500);
        exit('Database connection failed. Check your XAMPP MySQL service and import the schema first.');
    }

    return $connection;
}
