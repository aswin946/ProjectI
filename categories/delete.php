<?php
/*
 * categories/delete.php
 * Deletes a category. Only works with a POST request (the Delete button on the list page).
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/category_functions.php';

// Opening this URL directly (GET) does nothing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/categories/index.php');
}

if (!verify_csrf()) {
    set_flash('error', 'Invalid request. Please try again.');
    redirect('/categories/index.php');
}

$userId     = current_user_id();
$categoryId = read_id($_POST['category_id'] ?? 0);

// The category must exist AND belong to the logged-in user
$category = $categoryId ? get_user_category($pdo, $categoryId, $userId) : false;

if (!$category) {
    set_flash('error', 'Category not found.');
    redirect('/categories/index.php');
}

// A category used by transactions or budgets must not be deleted
if ($category['transaction_count'] > 0 || $category['budget_count'] > 0) {
    set_flash('error', 'This category is used by transactions or budgets and cannot be deleted. Mark it Inactive instead.');
    redirect('/categories/index.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM categories WHERE category_id = ? AND user_id = ?');
    $stmt->execute([$categoryId, $userId]);
    set_flash('success', 'Category deleted.');
} catch (PDOException $e) {
    // Foreign key error 1451 = the database itself refused because the category is in use
    error_log('PEMS delete category error: ' . $e->getMessage());
    set_flash('error', 'Unable to delete category.');
}

redirect('/categories/index.php');
