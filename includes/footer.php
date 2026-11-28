</main>
<footer class="site-footer">
    Personal Expense Management System | BCA Project I
</footer>
<script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
<?php
// A page can load extra scripts by setting $pageScripts before including this file.
// Example (reports): $pageScripts = ['/assets/js/chart.umd.js', '/assets/js/charts.js'];
foreach (($pageScripts ?? []) as $scriptPath): ?>
<script src="<?php echo BASE_URL . e($scriptPath); ?>"></script>
<?php endforeach; ?>
</body>
</html>
