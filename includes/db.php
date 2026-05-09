<?php

require_once __DIR__ . '/config.php';

try {

    $dsn = "mysql:host=" . DB_HOST .
           ";port=" . (getenv('MYSQLPORT') ?: '3306') .
           ";dbname=" . DB_NAME .
           ";charset=" . DB_CHARSET;

    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die("Database Connection Failed: " . $e->getMessage());

}

function db()
{
    global $pdo;
    return $pdo;
}
