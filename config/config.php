<?php
/*
 * config/config.php
 * General settings for the PEMS project.
 * Change these values if your XAMPP setup is different.
 */

// Show detailed errors while developing. Set to false when you demonstrate/submit.
define('APP_DEBUG', true);

// Name of the project folder inside C:\xampp\htdocs\
define('BASE_URL', '/PEMS');

// Application name (used in page titles later)
define('APP_NAME', 'Personal Expense Management System');

// Database settings (XAMPP defaults: user = root, empty password)
define('DB_HOST', 'localhost');
define('DB_NAME', 'pems_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Show PHP errors only in debug mode
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

// Use Nepal time for dates created by PHP
date_default_timezone_set('Asia/Kathmandu');
