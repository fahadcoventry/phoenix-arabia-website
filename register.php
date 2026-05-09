<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/_auth.php';

if (!empty($_SESSION['customer_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['full_name' => '', 'company_name' => '', 'mobile' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!rate_limit_check('cust_register_' . $ip, 3, 1800)) {
        $errors[] = 'Too many registration attempts. Please wait 30 minutes.';
    } else {
        $old['full_name'] = trim($_POST['full_name'] ?? '');
        $old['company_name'] = trim($_POST['company_name'] ?? '');
        $old['mobile'] = normalize_mobile($_POST['mobile'] ?? '');
        $old['email'] = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($old['full_name'])) $errors[] = 'Full name is required.';
        if (strlen($old['full_name']) > 160) $errors[] = 'Full name too long.';
        if (!valid_email($old['email'])) $errors[] = 'Valid email is required.';
        if (!valid_mobile($old['mobile'])) $errors[] = 'Valid mobile number is required.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm) $errors[] = 'Password and confirmation do not match.';
        
        // Strength
        if (!empty($password)) {
            if (!preg_match('/[A-Za-z]/', $password)) $errors[] = 'Password must include a letter.';
            if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must include a digit.';
        }
        
        // Uniqueness checks
        if (empty($errors)) {
            $check = db()->prepare("SELECT id FROM customer_accounts WHERE email = ? OR mobile = ? LIMIT 1");
            $check->execute([$old['email'], $old['mobile']]);
            if ($check->fetch()) {
                $errors[] = 'An account already exists with this email or mobile.';
            }
        }
        
        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            try {
                db()->prepare("
                    INSERT INTO customer_accounts (full_name, company_name, mobile, email, password_hash, account_status)
                    VALUES (?, ?, ?, ?, ?, 'Active')
                ")->execute([
                    $old['full_name'],
                    $old['company_name'] ?: null,
                    $old['mobile'],
                    $old['email'],
                    $hash
                ]);
                
                header('Location: login.php?registered=1');
                exit;
            } catch (PDOException $e) {
                error_log('Customer registration error: ' . $e->getMessage());
                $errors[] = 'Registration failed. Please try again or contact us.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Customer Account | Phoenix Arabia</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="customer.css">
</head>
<body>

<div class="auth-shell">
  <div class="auth-card" style="max-width: 540px;">
    <div class="auth-brand">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
    </div>
    <h1>Create Account</h1>
    <p class="auth-sub">Phoenix Arabia Customer Portal</p>
    
    <?php if (!empty($errors)): ?>
      <div class="flash flash-error">
        <ul style="margin: 0; padding-left: 20px;">
          <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      
      <div class="form-row">
        <div class="form-field">
          <label>Full Name *</label>
          <input type="text" name="full_name" required value="<?= e($old['full_name']) ?>" maxlength="160">
        </div>
        <div class="form-field">
          <label>Company Name</label>
          <input type="text" name="company_name" value="<?= e($old['company_name']) ?>" maxlength="190">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-field">
          <label>Email *</label>
          <input type="email" name="email" required value="<?= e($old['email']) ?>" autocomplete="email">
        </div>
        <div class="form-field">
          <label>Mobile *</label>
          <input type="tel" name="mobile" required value="<?= e($old['mobile']) ?>" placeholder="+966 5X..." autocomplete="tel">
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-field">
          <label>Password *</label>
          <input type="password" name="password" required minlength="8" autocomplete="new-password">
          <div class="hint">Min 8 chars, with letter and digit.</div>
        </div>
        <div class="form-field">
          <label>Confirm Password *</label>
          <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
        </div>
      </div>
      
      <button type="submit" class="btn blue" style="width:100%; padding:12px; font-size:14px; justify-content: center;">Create Account</button>
    </form>
    
    <div class="auth-foot">
      Already have an account? <a href="login.php">Sign in →</a>
    </div>
    <div style="text-align: center; margin-top: 12px; font-size: 11px; color: var(--pa-muted);">
      <a href="../index.php" style="color: var(--pa-muted);">← Back to website</a>
    </div>
  </div>
</div>

</body>
</html>
