<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

staff_require('view', 'customers');

$f_search = trim($_GET['q'] ?? '');
$f_status = trim($_GET['status'] ?? '');

$where = [];
$params = [];

if (!empty($f_search)) {
    $where[] = '(full_name LIKE ? OR company_name LIKE ? OR mobile LIKE ? OR email LIKE ?)';
    $like = '%' . $f_search . '%';
    array_push($params, $like, $like, $like, $like);
}
if (!empty($f_status)) {
    $where[] = 'account_status = ?';
    $params[] = $f_status;
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = db()->prepare("SELECT * FROM customer_accounts $where_sql ORDER BY id DESC LIMIT 200");
$stmt->execute($params);
$customers = $stmt->fetchAll();

layout_start('Customers', 'customers');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1>Customer Accounts</h1>
    <p><?= count($customers) ?> account(s) shown</p>
  </div>
</div>

<form class="filters-bar" method="get">
  <input type="search" name="q" placeholder="Search name, company, mobile, email..." value="<?= e($f_search) ?>">
  <select name="status">
    <option value="">All statuses</option>
    <?php foreach (['Active','Suspended','Pending'] as $st): ?>
      <option value="<?= $st ?>" <?= $f_status === $st ? 'selected' : '' ?>><?= $st ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn blue">Filter</button>
</form>

<?php if (empty($customers)): ?>
  <div class="empty-state">
    <div class="empty-state-icon">👥</div>
    <h3>No customers found</h3>
    <p>When customers register, they will appear here.</p>
  </div>
<?php else: ?>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Company</th>
          <th>Mobile</th>
          <th>Email</th>
          <th>Status</th>
          <th>Registered</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
          <tr>
            <td><b><?= e($c['full_name']) ?></b></td>
            <td><?= e($c['company_name'] ?: '—') ?></td>
            <td style="font-family: monospace; font-size: 12px;">
              <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $c['mobile'])) ?>" target="_blank"><?= e($c['mobile']) ?></a>
            </td>
            <td><a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></td>
            <td>
              <span class="badge badge-<?= strtolower($c['account_status']) ?>"><?= e($c['account_status']) ?></span>
            </td>
            <td style="font-size: 11px; color: var(--pa-muted);">
              <?= date('M j, Y', strtotime($c['created_at'])) ?>
            </td>
            <td class="actions">
              <a class="btn light" href="rfq_list.php?q=<?= e($c['email']) ?>">RFQs</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php layout_end(); ?>
