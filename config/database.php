<?php
/*
 * config/database.php
 * Creates ONE database connection ($pdo) that every page can use:
 *
 *     require_once __DIR__ . '/../config/database.php';
 *     $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
 *
 * The database settings (DB_HOST, DB_NAME, DB_USER, DB_PASS) are in config/config.php.
 */

require_once __DIR__ . '/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on SQL errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Always write the real error to the PHP error log (xampp/apache/logs/error.log)
    error_log('PEMS database connection failed: ' . $e->getMessage());

    if (APP_DEBUG) {
        die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
    }
    die('Unable to connect to the database. Please try again later.');
}
