<?php
/*
 * budgets/add.php   (optional: ?month=11&year=2026)
 * Sets a monthly budget for an expense category.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/budget_functions.php';

$userId = current_user_id();
$errors = [];

$month = read_id(get_text('month'));
$year  = read_id(get_text('year'));
$values = [
    'category_id'   => 0,
    'month'         => ($month >= 1 && $month <= 12) ? $month : (int) date('n'),
    'year'          => ($year >= 2000 && $year <= (int) date('Y') + 5) ? $year : (int) date('Y'),
    'budget_amount' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        list($errors, $clean) = validate_budget_input($pdo, $userId, $_POST);
        $values = $clean;

        if (!$errors) {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO budgets (user_id, category_id, budget_amount, `month`, `year`) VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([$userId, $clean['category_id'], $clean['budget_amount'], $clean['month'], $clean['year']]);

                set_flash('success', 'Budget saved successfully.');
                redirect('/budgets/index.php?month=' . $clean['month'] . '&year=' . $clean['year']);
            } catch (PDOException $e) {
                if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                    // UNIQUE key (user, category, month, year)
                    $errors[] = 'A budget for this category and month already exists. Edit that budget instead.';
                } else {
                    error_log('PEMS add budget error: ' . $e->getMessage());
                    $errors[] = 'Unable to save budget.';
                    if (APP_DEBUG) {
                        $errors[] = 'Debug: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$categories  = get_budget_categories($pdo, $userId);
$submitLabel = 'Save Budget';
$pageTitle   = 'Set Budget';
require __DIR__ . '/../includes/header.php';
?>
<h1>Set Budget</h1>
<?php if (!$categories): ?>
    <div class="alert alert-error">You have no active expense categories. Add one in Categories first.</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/budget_form.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
