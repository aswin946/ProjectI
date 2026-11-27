<?php
/*
 * includes/transaction_functions.php
 * Helper functions shared by the transaction pages (index, add, edit, delete).
 * Needs includes/category_functions.php for read_id().
 */
require_once __DIR__ . '/category_functions.php';

// Loads ONE transaction, but only if it belongs to the given user.
// Returns false if it does not exist or belongs to someone else.
function get_user_transaction(PDO $pdo, int $transactionId, int $userId)
{
    $stmt = $pdo->prepare(
        'SELECT t.transaction_id, t.category_id, t.amount, t.type, t.description,
                t.transaction_date, c.category_name
         FROM transactions t
         JOIN categories c ON c.category_id = t.category_id
         WHERE t.transaction_id = ? AND t.user_id = ?'
    );
    $stmt->execute([$transactionId, $userId]);
    return $stmt->fetch();
}

// Categories the user can choose in the form: active ones, plus the one
// already used by the transaction being edited (even if it became inactive).
function get_selectable_categories(PDO $pdo, int $userId, int $keepCategoryId = 0): array
{
    $stmt = $pdo->prepare(
        "SELECT category_id, category_name, category_type
         FROM categories
         WHERE user_id = ? AND (status = 'active' OR category_id = ?)
         ORDER BY category_name"
    );
    $stmt->execute([$userId, $keepCategoryId]);
    return $stmt->fetchAll();
}

// Validates the form data of a transaction.
// Returns [$errors, $clean]:  $errors = list of messages, $clean = safe values to save.
function validate_transaction_input(PDO $pdo, int $userId, array $post, int $existingCategoryId = 0): array
{
    $errors = [];

    // Read each field as trimmed text (anything that is not text becomes '')
    $text = function (string $key) use ($post): string {
        return (isset($post[$key]) && is_string($post[$key])) ? trim($post[$key]) : '';
    };

    $type        = $text('type');
    $categoryId  = read_id($text('category_id'));
    $amountText  = $text('amount');
    $description = $text('description');
    $date        = $text('transaction_date');

    // ----- Type -----
    if (!in_array($type, ['income', 'expense'], true)) {
        $errors[] = 'Please choose a valid transaction type.';
    }

    // ----- Amount: digits with optional 1-2 decimals, greater than zero -----
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $amountText) || (float) $amountText <= 0) {
        $errors[] = 'Amount must be a positive number with at most 2 decimal places.';
    }

    // ----- Date: real calendar date, from 2000-01-01 up to today -----
    $dateObject = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
        $errors[] = 'Please enter a valid date.';
    } elseif ($date < '2000-01-01' || $date > date('Y-m-d')) {
        $errors[] = 'Date must be between 2000-01-01 and today.';
    }

    // ----- Description (optional) -----
    if (mb_strlen($description) > 255) {
        $errors[] = 'Description must not be longer than 255 characters.';
    }

    // ----- Category: must belong to this user, be active, and match the type -----
    if ($categoryId === 0) {
        $errors[] = 'Please select a category.';
    } elseif (in_array($type, ['income', 'expense'], true)) {
        $stmt = $pdo->prepare('SELECT category_type, status FROM categories WHERE category_id = ? AND user_id = ?');
        $stmt->execute([$categoryId, $userId]);
        $category = $stmt->fetch();

        if (!$category) {
            $errors[] = 'Please select a valid category.';
        } elseif ($category['category_type'] !== $type) {
            $errors[] = 'The selected category is not an ' . ($type === 'income' ? 'income' : 'expense') . ' category.';
        } elseif ($category['status'] !== 'active' && $categoryId !== $existingCategoryId) {
            $errors[] = 'The selected category is inactive.';
        }
    }

    $clean = [
        'type'             => $type,
        'category_id'      => $categoryId,
        'amount'           => $amountText,
        'description'      => $description,
        'transaction_date' => $date,
    ];
    return [$errors, $clean];
}
