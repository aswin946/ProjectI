<?php
/*
 * budgets/delete.php
 * Deletes a budget. Only works with a POST request (the Delete button on the list).
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/budget_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/budgets/index.php');
}
if (!verify_csrf()) {
    set_flash('error', 'Invalid request. Please try again.');
    redirect('/budgets/index.php');
}

$userId   = current_user_id();
$budgetId = read_id(post_text('budget_id'));
$budget   = $budgetId ? get_user_budget($pdo, $budgetId, $userId) : false;

if (!$budget) {
    set_flash('error', 'Budget not found.');
    redirect('/budgets/index.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM budgets WHERE budget_id = ? AND user_id = ?');
    $stmt->execute([$budgetId, $userId]);
    set_flash('success', 'Budget deleted.');
} catch (PDOException $e) {
    error_log('PEMS delete budget error: ' . $e->getMessage());
    set_flash('error', 'Unable to delete budget.');
}

// Go back to the month the budget belonged to
redirect('/budgets/index.php?month=' . (int) $budget['month'] . '&year=' . (int) $budget['year']);
