<?php
/*
 * transactions/index.php
 * Transaction history of the logged-in user (newest first).
 * Search and filters are added in Phase 6.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/transaction_functions.php';

// WHERE t.user_id = ?  ->  a user only ever sees their own transactions
$stmt = $pdo->prepare(
    'SELECT t.transaction_id, t.amount, t.type, t.description, t.transaction_date, c.category_name
     FROM transactions t
     JOIN categories c ON c.category_id = t.category_id
     WHERE t.user_id = ?
     ORDER BY t.transaction_date DESC, t.transaction_id DESC'
);
$stmt->execute([current_user_id()]);
$transactions = $stmt->fetchAll();

$pageTitle = 'Transactions';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <h1>Transactions</h1>
    <div class="btn-group">
        <a class="btn btn-success" href="<?php echo BASE_URL; ?>/transactions/add.php?type=income">+ Add Income</a>
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/transactions/add.php?type=expense">+ Add Expense</a>
    </div>
</div>

<div class="card">
<?php if (!$transactions): ?>
    <p class="empty-state">No transactions yet. Use "Add Income" or "Add Expense" to record your first one.</p>
<?php else: ?>
    <div class="table-wrap">
    <table>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Category</th>
            <th>Description</th>
            <th class="text-right">Amount</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($transactions as $t): ?>
        <tr>
            <td><?php echo e(date('d M Y', strtotime($t['transaction_date']))); ?></td>
            <td><span class="badge badge-<?php echo e($t['type']); ?>"><?php echo e(ucfirst($t['type'])); ?></span></td>
            <td><?php echo e($t['category_name']); ?></td>
            <td><?php echo e($t['description']); ?></td>
            <td class="text-right amount-<?php echo e($t['type']); ?>">
                <?php echo $t['type'] === 'income' ? '+' : '-'; ?> <?php echo e(format_money($t['amount'])); ?>
            </td>
            <td class="actions">
                <a class="btn btn-small btn-light" href="<?php echo BASE_URL; ?>/transactions/edit.php?id=<?php echo (int) $t['transaction_id']; ?>">Edit</a>
                <form method="post" action="<?php echo BASE_URL; ?>/transactions/delete.php"
                      data-confirm="Delete this <?php echo e($t['type']); ?> of <?php echo e(format_money($t['amount'])); ?> (<?php echo e($t['category_name']); ?>)? This cannot be undone.">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="transaction_id" value="<?php echo (int) $t['transaction_id']; ?>">
                    <button type="submit" class="btn btn-small btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
    <p class="muted"><?php echo count($transactions); ?> transaction(s)</p>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
