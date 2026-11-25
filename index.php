<?php
/*
 * index.php
 * Entry point: send the visitor to the dashboard (if logged in) or the login page.
 */
require_once __DIR__ . '/includes/functions.php';

redirect(is_logged_in() ? '/dashboard/index.php' : '/auth/login.php');
