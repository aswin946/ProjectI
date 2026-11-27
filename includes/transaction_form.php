<?php
/*
 * includes/transaction_form.php
 * The transaction form, shared by add.php and edit.php.
 * The page that includes it must define:
 *   $values       - current field values (type, category_id, amount, description, transaction_date)
 *   $categories   - list of categories the user can choose
 *   $errors       - list of error messages
 *   $submitLabel  - text of the submit button
 */
?>
<div class="card form-card">
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endforeach; ?>

    <form method="post" action="">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label for="type">Type</label>
            <select id="type" name="type" required>
                <option value="income"  <?php echo $values['type'] === 'income'  ? 'selected' : ''; ?>>Income</option>
                <option value="expense" <?php echo $values['type'] === 'expense' ? 'selected' : ''; ?>>Expense</option>
            </select>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <!-- data-type lets JavaScript (assets/js/script.js) show only the categories of the chosen type -->
            <select id="category_id" name="category_id" required>
                <option value="">-- Select category --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo (int) $c['category_id']; ?>"
                            data-type="<?php echo e($c['category_type']); ?>"
                            <?php echo (int) $values['category_id'] === (int) $c['category_id'] ? 'selected' : ''; ?>>
                        <?php echo e($c['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount (Rs.)</label>
            <input type="number" id="amount" name="amount" value="<?php echo e($values['amount']); ?>"
                   step="0.01" min="0.01" max="9999999999.99" required>
        </div>

        <div class="form-group">
            <label for="transaction_date">Date</label>
            <input type="date" id="transaction_date" name="transaction_date" value="<?php echo e($values['transaction_date']); ?>"
                   min="2000-01-01" max="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description <span class="muted">(optional)</span></label>
            <input type="text" id="description" name="description" value="<?php echo e($values['description']); ?>" maxlength="255">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo e($submitLabel); ?></button>
            <a class="btn btn-light" href="<?php echo BASE_URL; ?>/transactions/index.php">Cancel</a>
        </div>
    </form>
</div>
