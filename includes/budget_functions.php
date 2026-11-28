<?php
/*
 * includes/budget_functions.php
 * Helper functions shared by the budget pages.
 * A budget = an amount planned for ONE expense category in ONE month of ONE year.
 */
require_once __DIR__ . '/category_functions.php';

// Loads ONE budget, only if it belongs to the given user (false otherwise).
function get_user_budget(PDO $pdo, int $budgetId, int $userId)
{
    $stmt = $pdo->prepare(
        'SELECT b.budget_id, b.category_id, b.budget_amount, b.`month` AS month, b.`year` AS year, c.category_name
         FROM budgets b
         JOIN categories c ON c.category_id = b.category_id
         WHERE b.budget_id = ? AND b.user_id = ?'
    );
    $stmt->execute([$budgetId, $userId]);
    return $stmt->fetch();
}

// Expense categories the user may choose: active ones, plus the one already used by the budget being edited.
function get_budget_categories(PDO $pdo, int $userId, int $keepCategoryId = 0): array
{
    $stmt = $pdo->prepare(
        "SELECT category_id, category_name
         FROM categories
         WHERE user_id = ? AND category_type = 'expense' AND (status = 'active' OR category_id = ?)
         ORDER BY category_name"
    );
    $stmt->execute([$userId, $keepCategoryId]);
    return $stmt->fetchAll();
}

// Validates the budget form. Returns [$errors, $clean].
function validate_budget_input(PDO $pdo, int $userId, array $post, int $existingCategoryId = 0): array
{
    $errors = [];
    $text = function (string $key) use ($post): string {
        return (isset($post[$key]) && is_string($post[$key])) ? trim($post[$key]) : '';
    };

    $categoryId = read_id($text('category_id'));
    $month      = read_id($text('month'));
    $year       = read_id($text('year'));
    $amountText = $text('budget_amount');

    // Amount: positive, at most 2 decimals (same rule as transactions)
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $amountText) || (float) $amountText <= 0) {
        $errors[] = 'Budget amount must be a positive number with at most 2 decimal places.';
    }
    if ($month < 1 || $month > 12) {
        $errors[] = 'Please choose a valid month.';
    }
    if ($year < 2000 || $year > (int) date('Y') + 5) {
        $errors[] = 'Please choose a valid year.';
    }

    // Category: must belong to this user, be an EXPENSE category and be active
    if ($categoryId === 0) {
        $errors[] = 'Please select a category.';
    } else {
        $stmt = $pdo->prepare('SELECT category_type, status FROM categories WHERE category_id = ? AND user_id = ?');
        $stmt->execute([$categoryId, $userId]);
        $category = $stmt->fetch();

        if (!$category) {
            $errors[] = 'Please select a valid category.';
        } elseif ($category['category_type'] !== 'expense') {
            $errors[] = 'A budget can only be set for an expense category.';
        } elseif ($category['status'] !== 'active' && $categoryId !== $existingCategoryId) {
            $errors[] = 'The selected category is inactive.';
        }
    }

    $clean = ['category_id' => $categoryId, 'month' => $month, 'year' => $year, 'budget_amount' => $amountText];
    return [$errors, $clean];
}
