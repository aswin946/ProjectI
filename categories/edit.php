<?php
/*
 * categories/edit.php?id=...
 * Edits a category. The category must belong to the logged-in user.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/category_functions.php';

$userId     = current_user_id();
$categoryId = read_id($_GET['id'] ?? 0);

// Never trust the ID in the URL: load the category ONLY if it belongs to this user
$category = $categoryId ? get_user_category($pdo, $categoryId, $userId) : false;

if (!$category) {
    set_flash('error', 'Category not found.');
    redirect('/categories/index.php');
}

$inUse  = ($category['transaction_count'] > 0 || $category['budget_count'] > 0);
$errors = [];
$name   = $category['category_name'];
$type   = $category['category_type'];
$status = $category['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name   = trim($_POST['category_name'] ?? '');
        $status = $_POST['status'] ?? '';

        // The type of a category that is already used cannot be changed
        $type = $inUse ? $category['category_type'] : ($_POST['category_type'] ?? '');

        $errors = validate_category_input($name, $type);
        if (!in_array($status, ['active', 'inactive'], true)) {
            $errors[] = 'Please choose a valid status.';
        }

        if (!$errors) {
            try {
                // "AND user_id = ?" makes sure only the owner's category can be updated
                $stmt = $pdo->prepare(
                    'UPDATE categories SET category_name = ?, category_type = ?, status = ?
                     WHERE category_id = ? AND user_id = ?'
                );
                $stmt->execute([$name, $type, $status, $categoryId, $userId]);

                set_flash('success', 'Category updated successfully.');
                redirect('/categories/index.php');
            } catch (PDOException $e) {
                if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                    $errors[] = 'You already have ' . ($type === 'income' ? 'an income' : 'an expense') . ' category with this name.';
                } else {
                    error_log('PEMS edit category error: ' . $e->getMessage());
                    $errors[] = 'Unable to update category.';
                    if (APP_DEBUG) {
                        $errors[] = 'Debug: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$pageTitle = 'Edit Category';
require __DIR__ . '/../includes/header.php';
?>
<h1>Edit Category</h1>

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
            <select id="category_type" name="category_type" <?php echo $inUse ? 'disabled' : ''; ?>>
                <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                <option value="income"  <?php echo $type === 'income'  ? 'selected' : ''; ?>>Income</option>
            </select>
            <?php if ($inUse): ?>
                <p class="muted">The type cannot be changed because this category is used by transactions or budgets.</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active"   <?php echo $status === 'active'   ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <p class="muted">Inactive categories are hidden when adding new transactions, but old records keep them.</p>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Category</button>
            <a class="btn btn-light" href="<?php echo BASE_URL; ?>/categories/index.php">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
