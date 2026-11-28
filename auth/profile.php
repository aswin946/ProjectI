<?php
/*
 * auth/profile.php
 * "Manage personal account information" (approved in the proposal):
 * change full name and change password. The email cannot be changed.
 */
require_once __DIR__ . '/../includes/auth_check.php';

$userId = current_user_id();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $action = post_text('action');

        // ----- Form 1: update name -----
        if ($action === 'update_name') {
            $fullName = post_text('full_name');
            if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 100) {
                $errors[] = 'Full name must be between 2 and 100 characters.';
            } else {
                $stmt = $pdo->prepare('UPDATE users SET full_name = ? WHERE user_id = ?');
                $stmt->execute([$fullName, $userId]);
                $_SESSION['full_name'] = $fullName;
                set_flash('success', 'Profile updated.');
                redirect('/auth/profile.php');
            }
        }

        // ----- Form 2: change password -----
        if ($action === 'change_password') {
            $current = post_raw('current_password');
            $new     = post_raw('new_password');
            $confirm = post_raw('confirm_password');

            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE user_id = ?');
            $stmt->execute([$userId]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($current, $row['password_hash'])) {
                $errors[] = 'Current password is incorrect.';
            }
            if (strlen($new) < 8 || strlen($new) > 72) {
                $errors[] = 'New password must be between 8 and 72 characters.';
            }
            if ($new !== $confirm) {
                $errors[] = 'New password and confirm password do not match.';
            }

            if (!$errors) {
                $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
                $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
                set_flash('success', 'Password changed successfully.');
                redirect('/auth/profile.php');
            }
        }
    }
}

// Load the user's current details (always WHERE user_id = the logged-in user)
$stmt = $pdo->prepare('SELECT full_name, email, created_at FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$pageTitle = 'My Profile';
require __DIR__ . '/../includes/header.php';
?>
<h1>My Profile</h1>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?php echo e($error); ?></div>
<?php endforeach; ?>

<div class="grid-2">
    <div class="card">
        <h2>Account information</h2>
        <form method="post" action="">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_name">
            <div class="form-group">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo e($user['full_name']); ?>" required maxlength="100">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo e($user['email']); ?>" disabled>
            </div>
            <p class="muted">Member since <?php echo e(date('j M Y', strtotime($user['created_at']))); ?></p>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </form>
    </div>

    <div class="card">
        <h2>Change password</h2>
        <form method="post" action="">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label for="current_password">Current password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New password</label>
                <input type="password" id="new_password" name="new_password" required minlength="8" maxlength="72">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm new password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" maxlength="72">
            </div>
            <button type="submit" class="btn btn-primary">Change password</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
