<?php
/*
 * transactions/delete.php
 * Deletes a transaction. Only works with a POST request (the Delete button on the list).
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/transaction_functions.php';

// Opening this URL directly (GET) does nothing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/transactions/index.php');
}

if (!verify_csrf()) {
    set_flash('error', 'Invalid request. Please try again.');
    redirect('/transactions/index.php');
}

$userId        = current_user_id();
$transactionId = read_id($_POST['transaction_id'] ?? 0);

// The transaction must exist AND belong to the logged-in user
$transaction = $transactionId ? get_user_transaction($pdo, $transactionId, $userId) : false;

if (!$transaction) {
    set_flash('error', 'Transaction not found.');
    redirect('/transactions/index.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM transactions WHERE transaction_id = ? AND user_id = ?');
    $stmt->execute([$transactionId, $userId]);
    set_flash('success', 'Transaction deleted.');
} catch (PDOException $e) {
    error_log('PEMS delete transaction error: ' . $e->getMessage());
    set_flash('error', 'Unable to delete transaction.');
}

redirect('/transactions/index.php');
