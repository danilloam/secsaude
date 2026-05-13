<?php
declare(strict_types=1);
define('BASE_URL', '/secsaude');
require_once __DIR__ . '/env.php';




function mysqlConnect(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $pdo = new PDO(
			env('DB_DSN'),
			env('DB_USER'),
			env('DB_PASS'),
         
            
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    return $pdo;
}

/**
 * POSTGRES (opcional)
 */
function pgConnect(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $pdo = new PDO(
			env('PG_DSN'),
			env('PG_USER'),
			env('PG_PASS'),
                        [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    return $pdo;
}