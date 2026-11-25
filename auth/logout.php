<?php
/*
 * auth/logout.php
 * Destroys the session and redirects to the login page.
 * Logout only works with a POST request (the Logout button in the navbar).
 */
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    // Someone opened this URL directly: do not log out, just send them back
    redirect(is_logged_in() ? '/dashboard/index.php' : '/auth/login.php');
}

// Remove all session data and the session cookie
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Start a fresh session only so we can show the "logged out" message
session_start();
session_regenerate_id(true);
set_flash('success', 'You have been logged out.');
redirect('/auth/login.php');
