<?php
/*
 * categories/add.php
 * Creates a new category for the logged-in user.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/category_functions.php';

$errors = [];
$name   = '';
$type   = 'expense';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name = post_text('category_name');
        $type = post_text('category_type');
        $errors = validate_category_input($name, $type);

        if (!$errors) {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO categories (user_id, category_name, category_type) VALUES (?, ?, ?)'
                );
                $stmt->execute([current_user_id(), $name, $type]);

                set_flash('success', 'Category added successfully.');
                redirect('/categories/index.php');
            } catch (PDOException $e) {
                if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                    // The UNIQUE key (user_id, category_name, category_type) was violated
                    $errors[] = 'You already have ' . ($type === 'income' ? 'an income' : 'an expense') . ' category with this name.';
                } else {
                    error_log('PEMS add category error: ' . $e->getMessage());
                    $errors[] = 'Unable to save category.';
                    if (APP_DEBUG) {
                        $errors[] = 'Debug: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$pageTitle = 'Add Category';
require __DIR__ . '/../includes/header.php';
?>
<h1>Add Category</h1>

<div class="card form-card">
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endforeach; ?>

    <form method="post" action="">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label for="category_name">Category name</label>
            <input type="text" id="category_name" name="category_name" value="<?php echo e($name); ?>" required maxlength="50">
        </div>

        <div class="form-group">
            <label for="category_type">Type</label>
            <select id="category_type" name="category_type" required>
                <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                <option value="income"  <?php echo $type === 'income'  ? 'selected' : ''; ?>>Income</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Category</button>
            <a class="btn btn-light" href="<?php echo BASE_URL; ?>/categories/index.php">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
