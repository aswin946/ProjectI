<?php
/*
 * transactions/index.php
 * Transaction history of the logged-in user with search, filters and paging.
 *
 * Filters come from the URL, for example:
 *   index.php?type=expense&category_id=3&date_from=2026-11-01&date_to=2026-11-30&q=lunch&page=2
 * The filtering is done by MySQL (WHERE ... LIKE ...), not by JavaScript.
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/transaction_functions.php';

$userId  = current_user_id();
$perPage = 15;

// 1. Read the filters and build the WHERE condition
list($filters, $filterErrors) = read_transaction_filters();
list($whereSql, $params)      = build_transaction_where($filters, $userId);

// 2. How many rows match, and what are their income / expense totals?
$stmt = $pdo->prepare(
    "SELECT COUNT(*) AS row_count,
            COALESCE(SUM(CASE WHEN t.type = 'income'  THEN t.amount END), 0) AS income_total,
            COALESCE(SUM(CASE WHEN t.type = 'expense' THEN t.amount END), 0) AS expense_total
     FROM transactions t
     WHERE $whereSql"
);
$stmt->execute($params);
$summary = $stmt->fetch();
$rowCount = (int) $summary['row_count'];

// 3. Paging: work out which page to show (page number is forced to be a valid integer)
$totalPages = max(1, (int) ceil($rowCount / $perPage));
$page       = min(max(1, read_id(get_text('page'))), $totalPages);
$offset     = ($page - 1) * $perPage;

// 4. Load only the rows of the current page.
// $perPage and $offset are integers calculated above, never text from the user.
$stmt = $pdo->prepare(
    "SELECT t.transaction_id, t.amount, t.type, t.description, t.transaction_date, c.category_name
     FROM transactions t
     JOIN categories c ON c.category_id = t.category_id
     WHERE $whereSql
     ORDER BY t.transaction_date DESC, t.transaction_id DESC
     LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// 5. Categories for the filter dropdown (this user's only)
$stmt = $pdo->prepare('SELECT category_id, category_name, category_type FROM categories WHERE user_id = ? ORDER BY category_type DESC, category_name');
$stmt->execute([$userId]);
$allCategories = $stmt->fetchAll();

// Helper: builds the "?type=...&page=2" part for the paging links, keeping the filters
function page_link(array $filters, int $page): string
{
    $query = array_filter($filters, function ($v) { return $v !== '' && $v !== 0; });
    $query['page'] = $page;
    return BASE_URL . '/transactions/index.php?' . http_build_query($query);
}

$filtersActive = ($filters['type'] !== '' || $filters['category_id'] > 0 || $filters['date_from'] !== ''
                  || $filters['date_to'] !== '' || $filters['q'] !== '');

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

<?php foreach ($filterErrors as $error): ?>
    <div class="alert alert-error"><?php echo e($error); ?></div>
<?php endforeach; ?>

<!-- Search and filter form (GET: the filters appear in the address bar) -->
<form method="get" action="" class="card filter-form">
    <div class="form-group">
        <label for="q">Search description</label>
        <input type="text" id="q" name="q" value="<?php echo e($filters['q']); ?>" maxlength="100" placeholder="e.g. lunch">
    </div>
    <div class="form-group">
        <label for="type">Type</label>
        <select id="type" name="type">
            <option value="">All</option>
            <option value="income"  <?php echo $filters['type'] === 'income'  ? 'selected' : ''; ?>>Income</option>
            <option value="expense" <?php echo $filters['type'] === 'expense' ? 'selected' : ''; ?>>Expense</option>
        </select>
    </div>
    <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="">All</option>
            <?php foreach ($allCategories as $c): ?>
                <option value="<?php echo (int) $c['category_id']; ?>" <?php echo $filters['category_id'] === (int) $c['category_id'] ? 'selected' : ''; ?>>
                    <?php echo e($c['category_name'] . ' (' . ucfirst($c['category_type']) . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="date_from">From date</label>
        <input type="date" id="date_from" name="date_from" value="<?php echo e($filters['date_from']); ?>">
    </div>
    <div class="form-group">
        <label for="date_to">To date</label>
        <input type="date" id="date_to" name="date_to" value="<?php echo e($filters['date_to']); ?>">
    </div>
    <div class="form-group filter-buttons">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a class="btn btn-light" href="<?php echo BASE_URL; ?>/transactions/index.php">Clear</a>
    </div>
</form>

<div class="card">
<?php if ($rowCount === 0): ?>
    <p class="empty-state">
        <?php if ($filtersActive): ?>
            No transactions match your filters.
        <?php else: ?>
            No transactions yet. Use "Add Income" or "Add Expense" to record your first one.
        <?php endif; ?>
    </p>
<?php else: ?>
    <p class="result-summary">
        <strong><?php echo $rowCount; ?></strong> transaction(s)<?php echo $filtersActive ? ' match your filters' : ''; ?>
        &nbsp;|&nbsp; Income: <span class="amount-income"><?php echo e(format_money($summary['income_total'])); ?></span>
        &nbsp;|&nbsp; Expenses: <span class="amount-expense"><?php echo e(format_money($summary['expense_total'])); ?></span>
    </p>
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

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?php echo e(page_link($filters, $page - 1)); ?>">&laquo; Previous</a>
        <?php endif; ?>
        <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
        <?php if ($page < $totalPages): ?>
            <a href="<?php echo e(page_link($filters, $page + 1)); ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
