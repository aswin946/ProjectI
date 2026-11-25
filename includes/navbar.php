<?php
/*
 * includes/navbar.php
 * Navigation bar shown on protected pages. More links are added in later phases.
 */

// Returns 'active' when the current page URL contains $path (to highlight the link)
function nav_active(string $path): string
{
    return strpos($_SERVER['SCRIPT_NAME'], $path) !== false ? 'active' : '';
}
?>
<nav class="navbar">
    <a class="brand" href="<?php echo BASE_URL; ?>/dashboard/index.php">PEMS</a>
    <ul class="nav-links">
        <li><a class="<?php echo nav_active('/dashboard/'); ?>" href="<?php echo BASE_URL; ?>/dashboard/index.php">Dashboard</a></li>
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
