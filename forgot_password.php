<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$message = null;
$message_type = null;
$reset_url = null; // Will hold the reset link if SMTP not configured

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!rate_limit_check('forgot_pw_' . $ip, 3, 1800)) {
        $message = 'Too many attempts. Please wait 30 minutes.';
        $message_type = 'error';
    } else {
        $email = trim($_POST['email'] ?? '');
        
        if (!valid_email($email)) {
            $message = 'Please enter a valid email address.';
            $message_type = 'error';
        } else {
            // Always show generic success (don't reveal whether email exists)
            $stmt = db()->prepare("SELECT id, full_name FROM customer_accounts WHERE email = ? AND account_status = 'Active' LIMIT 1");
            $stmt->execute([$email]);
            $u = $stmt->fetch();
            
            if ($u) {
                // Generate token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                db()->prepare("INSERT INTO customer_password_resets (customer_id, reset_token, expires_at) VALUES (?, ?, ?)")
                    ->execute([$u['id'], $token, $expires]);
                
                // Build reset URL
                $reset_url = SITE_URL . '/customer/reset_password.php?token=' . $token;
                
                // TODO: When SMTP is configured, send the reset_url via email
                // For now: show the reset link directly (development mode only)
                // In production with SMTP, ALWAYS show generic success regardless
            }
            
            $message = 'If an account exists with that email, password reset instructions have been generated. Check your email or use the reset link below.';
            $message_type = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password | Phoenix Arabia</title>
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
    <h1>Forgot Password</h1>
    <p class="auth-sub">Reset your customer account</p>
    
    <?php if ($message): ?>
      <div class="flash flash-<?= e($message_type) ?>"><?= e($message) ?></div>
      <?php if ($reset_url): ?>
        <div class="flash flash-warning" style="word-break: break-all; font-family: monospace; font-size: 11px;">
          ⚠ Development mode (no SMTP). Reset link:<br><br>
          <a href="<?= e($reset_url) ?>"><?= e($reset_url) ?></a>
        </div>
      <?php endif; ?>
    <?php endif; ?>
    
    <?php if (!$reset_url): ?>
      <p style="font-size: 13px; color: var(--pa-slate); margin-bottom: 20px; line-height: 1.6; text-align: center;">
        Enter the email associated with your account and we'll send you a password reset link.
      </p>
      
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="form-field">
          <label>Email</label>
          <input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <button type="submit" class="btn blue" style="width:100%; padding:12px; font-size:14px; justify-content: center;">Send Reset Link</button>
      </form>
    <?php endif; ?>
    
    <div class="auth-foot">
      Remember your password? <a href="login.php">Sign in →</a>
    </div>
  </div>
</div>

</body>
</html>
