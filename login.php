<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$error = null;

// If already logged in, redirect
if (!empty($_SESSION['staff_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Disabled-account flash
if (isset($_GET['disabled'])) {
    $error = 'Your account has been disabled. Please contact a Super Admin.';
}

// Logged-out flash
$logged_out = isset($_GET['logged_out']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!rate_limit_check('staff_login_' . $ip, 5, 600)) {
        $error = 'Too many login attempts. Please wait 10 minutes.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Email and password are required.';
        } else {
            $stmt = db()->prepare("SELECT id, name, email, password_hash, role, is_active FROM staff_users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $u = $stmt->fetch();

            if ($u && $u['is_active'] && password_verify($password, $u['password_hash'])) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                $_SESSION['staff_id']   = (int)$u['id'];
                $_SESSION['staff_name'] = $u['name'];
                $_SESSION['staff_role'] = $u['role'];

                // Update last login
                db()->prepare("UPDATE staff_users SET last_login = NOW() WHERE id = ?")
                    ->execute([$u['id']]);

                // Regenerate CSRF
                unset($_SESSION['csrf_token']);

                header('Location: dashboard.php');
                exit;
            } else {
                // Generic error (don't reveal whether email exists)
                $error = 'Invalid credentials or account disabled.';
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
<title>Staff Login | Phoenix Arabia</title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="staff.css">
</head>
<body>

<div class="login-shell">
  <div class="login-card">
    <div class="login-brand">
      <div class="mark">
        <span class="b"></span>
        <span class="g"></span>
      </div>
      <h1>Phoenix Arabia™</h1>
      <p class="login-sub">Staff Console</p>
    </div>

    <?php if ($error): ?>
      <div class="flash flash-error" style="margin-bottom: 16px;"><?= e($error) ?></div>
    <?php elseif ($logged_out): ?>
      <div class="flash flash-success" style="margin-bottom: 16px;">You have been signed out.</div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <div class="form-field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@phoenix.com.sa">
      </div>
      <div class="form-field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn blue" style="width: 100%; padding: 12px; font-size: 14px;">Sign In</button>
    </form>

    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: var(--pa-muted);">
      Authorized personnel only · Phoenix Arabia Contracting Co. Ltd.
    </div>
    <div style="text-align: center; margin-top: 8px; font-size: 11px; color: var(--pa-muted);">
      <a href="../index.php" style="color: var(--pa-muted);">← Back to website</a>
    </div>
  </div>
</div>

</body>
</html>
