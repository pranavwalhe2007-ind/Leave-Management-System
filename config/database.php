<?php
declare(strict_types=1);

$databaseConfig = [
    'host' => getenv('DB_HOST'),
    'name' => getenv('DB_NAME'),
    'user' => getenv('DB_USER'),
    'pass' => getenv('DB_PASS'),
];

$missingDatabaseSettings = [];
foreach ($databaseConfig as $setting => $value) {
    if ($setting === 'pass' && $value === false) {
        $databaseConfig[$setting] = '';
        continue;
    }
    if ($value === false) {
        $missingDatabaseSettings[] = 'DB_' . strtoupper($setting);
    }
}

if ($missingDatabaseSettings) {
    throw new RuntimeException(
        'Missing required database environment variables: ' . implode(', ', $missingDatabaseSettings)
    );
}

define('DB_HOST', $databaseConfig['host']);
define('DB_NAME', $databaseConfig['name']);
define('DB_USER', $databaseConfig['user']);
define('DB_PASS', $databaseConfig['pass']);

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $pdo;
}
