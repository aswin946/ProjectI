<?php
/*
 * includes/navbar.php
 * Navigation bar shown on all pages after login.
 */

// Returns 'active' when the current page URL contains $path (to highlight the link)
function nav_active(string $path): string
{
    return strpos($_SERVER['SCRIPT_NAME'], $path) !== false ? 'active' : '';
}
?>
<nav class="navbar" id="navbar">
    <a class="brand" href="<?php echo BASE_URL; ?>/dashboard/index.php">PEMS</a>
    <!-- Menu button: only visible on small screens (see assets/js/script.js) -->
    <button type="button" class="nav-toggle" id="navToggle" aria-label="Open menu">&#9776;</button>

    <ul class="nav-links">
        <li><a class="<?php echo nav_active('/dashboard/'); ?>" href="<?php echo BASE_URL; ?>/dashboard/index.php">Dashboard</a></li>
        <li><a class="<?php echo nav_active('/transactions/'); ?>" href="<?php echo BASE_URL; ?>/transactions/index.php">Transactions</a></li>
        <li><a class="<?php echo nav_active('/categories/'); ?>" href="<?php echo BASE_URL; ?>/categories/index.php">Categories</a></li>
        <li><a class="<?php echo nav_active('/budgets/'); ?>" href="<?php echo BASE_URL; ?>/budgets/index.php">Budgets</a></li>
        <li><a class="<?php echo nav_active('/reports/'); ?>" href="<?php echo BASE_URL; ?>/reports/monthly.php">Reports</a></li>
        <li><a class="<?php echo nav_active('/auth/profile.php'); ?>" href="<?php echo BASE_URL; ?>/auth/profile.php">My Profile</a></li>
    </ul>

    <div class="nav-user">
        <span>Hello, <?php echo e($_SESSION['full_name'] ?? ''); ?></span>
        <!-- Logout is a POST form so it cannot be triggered by a simple link or image -->
        <form method="post" action="<?php echo BASE_URL; ?>/auth/logout.php">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-small btn-light">Logout</button>
        </form>
    </div>
</nav>
