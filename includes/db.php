<?php
// ═══════════════════════════════════════════════════════════
// Phoenix Arabia — Database Connection (PDO with prepared statements)
// ═══════════════════════════════════════════════════════════

require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $e) {
            // Don't expose DB errors to user
            error_log('Database connection error: ' . $e->getMessage());
            if (IS_PRODUCTION) {
                die('Service temporarily unavailable. Please try again later.');
            } else {
                die('Database error: ' . $e->getMessage());
            }
        }
    }
    return $pdo;
}

