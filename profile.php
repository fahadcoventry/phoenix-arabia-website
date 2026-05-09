<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

customer_required();
$c = current_customer();

$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $mobile = normalize_mobile($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($full_name)) $form_errors[] = 'Full name is required.';
        if (!valid_email($email)) $form_errors[] = 'Valid email is required.';
        if (!valid_mobile($mobile)) $form_errors[] = 'Valid mobile is required.';
        
        // Check for email/mobile uniqueness (excluding self)
        if (empty($form_errors)) {
            $check = db()->prepare("SELECT id FROM customer_accounts WHERE (email = ? OR mobile = ?) AND id <> ? LIMIT 1");
            $check->execute([$email, $mobile, $c['id']]);
            if ($check->fetch()) {
                $form_errors[] = 'Email or mobile is already in use by another account.';
            }
        }
        
        if (empty($form_errors)) {
            db()->prepare("UPDATE customer_accounts SET full_name = ?, company_name = ?, mobile = ?, email = ? WHERE id = ?")
                ->execute([$full_name, $company_name ?: null, $mobile, $email, $c['id']]);
            cust_flash_set('success', 'Profile updated successfully.');
            header('Location: profile.php');
            exit;
        }
    } elseif ($action === 'password') {
        $current_pw = $_POST['current_password'] ?? '';
        $new_pw = $_POST['new_password'] ?? '';
        $confirm_pw = $_POST['confirm_password'] ?? '';
        
        // Verify current
        $stmt = db()->prepare("SELECT password_hash FROM customer_accounts WHERE id = ? LIMIT 1");
        $stmt->execute([$c['id']]);
        $u = $stmt->fetch();
        
        if (!$u || !password_verify($current_pw, $u['password_hash'])) {
            $form_errors[] = 'Current password is incorrect.';
        }
        if (strlen($new_pw) < 8) {
            $form_errors[] = 'New password must be at least 8 characters.';
        }
        if ($new_pw !== $confirm_pw) {
            $form_errors[] = 'New password and confirmation do not match.';
        }
        if ($new_pw === $current_pw) {
            $form_errors[] = 'New password must be different from current.';
        }
        if (!preg_match('/[A-Za-z]/', $new_pw) || !preg_match('/[0-9]/', $new_pw)) {
            $form_errors[] = 'Password must include letter and digit.';
        }
        
        if (empty($form_errors)) {
            $hash = password_hash($new_pw, PASSWORD_BCRYPT);
            db()->prepare("UPDATE customer_accounts SET password_hash = ? WHERE id = ?")
                ->execute([$hash, $c['id']]);
            cust_flash_set('success', 'Password changed successfully.');
            header('Location: profile.php');
            exit;
        }
    }
}

customer_layout_start('Profile', 'profile');
?>

<?= cust_flash_html() ?>

<div class="page-head">
  <div>
    <h1>My Profile</h1>
    <p>Manage your account information and password</p>
  </div>
</div>

<?php if (!empty($form_errors)): ?>
  <div class="flash flash-error">
    <ul style="margin: 0; padding-left: 20px;">
      <?php foreach ($form_errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<!-- Personal Info -->
<form method="post" class="form-card" style="margin-bottom: 24px;">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="profile">
  
  <h2 style="font-size: 18px; font-weight: 800; color: var(--pa-blue); margin-bottom: 8px;">Account Information</h2>
  <p style="font-size: 13px; color: var(--pa-muted); margin-bottom: 20px;">
    Account created: <?= date('F j, Y', strtotime($c['created_at'])) ?>
  </p>
  
  <div class="form-row">
    <div class="form-field">
      <label>Full Name *</label>
      <input type="text" name="full_name" required value="<?= e($c['full_name']) ?>" maxlength="160">
    </div>
    <div class="form-field">
      <label>Company Name</label>
      <input type="text" name="company_name" value="<?= e($c['company_name']) ?>" maxlength="190">
    </div>
  </div>
  
  <div class="form-row">
    <div class="form-field">
      <label>Email *</label>
      <input type="email" name="email" required value="<?= e($c['email']) ?>">
    </div>
    <div class="form-field">
      <label>Mobile *</label>
      <input type="tel" name="mobile" required value="<?= e($c['mobile']) ?>">
    </div>
  </div>
  
  <div class="form-actions">
    <button type="submit" class="btn blue">Save Changes</button>
  </div>
</form>

<!-- Change Password -->
<form method="post" class="form-card" autocomplete="off">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="password">
  
  <h2 style="font-size: 18px; font-weight: 800; color: var(--pa-blue); margin-bottom: 20px;">🔑 Change Password</h2>
  
  <div class="form-field">
    <label>Current Password *</label>
    <input type="password" name="current_password" required autocomplete="current-password">
  </div>
  
  <div class="form-row">
    <div class="form-field">
      <label>New Password *</label>
      <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
      <div class="hint">Min 8 chars, with letter and digit.</div>
    </div>
    <div class="form-field">
      <label>Confirm New Password *</label>
      <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
    </div>
  </div>
  
  <div class="form-actions">
    <button type="submit" class="btn blue">Change Password</button>
  </div>
</form>

<?php customer_layout_end(); ?>
