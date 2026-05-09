<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$success = false;

if (empty($token)) {
    $errors[] = 'Invalid or missing reset token.';
}

// Validate token
$reset_record = null;
if (empty($errors)) {
    $stmt = db()->prepare("
        SELECT cpr.*, ca.email, ca.full_name
        FROM customer_password_resets cpr
        INNER JOIN customer_accounts ca ON ca.id = cpr.customer_id
        WHERE cpr.reset_token = ? AND cpr.used_at IS NULL AND cpr.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset_record = $stmt->fetch();
    
    if (!$reset_record) {
        $errors[] = 'This reset link is invalid or has expired. Please request a new one.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    require_csrf();
    
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Password and confirmation do not match.';
    if (!preg_match('/[A-Za-z]/', $password)) $errors[] = 'Password must include a letter.';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must include a digit.';
    
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Update password
        db()->prepare("UPDATE customer_accounts SET password_hash = ? WHERE id = ?")
            ->execute([$hash, $reset_record['customer_id']]);
        
        // Mark token as used
        db()->prepare("UPDATE customer_password_resets SET used_at = NOW() WHERE id = ?")
            ->execute([$reset_record['id']]);
        
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password | Phoenix Arabia</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="customer.css">
</head>
<body>

<div class="auth-shell">
  <div class="auth-card">
    <div class="auth-brand">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
    </div>
    <h1>Reset Password</h1>
    <p class="auth-sub">Phoenix Arabia Customer Portal</p>
    
    <?php if ($success): ?>
      <div class="flash flash-success">✓ Password reset successfully. You can now sign in with your new password.</div>
      <a href="login.php" class="btn blue" style="width:100%; padding:12px; font-size:14px; justify-content: center;">Sign In →</a>
      
    <?php elseif (!empty($errors)): ?>
      <div class="flash flash-error">
        <ul style="margin: 0; padding-left: 20px;">
          <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php if ($reset_record): ?>
        <form method="post" autocomplete="off">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <div class="form-field">
            <label>New Password</label>
            <input type="password" name="password" required minlength="8" autofocus>
            <div class="hint">Min 8 chars, with letter and digit.</div>
          </div>
          <div class="form-field">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required minlength="8">
          </div>
          <button type="submit" class="btn blue" style="width:100%; padding:12px; font-size:14px; justify-content: center;">Reset Password</button>
        </form>
      <?php else: ?>
        <a href="forgot_password.php" class="btn blue" style="width:100%; padding:12px; font-size:14px; justify-content: center;">Request New Link</a>
      <?php endif; ?>
      
    <?php else: ?>
      <p style="font-size: 13px; color: var(--pa-slate); margin-bottom: 20px; line-height: 1.6; text-align: center;">
        Welcome <b><?= e($reset_record['full_name']) ?></b>. Choose a new password for your account.
      </p>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="form-field">
          <label>New Password</label>
          <input type="password" name="password" required minlength="8" autofocus>
          <div class="hint">Min 8 chars, with letter and digit.</div>
        </div>
        <div class="form-field">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" required minlength="8">
        </div>
        <button type="submit" class="btn blue" style="width:100%; padding:12px; font-size:14px; justify-content: center;">Reset Password</button>
      </form>
    <?php endif; ?>
    
    <div class="auth-foot">
      <a href="login.php">← Back to Sign In</a>
    </div>
  </div>
</div>

</body>
</html>
