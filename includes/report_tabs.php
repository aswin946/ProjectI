<?php
/* includes/report_tabs.php - small tab bar shown at the top of both report pages */
?>
<div class="tabs">
    <a class="<?php echo nav_active('/reports/monthly.php'); ?>" href="<?php echo BASE_URL; ?>/reports/monthly.php">Monthly Summary</a>
    <a class="<?php echo nav_active('/reports/category.php'); ?>" href="<?php echo BASE_URL; ?>/reports/category.php">Category-wise Expenses</a>
</div>
