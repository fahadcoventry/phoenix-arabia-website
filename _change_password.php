<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Verify current password
    $stmt = db()->prepare("SELECT password_hash FROM staff_users WHERE id = ? LIMIT 1");
    $stmt->execute([current_staff()['id']]);
    $u = $stmt->fetch();

    if (!$u || !password_verify($current, $u['password_hash'])) {
        $form_errors[] = 'Current password is incorrect.';
    }
    if (strlen($new) < 10) {
        $form_errors[] = 'New password must be at least 10 characters.';
    }
    if ($new !== $confirm) {
        $form_errors[] = 'New password and confirmation do not match.';
    }
    if ($new === $current) {
        $form_errors[] = 'New password must be different from current.';
    }

    if (empty($form_errors)) {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        db()->prepare("UPDATE staff_users SET password_hash = ? WHERE id = ?")
            ->execute([$hash, current_staff()['id']]);
        flash_set('success', 'Password changed successfully.');
        header('Location: dashboard.php');
        exit;
    }
}

layout_start('Change Password', '');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1>🔑 Change Password</h1>
    <p>Update your account password</p>
  </div>
  <a class="btn light" href="dashboard.php">← Back to Dashboard</a>
</div>

<?php if (!empty($form_errors)): ?>
  <div class="flash flash-error">
    <ul style="margin: 0; padding-left: 20px;">
      <?php foreach ($form_errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" class="form-card" style="max-width: 480px;" autocomplete="off">
  <?= csrf_field() ?>

  <div class="form-field">
    <label>Current Password *</label>
    <input type="password" name="current_password" required autocomplete="current-password">
  </div>

  <div class="form-field">
    <label>New Password *</label>
    <input type="password" name="new_password" required autocomplete="new-password" minlength="10">
    <div class="hint">Minimum 10 characters. Use a unique strong password.</div>
  </div>

  <div class="form-field">
    <label>Confirm New Password *</label>
    <input type="password" name="confirm_password" required autocomplete="new-password" minlength="10">
  </div>

  <div class="form-actions">
    <button type="submit" class="btn blue">Change Password</button>
    <a class="btn light" href="dashboard.php">Cancel</a>
  </div>
</form>

<?php layout_end(); ?>
