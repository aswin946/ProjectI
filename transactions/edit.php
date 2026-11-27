<?php
/*
 * transactions/edit.php?id=...
 * Edits a transaction. It must belong to the logged-in user.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/transaction_functions.php';

$userId        = current_user_id();
$transactionId = read_id($_GET['id'] ?? 0);

// Never trust the ID in the URL: load the transaction ONLY if it belongs to this user
$transaction = $transactionId ? get_user_transaction($pdo, $transactionId, $userId) : false;

if (!$transaction) {
    set_flash('error', 'Transaction not found.');
    redirect('/transactions/index.php');
}

$errors = [];
$values = [
    'type'             => $transaction['type'],
    'category_id'      => (int) $transaction['category_id'],
    'amount'           => $transaction['amount'],
    'description'      => $transaction['description'],
    'transaction_date' => $transaction['transaction_date'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // The old category is still allowed even if it was made inactive later
        list($errors, $clean) = validate_transaction_input($pdo, $userId, $_POST, (int) $transaction['category_id']);
        $values = $clean;

        if (!$errors) {
            try {
                // "AND user_id = ?" makes sure only the owner's transaction can be updated
                $stmt = $pdo->prepare(
                    'UPDATE transactions
                     SET category_id = ?, amount = ?, type = ?, description = ?, transaction_date = ?
                     WHERE transaction_id = ? AND user_id = ?'
                );
                $stmt->execute([
                    $clean['category_id'], $clean['amount'], $clean['type'],
                    $clean['description'], $clean['transaction_date'],
                    $transactionId, $userId,
                ]);
                set_flash('success', 'Transaction updated successfully.');
                redirect('/transactions/index.php');
            } catch (PDOException $e) {
                error_log('PEMS edit transaction error: ' . $e->getMessage());
                $errors[] = 'Unable to update transaction.';
                if (APP_DEBUG) {
                    $errors[] = 'Debug: ' . $e->getMessage();
                }
            }
        }
    }
}

$categories  = get_selectable_categories($pdo, $userId, (int) $transaction['category_id']);
$submitLabel = 'Update Transaction';
$pageTitle   = 'Edit Transaction';
require __DIR__ . '/../includes/header.php';
?>
<h1>Edit Transaction</h1>
<?php require __DIR__ . '/../includes/transaction_form.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
