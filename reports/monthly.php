<?php
/*
 * reports/monthly.php
 * Monthly summary for one year: income, expenses and balance for each month,
 * plus a bar chart (income vs expenses). All numbers come from the database.
 */
require_once __DIR__ . '/../includes/auth_check.php';

$userId      = current_user_id();
$currentYear = (int) date('Y');

// Years that have data for this user (plus the current year) for the dropdown
$stmt = $pdo->prepare('SELECT DISTINCT YEAR(transaction_date) AS y FROM transactions WHERE user_id = ? ORDER BY y DESC');
$stmt->execute([$userId]);
$years = array_map('intval', array_column($stmt->fetchAll(), 'y'));
if (!in_array($currentYear, $years, true)) {
    array_unshift($years, $currentYear);
}

// The selected year must be one of the years in the list, otherwise use the current year
$year = read_id(get_text('year'));
if (!in_array($year, $years, true)) {
    $year = $currentYear;
}

// One query: GROUP BY month. Balance = income - expenses (calculated by MySQL).
$stmt = $pdo->prepare(
    "SELECT MONTH(transaction_date) AS m,
            COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0) AS income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS expense,
            COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0)
              - COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS balance
     FROM transactions
     WHERE user_id = ? AND transaction_date BETWEEN ? AND ?
     GROUP BY MONTH(transaction_date)"
);
$stmt->execute([$userId, $year . '-01-01', $year . '-12-31']);
$byMonth = [];
foreach ($stmt->fetchAll() as $row) {
    $byMonth[(int) $row['m']] = $row;
}

// Year totals
$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0) AS income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS expense,
            COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0)
              - COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS balance,
            COUNT(*) AS n
     FROM transactions
     WHERE user_id = ? AND transaction_date BETWEEN ? AND ?"
);
$stmt->execute([$userId, $year . '-01-01', $year . '-12-31']);
$yearTotals = $stmt->fetch();

// Data for the chart (12 months, months without data are 0)
$labels = []; $incomeData = []; $expenseData = [];
for ($m = 1; $m <= 12; $m++) {
    $labels[]      = substr(month_name($m), 0, 3);
    $incomeData[]  = isset($byMonth[$m]) ? (float) $byMonth[$m]['income']  : 0;
    $expenseData[] = isset($byMonth[$m]) ? (float) $byMonth[$m]['expense'] : 0;
}

$pageTitle   = 'Monthly Summary';
$pageScripts = ['/assets/js/chart.umd.js', '/assets/js/charts.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1>Reports</h1>
<?php require __DIR__ . '/../includes/report_tabs.php'; ?>

<form method="get" action="" class="card inline-form">
    <label for="year">Year</label>
    <select id="year" name="year">
        <?php foreach ($years as $y): ?>
            <option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Show</button>
</form>

<?php if ((int) $yearTotals['n'] === 0): ?>
    <div class="card"><p class="empty-state">No transactions were recorded in <?php echo $year; ?>.</p></div>
<?php else: ?>
    <div class="card">
        <h2>Income vs Expenses, <?php echo $year; ?></h2>
        <div class="chart-box">
            <canvas id="monthlyChart"
                    data-labels="<?php echo e(json_encode($labels)); ?>"
                    data-income="<?php echo e(json_encode($incomeData)); ?>"
                    data-expense="<?php echo e(json_encode($expenseData)); ?>"></canvas>
        </div>
    </div>

    <div class="card">
        <h2>Monthly summary, <?php echo $year; ?></h2>
        <div class="table-wrap">
        <table>
            <tr>
                <th>Month</th>
                <th class="text-right">Total Income</th>
                <th class="text-right">Total Expenses</th>
                <th class="text-right">Balance</th>
            </tr>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <?php $row = $byMonth[$m] ?? ['income' => 0, 'expense' => 0, 'balance' => 0]; ?>
                <tr>
                    <td><?php echo e(month_name($m)); ?></td>
                    <td class="text-right"><?php echo e(format_money($row['income'])); ?></td>
                    <td class="text-right"><?php echo e(format_money($row['expense'])); ?></td>
                    <td class="text-right <?php echo $row['balance'] < 0 ? 'amount-expense' : ''; ?>"><?php echo e(format_money($row['balance'])); ?></td>
                </tr>
            <?php endfor; ?>
            <tr class="total-row">
                <td>Total <?php echo $year; ?></td>
                <td class="text-right"><?php echo e(format_money($yearTotals['income'])); ?></td>
                <td class="text-right"><?php echo e(format_money($yearTotals['expense'])); ?></td>
                <td class="text-right <?php echo $yearTotals['balance'] < 0 ? 'amount-expense' : ''; ?>"><?php echo e(format_money($yearTotals['balance'])); ?></td>
            </tr>
        </table>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
