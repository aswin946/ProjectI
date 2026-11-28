<?php
/*
 * includes/budget_form.php
 * The budget form, shared by budgets/add.php and budgets/edit.php.
 * The page must define: $values, $categories, $errors, $submitLabel
 */
$currentYear = (int) date('Y');
$yearFrom    = min($currentYear - 1, (int) $values['year']);
$yearTo      = max($currentYear + 2, (int) $values['year']);
?>
<div class="card form-card">
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endforeach; ?>

    <form method="post" action="">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label for="category_id">Expense category</label>
            <select id="category_id" name="category_id" required>
                <option value="">-- Select category --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo (int) $c['category_id']; ?>" <?php echo (int) $values['category_id'] === (int) $c['category_id'] ? 'selected' : ''; ?>>
                        <?php echo e($c['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="month">Month</label>
            <select id="month" name="month" required>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo (int) $values['month'] === $m ? 'selected' : ''; ?>><?php echo e(month_name($m)); ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="year">Year</label>
            <select id="year" name="year" required>
                <?php for ($y = $yearFrom; $y <= $yearTo; $y++): ?>
                    <option value="<?php echo $y; ?>" <?php echo (int) $values['year'] === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="budget_amount">Budget amount (Rs.)</label>
            <input type="number" id="budget_amount" name="budget_amount" value="<?php echo e($values['budget_amount']); ?>"
                   step="0.01" min="0.01" max="9999999999.99" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo e($submitLabel); ?></button>
            <a class="btn btn-light" href="<?php echo BASE_URL; ?>/budgets/index.php?month=<?php echo (int) $values['month']; ?>&amp;year=<?php echo (int) $values['year']; ?>">Cancel</a>
        </div>
    </form>
</div>
