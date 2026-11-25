<?php
/*
 * includes/auth_check.php
 * Put this at the TOP of every protected page:
 *
 *     require_once __DIR__ . '/../includes/auth_check.php';
 *
 * It makes sure a user is logged in and gives the page the $pdo connection.
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';

// Stop the browser from showing protected pages from its cache after logout
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// 1. Must be logged in
if (!is_logged_in()) {
    set_flash('error', 'Please log in to continue.');
    redirect('/auth/login.php');
}

// 2. The account must still exist and be active
$stmt = $pdo->prepare('SELECT status FROM users WHERE user_id = ?');
$stmt->execute([current_user_id()]);
$account = $stmt->fetch();

if (!$account || $account['status'] !== 'active') {
    $_SESSION = [];
    session_destroy();
    session_start();
    set_flash('error', 'Unauthorized access.');
    redirect('/auth/login.php');
}
