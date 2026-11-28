<?php
/*
 * includes/functions.php
 * Small helper functions used by many pages.
 * Every page includes this file first. It also starts the PHP session.
 */

require_once __DIR__ . '/../config/config.php';

// ---------- Session ----------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,          // cookie lasts until the browser is closed
        'path'     => '/',
        'httponly' => true,       // JavaScript cannot read the session cookie
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------- Basic security headers ----------
header('X-Content-Type-Options: nosniff');   // browser must not guess file types
header('X-Frame-Options: SAMEORIGIN');       // other websites cannot put our pages in a frame
header('Referrer-Policy: same-origin');

// ---------- Reading form / URL values safely ----------
// A value sent by a user can be text OR an array (for example ?id[]=1).
// These helpers always return plain text, so the rest of the code never gets a surprise.
function post_text(string $key): string   // trimmed text from a form
{
    return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : '';
}
function post_raw(string $key): string    // untrimmed text (used for passwords)
{
    return (isset($_POST[$key]) && is_string($_POST[$key])) ? $_POST[$key] : '';
}
function get_text(string $key): string    // trimmed text from the URL (?key=value)
{
    return (isset($_GET[$key]) && is_string($_GET[$key])) ? trim($_GET[$key]) : '';
}

// Turns a value from the URL or a form into a positive whole number.
// Returns 0 when it is not a valid ID (so "abc", "-5", "1.5" and "" all become 0).
function read_id($value): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT);
    return ($id !== false && $id > 0) ? $id : 0;
}

// ---------- Month names ----------
function month_name(int $month): string
{
    $names = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
              7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
    return $names[$month] ?? '';
}

// ---------- Output safety ----------
// e() = "escape". Use it every time you print text that a user typed.
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// ---------- Redirect ----------
// Example: redirect('/dashboard/index.php');
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

// ---------- Login state ----------
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

// ID of the logged-in user. Every user-specific SQL query must use this value.
function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

// ---------- Flash messages (shown once, on the next page) ----------
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// ---------- CSRF protection ----------
// A secret token is put in every form and checked when the form is submitted.
// This stops other websites from submitting forms on behalf of a logged-in user.
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}

// ---------- Money format ----------
function format_money($amount): string
{
    return 'Rs. ' . number_format((float) $amount, 2);
}

// ---------- Default categories for a new user ----------
// Expense categories are the ones listed in the proposal. Salary and Other Income
// are the two income categories approved for this project.
function create_default_categories(PDO $pdo, int $userId): void
{
    $defaults = [
        ['Food', 'expense'], ['Transport', 'expense'], ['Education', 'expense'],
        ['Bills', 'expense'], ['Shopping', 'expense'], ['Health', 'expense'],
        ['Entertainment', 'expense'], ['Other', 'expense'],
        ['Salary', 'income'], ['Other Income', 'income'],
    ];
    $stmt = $pdo->prepare(
        'INSERT INTO categories (user_id, category_name, category_type) VALUES (?, ?, ?)'
    );
    foreach ($defaults as $row) {
        $stmt->execute([$userId, $row[0], $row[1]]);
    }
}
