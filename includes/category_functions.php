<?php
/*
 * includes/category_functions.php
 * Helper functions shared by the category pages (add, edit, delete, index).
 */

// Checks the name and type typed by the user. Returns a list of error messages (empty = OK).
function validate_category_input(string $name, string $type): array
{
    $errors = [];

    if (mb_strlen($name) < 1 || mb_strlen($name) > 50) {
        $errors[] = 'Category name must be between 1 and 50 characters.';
    }
    if (!in_array($type, ['income', 'expense'], true)) {
        $errors[] = 'Please choose a valid category type.';
    }
    return $errors;
}

// Loads ONE category, but only if it belongs to the given user.
// Returns false when the category does not exist or belongs to someone else.
// It also returns how many transactions and budgets use the category.
function get_user_category(PDO $pdo, int $categoryId, int $userId)
{
    $stmt = $pdo->prepare(
        'SELECT c.category_id, c.category_name, c.category_type, c.status,
                (SELECT COUNT(*) FROM transactions t WHERE t.category_id = c.category_id AND t.user_id = c.user_id) AS transaction_count,
                (SELECT COUNT(*) FROM budgets b       WHERE b.category_id = c.category_id AND b.user_id = c.user_id) AS budget_count
         FROM categories c
         WHERE c.category_id = ? AND c.user_id = ?'
    );
    $stmt->execute([$categoryId, $userId]);
    return $stmt->fetch();
}
