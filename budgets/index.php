<?php
/*
 * budgets/index.php
 * Shows the budgets of one month and compares each with the ACTUAL spending
 * calculated from the transactions table:
 *      Remaining = Budget - Spent
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/budget_functions.php';

$userId      = current_user_id();
$currentYear = (int) date('Y');

// ----- Selected month and year (default: this month) -----
$month = read_id(get_text('month'));
if ($month < 1 || $month > 12) {
    $month = (int) date('n');
}
$year = read_id(get_text('year'));
if ($year < 2000 || $year > $currentYear + 5) {
    $year = $currentYear;
}
$startDate = sprintf('%04d-%02d-01', $year, $month);
$endDate   = date('Y-m-t', strtotime($startDate));

// ----- Budgets with spent / remaining (spent = this user's expenses in that category and month) -----
$stmt = $pdo->prepare(
    "SELECT b.budget_id, b.budget_amount, c.category_name,
            COALESCE((SELECT SUM(t.amount) FROM transactions t
                      WHERE t.user_id = b.user_id AND t.category_id = b.category_id
                        AND t.type = 'expense' AND t.transaction_date BETWEEN ? AND ?), 0) AS spent
     FROM budgets b
     JOIN categories c ON c.category_id = b.category_id
     WHERE b.user_id = ? AND b.`month` = ? AND b.`year` = ?
     ORDER BY c.category_name"
);
$stmt->execute([$startDate, $endDate, $userId, $month, $year]);
$budgets = $stmt->fetchAll();

// ----- Totals for the month (calculated by MySQL) -----
$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(x.budget_amount), 0) AS total_budget,
            COALESCE(SUM(x.spent), 0)         AS total_spent,
            COALESCE(SUM(x.budget_amount), 0) - COALESCE(SUM(x.spent), 0) AS total_remaining
     FROM (SELECT b.budget_amount,
                  COALESCE((SELECT SUM(t.amount) FROM transactions t
                            WHERE t.user_id = b.user_id AND t.category_id = b.category_id
                              AND t.type = 'expense' AND t.transaction_date BETWEEN ? AND ?), 0) AS spent
           FROM budgets b
           WHERE b.user_id = ? AND b.`month` = ? AND b.`year` = ?) x"
);
$stmt->execute([$startDate, $endDate, $userId, $month, $year]);
$totals = $stmt->fetch();

$pageTitle = 'Budgets';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <h1>Budgets</h1>
    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/budgets/add.php?month=<?php echo $month; ?>&amp;year=<?php echo $year; ?>">+ Set Budget</a>
</div>

<form method="get" action="" class="card inline-form">
    <label for="month">Month</label>
    <select id="month" name="month">
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?php echo $m; ?>" <?php echo $m === $month ? 'selected' : ''; ?>><?php echo e(month_name($m)); ?></option>
        <?php endfor; ?>
    </select>
    <label for="year">Year</label>
    <select id="year" name="year">
        <?php for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++): ?>
            <option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
        <?php endfor; ?>
    </select>
    <button type="submit" class="btn btn-primary">Show</button>
</form>

<?php if (!$budgets): ?>
    <div class="card">
        <p class="empty-state">No budgets are set for <?php echo e(month_name($month) . ' ' . $year); ?>.<br>
           Click "Set Budget" to plan how much you want to spend on a category.</p>
    </div>
<?php else: ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Budget (<?php echo e(month_name($month)); ?>)</div>
            <div class="stat-value stat-balance"><?php echo e(format_money($totals['total_budget'])); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Spent (budgeted categories)</div>
            <div class="stat-value stat-expense"><?php echo e(format_money($totals['total_spent'])); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><?php echo $totals['total_remaining'] < 0 ? 'Over budget by' : 'Remaining'; ?></div>
            <div class="stat-value <?php echo $totals['total_remaining'] < 0 ? 'stat-expense' : 'stat-income'; ?>">
                <?php echo e(format_money(abs($totals['total_remaining']))); ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
        <table>
            <tr>
                <th>Category</th>
                <th class="text-right">Budget</th>
                <th class="text-right">Spent</th>
                <th class="text-right">Remaining</th>
                <th>Usage</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($budgets as $b): ?>
            <?php
                $remaining = $b['budget_amount'] - $b['spent'];
                $over      = ($b['spent'] > $b['budget_amount']);
                $percent   = ((float) $b['spent'] / (float) $b['budget_amount']) * 100;   // for the progress bar only
            ?>
            <tr>
                <td><?php echo e($b['category_name']); ?></td>
                <td class="text-right"><?php echo e(format_money($b['budget_amount'])); ?></td>
                <td class="text-right"><?php echo e(format_money($b['spent'])); ?></td>
                <td class="text-right <?php echo $over ? 'amount-expense' : 'amount-income'; ?>"><?php echo e(format_money($remaining)); ?></td>
                <td class="usage-cell">
                    <div class="progress"><div class="progress-bar <?php echo $over ? 'progress-over' : ''; ?>" style="width: <?php echo (int) min(100, round($percent)); ?>%"></div></div>
                    <span class="muted"><?php echo (int) round($percent); ?>% used</span>
                </td>
                <td>
                    <?php if ($over): ?>
                        <span class="badge badge-expense">Over budget by <?php echo e(format_money($b['spent'] - $b['budget_amount'])); ?></span>
                    <?php else: ?>
                        <span class="badge badge-income">Within budget</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <a class="btn btn-small btn-light" href="<?php echo BASE_URL; ?>/budgets/edit.php?id=<?php echo (int) $b['budget_id']; ?>">Edit</a>
                    <form method="post" action="<?php echo BASE_URL; ?>/budgets/delete.php"
                          data-confirm="Delete the <?php echo e($b['category_name']); ?> budget for <?php echo e(month_name($month) . ' ' . $year); ?>?">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="budget_id" value="<?php echo (int) $b['budget_id']; ?>">
                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <p class="muted">"Spent" is the total of your expense transactions in that category during the month.</p>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
