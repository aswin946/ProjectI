<?php
/*
 * auth/register.php
 * Creates a new user account and the user's default categories.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

if (is_logged_in()) {
    redirect('/dashboard/index.php');
}

$errors   = [];
$fullName = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $fullName = post_text('full_name');
        $email    = strtolower(post_text('email'));
        $password = post_raw('password');
        $confirm  = post_raw('confirm_password');

        // ----- Server-side validation -----
        if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 100) {
            $errors[] = 'Full name must be between 2 and 100 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8 || strlen($password) > 72) {
            $errors[] = 'Password must be between 8 and 72 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Password and confirm password do not match.';
        }

        // ----- Save the user -----
        if (!$errors) {
            try {
                // A database transaction: either the user AND the categories are saved, or nothing is.
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    'INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)'
                );
                $stmt->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT)]);

                $newUserId = (int) $pdo->lastInsertId();
                create_default_categories($pdo, $newUserId);

                $pdo->commit();

                set_flash('success', 'Registration successful. Please log in.');
                redirect('/auth/login.php');
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                // MySQL error 1062 = duplicate value in a UNIQUE column (the email)
                if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                    $errors[] = 'This email is already registered.';
                } else {
                    error_log('PEMS register error: ' . $e->getMessage());
                    $errors[] = 'Unable to register. Please try again.';
                    if (APP_DEBUG) {
                        $errors[] = 'Debug: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$pageTitle = 'Register';
$bodyClass = 'auth-page';
require __DIR__ . '/../includes/header.php';
?>
<div class="auth-box">
    <h1>Create Account</h1>
    <p class="muted">Personal Expense Management System</p>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endforeach; ?>

    <form method="post" action="">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo e($fullName); ?>" required maxlength="100">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required maxlength="150">
        </div>
        <div class="form-group">
            <label for="password">Password <span class="muted">(at least 8 characters)</span></label>
            <input type="password" id="password" name="password" required minlength="8" maxlength="72">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" maxlength="72">
        </div>

        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>

    <p class="auth-link">Already have an account? <a href="<?php echo BASE_URL; ?>/auth/login.php">Login</a></p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
