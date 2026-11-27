<?php
/*
 * categories/index.php
 * Lists all categories of the logged-in user.
 */
require_once __DIR__ . '/../includes/auth_check.php';

// Only this user's categories (WHERE user_id = ?), with usage counts
$stmt = $pdo->prepare(
    'SELECT c.category_id, c.category_name, c.category_type, c.status,
            (SELECT COUNT(*) FROM transactions t WHERE t.category_id = c.category_id AND t.user_id = c.user_id) AS transaction_count,
            (SELECT COUNT(*) FROM budgets b       WHERE b.category_id = c.category_id AND b.user_id = c.user_id) AS budget_count
     FROM categories c
     WHERE c.user_id = ?
     ORDER BY c.category_type DESC, c.category_name'
);
$stmt->execute([current_user_id()]);
$categories = $stmt->fetchAll();

$pageTitle = 'Categories';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <h1>Categories</h1>
    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/categories/add.php">+ Add Category</a>
</div>

<div class="card">
<?php if (!$categories): ?>
    <p class="empty-state">You have no categories yet. Click "Add Category" to create one.</p>
<?php else: ?>
    <div class="table-wrap">
    <table>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Status</th>
            <th>Usage</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($categories as $c): ?>
        <?php $inUse = ($c['transaction_count'] > 0 || $c['budget_count'] > 0); ?>
        <tr>
            <td><?php echo e($c['category_name']); ?></td>
            <td><span class="badge badge-<?php echo e($c['category_type']); ?>"><?php echo e(ucfirst($c['category_type'])); ?></span></td>
            <td>
                <?php if ($c['status'] === 'active'): ?>
                    <span class="badge badge-active">Active</span>
                <?php else: ?>
                    <span class="badge badge-inactive">Inactive</span>
                <?php endif; ?>
            </td>
            <td class="muted">
                <?php
                if ($inUse) {
                    echo e($c['transaction_count'] . ' transaction(s), ' . $c['budget_count'] . ' budget(s)');
                } else {
                    echo 'Not used';
                }
                ?>
            </td>
            <td class="actions">
                <a class="btn btn-small btn-light" href="<?php echo BASE_URL; ?>/categories/edit.php?id=<?php echo (int) $c['category_id']; ?>">Edit</a>
                <?php if (!$inUse): ?>
                <!-- Delete is a POST form with a confirmation question (see assets/js/script.js) -->
                <form method="post" action="<?php echo BASE_URL; ?>/categories/delete.php"
                      data-confirm="Delete the category '<?php echo e($c['category_name']); ?>'? This cannot be undone.">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="category_id" value="<?php echo (int) $c['category_id']; ?>">
                    <button type="submit" class="btn btn-small btn-danger">Delete</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
    <p class="muted">A category that is used by transactions or budgets cannot be deleted. Edit it and mark it Inactive instead.</p>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
