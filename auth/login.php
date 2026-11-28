<?php
/*
 * auth/login.php
 * Checks email and password, then creates the login session.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

if (is_logged_in()) {
    redirect('/dashboard/index.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email    = strtolower(post_text('email'));
        $password = post_raw('password');

        try {
            $stmt = $pdo->prepare(
                'SELECT user_id, full_name, password_hash, status FROM users WHERE email = ?'
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Same message for "no such email" and "wrong password" so attackers learn nothing
            if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);   // new session ID after login (prevents session fixation)
                $_SESSION['user_id']   = (int) $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                redirect('/dashboard/index.php');
            } else {
                $error = 'Invalid login credentials.';
            }
        } catch (PDOException $e) {
            error_log('PEMS login error: ' . $e->getMessage());
            $error = 'Unable to log in right now. Please try again.';
            if (APP_DEBUG) {
                $error .= ' Debug: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Login';
$bodyClass = 'auth-page';
require __DIR__ . '/../includes/header.php';
?>
<div class="auth-box">
    <h1>Login</h1>
    <p class="muted">Personal Expense Management System</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <p class="auth-link">New user? <a href="<?php echo BASE_URL; ?>/auth/register.php">Create an account</a></p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
