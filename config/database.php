<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db(): mysqli
{
    static $connection = null;
    if ($connection instanceof mysqli) {
        return $connection;
    }
    $connection = new mysqli(
        app_env('DB_HOST') ?: '127.0.0.1',
        app_env('DB_USER') ?: 'root',
        app_env('DB_PASSWORD') ?: '',
        app_env('DB_NAME') ?: 'labflow_crm',
        (int) (app_env('DB_PORT') ?: 3306)
    );
    $connection->set_charset('utf8mb4');
    return $connection;
}

function db_ready(): bool
{
    try {
        db()->query('SELECT 1');
        return true;
    } catch (Throwable) {
        return false;
    }
}
