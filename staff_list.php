<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

// Only Super Admin can manage staff
if (current_staff()['role'] !== 'Super Admin') {
    http_response_code(403);
    require __DIR__ . '/../403.php';
    exit;
}

// Toggle active status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    require_csrf();
    $tid = (int)($_POST['id'] ?? 0);
    if ($tid > 0 && $tid !== (int)current_staff()['id']) {
        db()->prepare("UPDATE staff_users SET is_active = 1 - is_active WHERE id = ?")->execute([$tid]);
        flash_set('success', 'Staff status updated.');
    } else {
        flash_set('error', 'Cannot disable your own account.');
    }
    header('Location: staff_list.php');
    exit;
}

$staff = db()->query("SELECT * FROM staff_users ORDER BY id ASC")->fetchAll();

layout_start('Staff Users', 'staff');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1>Staff Users</h1>
    <p><?= count($staff) ?> staff member(s) — Super Admin access</p>
  </div>
  <div class="page-head-actions">
    <a class="btn green" href="staff_edit.php">+ Add Staff Member</a>
  </div>
</div>

<div class="data-table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>Role</th>
        <th>Status</th>
        <th>Last Login</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($staff as $u): ?>
        <tr>
          <td>
            <b><?= e($u['name']) ?></b>
            <?php if ((int)$u['id'] === (int)current_staff()['id']): ?>
              <span style="font-size: 10px; color: var(--pa-muted); margin-left: 4px;">(you)</span>
            <?php endif; ?>
          </td>
          <td><?= e($u['email']) ?></td>
          <td style="font-family: monospace; font-size: 12px;"><?= e($u['mobile'] ?: '—') ?></td>
          <td>
            <span style="font-size: 11px; font-weight: 700; color: <?= role_color($u['role']) ?>; text-transform: uppercase; letter-spacing: 0.5px;">
              <?= e($u['role']) ?>
            </span>
          </td>
          <td>
            <span class="badge badge-<?= $u['is_active'] ? 'active' : 'disabled' ?>">
              <?= $u['is_active'] ? 'Active' : 'Disabled' ?>
            </span>
          </td>
          <td style="font-size: 11px; color: var(--pa-muted);">
            <?= $u['last_login'] ? date('M j, Y g:i A', strtotime($u['last_login'])) : 'Never' ?>
          </td>
          <td class="actions">
            <a class="btn light" href="staff_edit.php?id=<?= e($u['id']) ?>">Edit</a>
            <?php if ((int)$u['id'] !== (int)current_staff()['id']): ?>
              <form method="post" style="display: inline;" onsubmit="return confirm('<?= $u['is_active'] ? 'Disable' : 'Enable' ?> this account?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                <button type="submit" class="btn light" style="color: <?= $u['is_active'] ? '#A32D2D' : 'var(--pa-green-dark)' ?>;">
                  <?= $u['is_active'] ? 'Disable' : 'Enable' ?>
                </button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php layout_end(); ?>
