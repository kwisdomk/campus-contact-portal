<?php
// Copy this file to config.php and update with your local database credentials.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'campus_contact_db';
const DB_USER = 'root';
const DB_PASS = '';

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function db(): PDO {
    static $connection = null;
    if ($connection === null) {
        try {
            $connection = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(503);
            exit('Database unavailable. Check that MySQL is running.');
        }
    }
    return $connection;
}
