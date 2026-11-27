<?php
/*
 * dashboard/index.php
 * Summary page for the logged-in user:
 *   - total income, total expenses, current balance
 *   - this month's income and expenses
 *   - 5 most recent transactions
 * Every number comes from the database and every query has WHERE user_id = ?
 */
require_once __DIR__ . '/../includes/auth_check.php';

$userId = current_user_id();

// ---------- 1. All-time totals ----------
// SUM(CASE WHEN ...) adds only the rows of one type.
// COALESCE(..., 0) turns "no rows" (NULL) into 0.
// The balance is calculated in SQL too: total income - total expenses.
$stmt = $pdo->prepare(
    "SELECT
        COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS total_expense,
        COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0)
          - COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS balance,
        COUNT(*) AS transaction_count
     FROM transactions
     WHERE user_id = ?"
);
$stmt->execute([$userId]);
$totals = $stmt->fetch();

// ---------- 2. This month's totals ----------
$monthStart = date('Y-m-01');   // first day of this month
$monthEnd   = date('Y-m-t');    // last day of this month

$stmt = $pdo->prepare(
    "SELECT
        COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0) AS month_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS month_expense
     FROM transactions
     WHERE user_id = ? AND transaction_date BETWEEN ? AND ?"
);
$stmt->execute([$userId, $monthStart, $monthEnd]);
$month = $stmt->fetch();

// ---------- 3. Recent transactions ----------
$stmt = $pdo->prepare(
    'SELECT t.transaction_id, t.amount, t.type, t.description, t.transaction_date, c.category_name
     FROM transactions t
     JOIN categories c ON c.category_id = t.category_id
     WHERE t.user_id = ?
     ORDER BY t.transaction_date DESC, t.transaction_id DESC
     LIMIT 5'
);
$stmt->execute([$userId]);
$recent = $stmt->fetchAll();

$hasTransactions = ((int) $totals['transaction_count'] > 0);
$monthNet        = $month['month_income'] - $month['month_expense'];

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="muted">Welcome, <?php echo e($_SESSION['full_name']); ?></p>
    </div>
    <div class="btn-group">
        <a class="btn btn-success" href="<?php echo BASE_URL; ?>/transactions/add.php?type=income">+ Add Income</a>
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/transactions/add.php?type=expense">+ Add Expense</a>
    </div>
</div>

<!-- Totals -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Income</div>
        <div class="stat-value stat-income"><?php echo e(format_money($totals['total_income'])); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Expenses</div>
        <div class="stat-value stat-expense"><?php echo e(format_money($totals['total_expense'])); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Current Balance</div>
        <div class="stat-value <?php echo $totals['balance'] < 0 ? 'stat-expense' : 'stat-balance'; ?>">
            <?php echo e(format_money($totals['balance'])); ?>
        </div>
        <?php if ($totals['balance'] < 0): ?>
            <div class="stat-note">Your expenses are higher than your income.</div>
        <?php endif; ?>
    </div>
</div>

<!-- This month -->
<div class="card">
    <h2>This month (<?php echo e(date('F Y')); ?>)</h2>
    <div class="month-summary">
        <div><span class="muted">Income</span><br><strong class="amount-income"><?php echo e(format_money($month['month_expense'])); ?></strong></div>
        <div><span class="muted">Expenses</span><br><strong class="amount-expense"><?php echo e(format_money($month['month_expense'])); ?></strong></div>
        <div><span class="muted">Net for the month</span><br><strong class="<?php echo $monthNet < 0 ? 'amount-expense' : 'amount-income'; ?>"><?php echo e(format_money($monthNet)); ?></strong></div>
    </div>
</div>

<!-- Recent transactions -->
<div class="card">
    <div class="card-header">
        <h2>Recent transactions</h2>
        <?php if ($hasTransactions): ?>
            <a href="<?php echo BASE_URL; ?>/transactions/index.php">View all</a>
        <?php endif; ?>
    </div>

<?php if (!$hasTransactions): ?>
    <p class="empty-state">You have not recorded any transactions yet.<br>
       Start with "Add Income" or "Add Expense" and your totals will appear here.</p>
<?php else: ?>
    <div class="table-wrap">
    <table>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Category</th>
            <th>Description</th>
            <th class="text-right">Amount</th>
        </tr>
        <?php foreach ($recent as $t): ?>
        <tr>
            <td><?php echo e(date('d M Y', strtotime($t['transaction_date']))); ?></td>
            <td><span class="badge badge-<?php echo e($t['type']); ?>"><?php echo e(ucfirst($t['type'])); ?></span></td>
            <td><?php echo e($t['category_name']); ?></td>
            <td><?php echo e($t['description']); ?></td>
            <td class="text-right amount-<?php echo e($t['type']); ?>">
                <?php echo $t['type'] === 'income' ? '+' : '-'; ?> <?php echo e(format_money($t['amount'])); ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
