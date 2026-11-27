<?php
/*
 * transactions/add.php            (add.php?type=income or add.php?type=expense)
 * Adds a new income or expense for the logged-in user.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/transaction_functions.php';

$userId = current_user_id();
$errors = [];

// Starting values. ?type=income / ?type=expense chooses the type shown first.
$values = [
    'type'             => (($_GET['type'] ?? '') === 'income') ? 'income' : 'expense',
    'category_id'      => 0,
    'amount'           => '',
    'description'      => '',
    'transaction_date' => date('Y-m-d'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        list($errors, $clean) = validate_transaction_input($pdo, $userId, $_POST);
        $values = $clean;   // keep what the user typed if there are errors

        if (!$errors) {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $userId, $clean['category_id'], $clean['amount'],
                    $clean['type'], $clean['description'], $clean['transaction_date'],
                ]);
                set_flash('success', ucfirst($clean['type']) . ' added successfully.');
                redirect('/transactions/index.php');
            } catch (PDOException $e) {
                error_log('PEMS add transaction error: ' . $e->getMessage());
                $errors[] = 'Unable to save transaction.';
                if (APP_DEBUG) {
                    $errors[] = 'Debug: ' . $e->getMessage();
                }
            }
        }
    }
}

$categories  = get_selectable_categories($pdo, $userId);
$submitLabel = 'Save Transaction';
$pageTitle   = 'Add Transaction';
require __DIR__ . '/../includes/header.php';
?>
<h1>Add Transaction</h1>
<?php require __DIR__ . '/../includes/transaction_form.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
