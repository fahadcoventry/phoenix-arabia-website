<?php
// ═══════════════════════════════════════════════════════════
// Phoenix Arabia — One-Time Setup Script
// ═══════════════════════════════════════════════════════════
// This file is for INITIAL SETUP ONLY.
// After running it once successfully, DELETE IT IMMEDIATELY.
//
// What it does:
// 1. Creates (or updates) the Super Admin staff account
// 2. Removes the insecure default password from the SQL seed
// 3. Sets a strong password chosen by you
//
// USAGE:
// 1. Upload this file to public_html
// 2. Visit https://www.phoenix.com.sa/setup_admin.php
// 3. Enter the admin name, email, and a strong password
// 4. Submit
// 5. DELETE this file from the server immediately after
// ═══════════════════════════════════════════════════════════

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$message = null;
$message_type = null;
$completed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required.';
    if (!valid_email($email)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 12) $errors[] = 'Password must be at least 12 characters.';
    if ($password !== $confirm) $errors[] = 'Password confirmation does not match.';
    
    // Strength check
    if (!empty($password)) {
        if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must include an uppercase letter.';
        if (!preg_match('/[a-z]/', $password)) $errors[] = 'Password must include a lowercase letter.';
        if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must include a digit.';
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = 'Password must include a symbol.';
    }
    
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Upsert: if email exists, update; otherwise insert
        $check = db()->prepare("SELECT id FROM staff_users WHERE email = ? LIMIT 1");
        $check->execute([$email]);
        $existing = $check->fetch();
        
        if ($existing) {
            db()->prepare("UPDATE staff_users SET name = ?, password_hash = ?, role = 'Super Admin', is_active = 1 WHERE id = ?")
                ->execute([$name, $hash, $existing['id']]);
            $message = "Super Admin account UPDATED successfully. Email: $email";
        } else {
            db()->prepare("INSERT INTO staff_users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, 'Super Admin', 1)")
                ->execute([$name, $email, $hash]);
            $message = "Super Admin account CREATED successfully. Email: $email";
        }
        
        // Also delete the insecure seeded admin if present
        db()->prepare("DELETE FROM staff_users WHERE email = 'superadmin@phoenix.com.sa' AND email <> ?")
            ->execute([$email]);
        
        $message_type = 'success';
        $completed = true;
    } else {
        $message = implode(' · ', $errors);
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Initial Setup | Phoenix Arabia</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="staff/staff.css">
</head>
<body>

<div class="login-shell">
  <div class="login-card" style="max-width: 520px;">
    <div class="login-brand">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
      <h1>Phoenix Arabia™</h1>
      <p class="login-sub">Initial Setup</p>
    </div>
    
    <?php if ($completed): ?>
      <div class="flash flash-success" style="margin-bottom: 20px;">
        ✓ <?= e($message) ?>
      </div>
      
      <div style="background: #FEF3C7; border: 1px solid #FCD34D; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
        <div style="font-weight: 800; color: #92400E; margin-bottom: 8px;">⚠ CRITICAL — Delete This File Now</div>
        <p style="font-size: 13px; color: #78350F; line-height: 1.6;">
          Setup is complete. <b>Immediately delete <code>setup_admin.php</code></b> from the server using FTP or File Manager. Leaving it in place is a serious security risk.
        </p>
      </div>
      
      <a href="staff/login.php" class="btn blue" style="width: 100%; padding: 12px; font-size: 14px; justify-content: center;">→ Go to Staff Login</a>
      
    <?php else: ?>
    
      <?php if ($message): ?>
        <div class="flash flash-<?= e($message_type) ?>" style="margin-bottom: 16px;"><?= e($message) ?></div>
      <?php endif; ?>
      
      <div style="background: var(--pa-blue-soft); border: 1px solid var(--pa-blue); padding: 14px; border-radius: 8px; margin-bottom: 20px;">
        <div style="font-size: 12px; color: var(--pa-blue); font-weight: 700; margin-bottom: 6px;">ℹ ONE-TIME SETUP</div>
        <p style="font-size: 13px; color: var(--pa-charcoal); line-height: 1.6;">
          This page creates the first Super Admin account for the staff console. Run it once and then <b>delete this file</b>.
        </p>
      </div>
      
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        
        <div class="form-field">
          <label>Full Name</label>
          <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Fahad Al-Enazi">
        </div>
        
        <div class="form-field">
          <label>Admin Email</label>
          <input type="email" name="email" required value="<?= e($_POST['email'] ?? 'fahad@phoenix.com.sa') ?>">
        </div>
        
        <div class="form-field">
          <label>Strong Password</label>
          <input type="password" name="password" required minlength="12" placeholder="Min 12 chars, mixed case, digits, symbols">
          <div class="hint">Must contain: uppercase, lowercase, digit, symbol. 12+ characters.</div>
        </div>
        
        <div class="form-field">
          <label>Confirm Password</label>
          <input type="password" name="confirm" required minlength="12">
        </div>
        
        <button type="submit" class="btn blue" style="width: 100%; padding: 12px; font-size: 14px; justify-content: center;">Create Super Admin Account</button>
      </form>
    
    <?php endif; ?>
  </div>
</div>

</body>
</html>
