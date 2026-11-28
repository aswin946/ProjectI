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

// =====================================================================
// Search and filter (Phase 6)
// =====================================================================

// Reads the filter values from the URL (?type=...&category_id=...&date_from=...&date_to=...&q=...)
// Returns [$filters, $errors]. Invalid values are ignored and reported.
function read_transaction_filters(): array
{
    $errors  = [];
    $filters = ['type' => '', 'category_id' => 0, 'date_from' => '', 'date_to' => '', 'q' => ''];

    $type = get_text('type');
    if (in_array($type, ['income', 'expense'], true)) {
        $filters['type'] = $type;
    }

    $filters['category_id'] = read_id(get_text('category_id'));
    $filters['q'] = mb_substr(get_text('q'), 0, 100);

    // A date is accepted only if it is a real date written as YYYY-MM-DD
    foreach (['date_from' => 'From date', 'date_to' => 'To date'] as $key => $label) {
        $value = get_text($key);
        if ($value === '') {
            continue;
        }
        $d = DateTime::createFromFormat('Y-m-d', $value);
        if ($d && $d->format('Y-m-d') === $value) {
            $filters[$key] = $value;
        } else {
            $errors[] = $label . ' is not a valid date and was ignored.';
        }
    }

    if ($filters['date_from'] !== '' && $filters['date_to'] !== '' && $filters['date_from'] > $filters['date_to']) {
        $errors[] = 'The From date is after the To date, so the date filter was ignored.';
        $filters['date_from'] = '';
        $filters['date_to']   = '';
    }
    return [$filters, $errors];
}

// Builds the WHERE part of the SQL from the filters.
// Returns [$whereSql, $params]. The user_id condition is ALWAYS first.
// Values are never put inside the SQL text, only into $params (prepared statement).
function build_transaction_where(array $filters, int $userId): array
{
    $where  = ['t.user_id = ?'];
    $params = [$userId];

    if ($filters['type'] !== '') {
        $where[]  = 't.type = ?';
        $params[] = $filters['type'];
    }
    if ($filters['category_id'] > 0) {
        $where[]  = 't.category_id = ?';
        $params[] = $filters['category_id'];
    }
    if ($filters['date_from'] !== '') {
        $where[]  = 't.transaction_date >= ?';
        $params[] = $filters['date_from'];
    }
    if ($filters['date_to'] !== '') {
        $where[]  = 't.transaction_date <= ?';
        $params[] = $filters['date_to'];
    }
    if ($filters['q'] !== '') {
        // % and _ typed by the user are escaped so they are searched as normal characters
        $where[]  = 't.description LIKE ?';
        $params[] = '%' . addcslashes($filters['q'], '%_\\') . '%';
    }
    return [implode(' AND ', $where), $params];
}
