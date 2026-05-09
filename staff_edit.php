<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

if (current_staff()['role'] !== 'Super Admin') {
    http_response_code(403);
    require __DIR__ . '/../403.php';
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$is_new = ($id === 0);

$u = ['name' => '', 'email' => '', 'mobile' => '', 'role' => 'Sales Agent', 'is_active' => 1];

if (!$is_new) {
    $stmt = db()->prepare("SELECT * FROM staff_users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $loaded = $stmt->fetch();
    if (!$loaded) {
        flash_set('error', 'Staff user not found.');
        header('Location: staff_list.php');
        exit;
    }
    $u = $loaded;
}

$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = normalize_mobile($_POST['mobile'] ?? '');
    $role = $_POST['role'] ?? 'Sales Agent';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    $allowed_roles = ['Super Admin', 'Sales Agent', 'Account Manager', 'Procurement Officer', 'Data Entry'];
    if (!in_array($role, $allowed_roles, true)) $role = 'Sales Agent';

    // Validation
    if (empty($name)) $form_errors[] = 'Name is required.';
    if (!valid_email($email)) $form_errors[] = 'Valid email is required.';
    if (!empty($mobile) && !valid_mobile($mobile)) $form_errors[] = 'Mobile format invalid.';
    if ($is_new && empty($password)) $form_errors[] = 'Password is required for new user.';
    if (!empty($password) && strlen($password) < 10) $form_errors[] = 'Password must be at least 10 characters.';

    // Check email uniqueness
    if (empty($form_errors)) {
        $check = db()->prepare("SELECT id FROM staff_users WHERE email = ? AND id <> ? LIMIT 1");
        $check->execute([$email, $id]);
        if ($check->fetch()) $form_errors[] = 'Email is already used by another staff member.';
    }

    if (empty($form_errors)) {
        if ($is_new) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            db()->prepare("INSERT INTO staff_users (name, email, mobile, password_hash, role, is_active) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$name, $email, $mobile ?: null, $hash, $role, $is_active]);
            $new_id = (int)db()->lastInsertId();
            flash_set('success', 'Staff user created successfully.');
            header('Location: staff_edit.php?id=' . $new_id);
            exit;
        } else {
            // Don't allow Super Admin to demote themselves
            if ((int)$id === (int)current_staff()['id'] && $role !== 'Super Admin') {
                $form_errors[] = 'You cannot change your own role from Super Admin.';
            } else {
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    db()->prepare("UPDATE staff_users SET name=?, email=?, mobile=?, password_hash=?, role=?, is_active=? WHERE id=?")
                        ->execute([$name, $email, $mobile ?: null, $hash, $role, $is_active, $id]);
                } else {
                    db()->prepare("UPDATE staff_users SET name=?, email=?, mobile=?, role=?, is_active=? WHERE id=?")
                        ->execute([$name, $email, $mobile ?: null, $role, $is_active, $id]);
                }
                flash_set('success', 'Staff user updated successfully.');
                header('Location: staff_edit.php?id=' . $id);
                exit;
            }
        }
    }

    // Re-fill on error
    $u = array_merge($u, [
        'name' => $name, 'email' => $email, 'mobile' => $mobile, 'role' => $role, 'is_active' => $is_active,
    ]);
}

layout_start($is_new ? 'New Staff User' : 'Edit Staff User', 'staff');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1><?= $is_new ? 'New Staff User' : 'Edit Staff User' ?></h1>
    <?php if (!$is_new): ?>
      <p>ID: <?= e($u['id']) ?> · Created <?= date('M j, Y', strtotime($u['created_at'])) ?></p>
    <?php endif; ?>
  </div>
  <a class="btn light" href="staff_list.php">← Back to List</a>
</div>

<?php if (!empty($form_errors)): ?>
  <div class="flash flash-error">
    <ul style="margin: 0; padding-left: 20px;">
      <?php foreach ($form_errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" class="form-card">
  <?= csrf_field() ?>

  <div class="form-row">
    <div class="form-field">
      <label>Full Name *</label>
      <input type="text" name="name" required value="<?= e($u['name']) ?>">
    </div>
    <div class="form-field">
      <label>Email *</label>
      <input type="email" name="email" required value="<?= e($u['email']) ?>">
    </div>
  </div>

  <div class="form-row">
    <div class="form-field">
      <label>Mobile (optional)</label>
      <input type="tel" name="mobile" value="<?= e($u['mobile']) ?>" placeholder="+966 5X...">
    </div>
    <div class="form-field">
      <label>Role *</label>
      <select name="role">
        <?php foreach (['Super Admin','Account Manager','Sales Agent','Procurement Officer','Data Entry'] as $r): ?>
          <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= $r ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-field">
    <label>Password <?= $is_new ? '*' : '(leave blank to keep current)' ?></label>
    <input type="password" name="password" <?= $is_new ? 'required' : '' ?> autocomplete="new-password" placeholder="<?= $is_new ? 'Min 10 characters' : '••••••••' ?>">
    <div class="hint">Minimum 10 characters. Use a strong unique password.</div>
  </div>

  <div class="form-field" style="display: flex; align-items: center; padding-top: 8px;">
    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; text-transform: none; font-size: 13px; letter-spacing: 0;">
      <input type="checkbox" name="is_active" <?= $u['is_active'] ? 'checked' : '' ?> style="width: auto;">
      Account active (can log in)
    </label>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn blue"><?= $is_new ? 'Create User' : 'Save Changes' ?></button>
    <a class="btn light" href="staff_list.php">Cancel</a>
  </div>
</form>

<?php layout_end(); ?>
