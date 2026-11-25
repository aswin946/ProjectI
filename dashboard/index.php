<?php
/*
 * dashboard/index.php  (Phase 2 placeholder)
 * Only logged-in users can open this page. The real dashboard (totals, balance,
 * recent transactions) is built in Phase 5.
 * For now it lists the user's own categories to prove that login and
 * default-category creation work.
 */
require_once __DIR__ . '/../includes/auth_check.php';

$stmt = $pdo->prepare(
    'SELECT category_name, category_type FROM categories
     WHERE user_id = ? ORDER BY category_type DESC, category_name'
);
$stmt->execute([current_user_id()]);
$categories = $stmt->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h1>Dashboard</h1>
<div class="card">
    <h2>Welcome, <?php echo e($_SESSION['full_name']); ?></h2>
    <p>You are logged in. Income, expense and balance totals will appear here in Phase 5.</p>
</div>

<div class="card">
    <h2>Your categories (loaded from the database)</h2>
    <table>
        <tr><th>Category</th><th>Type</th></tr>
        <?php foreach ($categories as $c): ?>
        <tr>
            <td><?php echo e($c['category_name']); ?></td>
            <td><?php echo e(ucfirst($c['category_type'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
