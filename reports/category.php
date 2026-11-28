<?php
/*
 * reports/category.php
 * Category-wise EXPENSE summary for a chosen month (or a whole year),
 * with a doughnut chart. All numbers come from the database.
 */
require_once __DIR__ . '/../includes/auth_check.php';

$userId      = current_user_id();
$currentYear = (int) date('Y');

// ----- Read and validate the selected period -----
$year  = read_id(get_text('year'));
if ($year < 2000 || $year > $currentYear) {
    $year = $currentYear;
}
// month 0 = whole year; otherwise 1..12. When no month is given, use the current month.
$monthText = get_text('month');
if ($monthText === '') {
    $month = (int) date('n');
} else {
    $month = ctype_digit($monthText) ? (int) $monthText : (int) date('n');
    if ($month < 0 || $month > 12) {
        $month = (int) date('n');
    }
}

if ($month === 0) {
    $startDate   = $year . '-01-01';
    $endDate     = $year . '-12-31';
    $periodLabel = 'Whole year ' . $year;
} else {
    $startDate   = sprintf('%04d-%02d-01', $year, $month);
    $endDate     = date('Y-m-t', strtotime($startDate));
    $periodLabel = month_name($month) . ' ' . $year;
}

// ----- Expense total per category (largest first) -----
$stmt = $pdo->prepare(
    "SELECT c.category_name, SUM(t.amount) AS total, COUNT(*) AS tx_count
     FROM transactions t
     JOIN categories c ON c.category_id = t.category_id
     WHERE t.user_id = ? AND t.type = 'expense' AND t.transaction_date BETWEEN ? AND ?
     GROUP BY c.category_id, c.category_name
     ORDER BY total DESC"
);
$stmt->execute([$userId, $startDate, $endDate]);
$rows = $stmt->fetchAll();

// ----- Grand total of the period -----
$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(amount), 0) FROM transactions
     WHERE user_id = ? AND type = 'expense' AND transaction_date BETWEEN ? AND ?"
);
$stmt->execute([$userId, $startDate, $endDate]);
$grandTotal = $stmt->fetchColumn();

// ----- Data for the chart -----
$labels = []; $values = [];
foreach ($rows as $r) {
    $labels[] = $r['category_name'];
    $values[] = (float) $r['total'];
}

$pageTitle   = 'Category-wise Expenses';
$pageScripts = ['/assets/js/chart.umd.js', '/assets/js/charts.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1>Reports</h1>
<?php require __DIR__ . '/../includes/report_tabs.php'; ?>

<form method="get" action="" class="card inline-form">
    <label for="month">Month</label>
    <select id="month" name="month">
        <option value="0" <?php echo $month === 0 ? 'selected' : ''; ?>>Whole year</option>
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?php echo $m; ?>" <?php echo $m === $month ? 'selected' : ''; ?>><?php echo e(month_name($m)); ?></option>
        <?php endfor; ?>
    </select>
    <label for="year">Year</label>
    <select id="year" name="year">
        <?php for ($y = $currentYear; $y >= $currentYear - 5; $y--): ?>
            <option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
        <?php endfor; ?>
    </select>
    <button type="submit" class="btn btn-primary">Show</button>
</form>

<?php if (!$rows): ?>
    <div class="card"><p class="empty-state">No expenses were recorded for <?php echo e($periodLabel); ?>.</p></div>
<?php else: ?>
    <div class="grid-2">
        <div class="card">
            <h2>Expense distribution, <?php echo e($periodLabel); ?></h2>
            <div class="chart-box">
                <canvas id="categoryChart"
                        data-labels="<?php echo e(json_encode($labels)); ?>"
                        data-values="<?php echo e(json_encode($values)); ?>"></canvas>
            </div>
        </div>

        <div class="card">
            <h2>Category-wise expenses</h2>
            <div class="table-wrap">
            <table>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Share</th>
                    <th class="text-right">Transactions</th>
                </tr>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo e($r['category_name']); ?></td>
                    <td class="text-right"><?php echo e(format_money($r['total'])); ?></td>
                    <td class="text-right"><?php echo e(number_format(((float) $r['total'] / (float) $grandTotal) * 100, 1)); ?>%</td>
                    <td class="text-right"><?php echo (int) $r['tx_count']; ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td>Total expenses</td>
                    <td class="text-right"><?php echo e(format_money($grandTotal)); ?></td>
                    <td class="text-right">100.0%</td>
                    <td class="text-right"></td>
                </tr>
            </table>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
