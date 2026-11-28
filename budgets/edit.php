<?php
/*
 * budgets/edit.php?id=...
 * Edits a budget. It must belong to the logged-in user.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/budget_functions.php';

$userId   = current_user_id();
$budgetId = read_id(get_text('id'));

// Never trust the ID in the URL: load the budget ONLY if it belongs to this user
$budget = $budgetId ? get_user_budget($pdo, $budgetId, $userId) : false;

if (!$budget) {
    set_flash('error', 'Budget not found.');
    redirect('/budgets/index.php');
}

$errors = [];
$values = [
    'category_id'   => (int) $budget['category_id'],
    'month'         => (int) $budget['month'],
    'year'          => (int) $budget['year'],
    'budget_amount' => $budget['budget_amount'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        list($errors, $clean) = validate_budget_input($pdo, $userId, $_POST, (int) $budget['category_id']);
        $values = $clean;

        if (!$errors) {
            try {
                // "AND user_id = ?" makes sure only the owner's budget can be updated
                $stmt = $pdo->prepare(
                    'UPDATE budgets SET category_id = ?, budget_amount = ?, `month` = ?, `year` = ?
                     WHERE budget_id = ? AND user_id = ?'
                );
                $stmt->execute([$clean['category_id'], $clean['budget_amount'], $clean['month'], $clean['year'], $budgetId, $userId]);

                set_flash('success', 'Budget updated successfully.');
                redirect('/budgets/index.php?month=' . $clean['month'] . '&year=' . $clean['year']);
            } catch (PDOException $e) {
                if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                    $errors[] = 'A budget for this category and month already exists.';
                } else {
                    error_log('PEMS edit budget error: ' . $e->getMessage());
                    $errors[] = 'Unable to update budget.';
                    if (APP_DEBUG) {
                        $errors[] = 'Debug: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$categories  = get_budget_categories($pdo, $userId, (int) $budget['category_id']);
$submitLabel = 'Update Budget';
$pageTitle   = 'Edit Budget';
require __DIR__ . '/../includes/header.php';
?>
<h1>Edit Budget</h1>
<?php require __DIR__ . '/../includes/budget_form.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
