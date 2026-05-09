<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

customer_required();
$c = current_customer();

// Filter
$f_status = trim($_GET['status'] ?? '');

$where = ['customer_id = ?'];
$params = [$c['id']];

if (!empty($f_status)) {
    $where[] = 'status = ?';
    $params[] = $f_status;
}

$where_sql = 'WHERE ' . implode(' AND ', $where);
$stmt = db()->prepare("SELECT * FROM customer_orders $where_sql ORDER BY id DESC LIMIT 100");
$stmt->execute($params);
$orders = $stmt->fetchAll();

customer_layout_start('My RFQ Orders', 'orders');
?>

<?= cust_flash_html() ?>

<div class="page-head">
  <div>
    <h1>My RFQ Orders</h1>
    <p><?= count($orders) ?> RFQ order(s) <?= !empty($f_status) ? '(filtered)' : '' ?></p>
  </div>
  <div class="page-head-actions">
    <a class="btn green" href="../index.php#rfq">📤 Submit New RFQ</a>
  </div>
</div>

<form method="get" style="background: var(--pa-white); border: 1px solid var(--pa-border); border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
  <select name="status" style="border: 1px solid var(--pa-border); border-radius: 6px; padding: 8px 12px; font-size: 13px; font-family: inherit; background: var(--pa-off); min-width: 200px;">
    <option value="">All statuses</option>
    <?php foreach (['Draft','Submitted','Reviewing','Quoted','Won','Lost','Closed'] as $st): ?>
      <option value="<?= $st ?>" <?= $f_status === $st ? 'selected' : '' ?>><?= $st ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn blue">Filter</button>
  <?php if (!empty($f_status)): ?>
    <a class="btn light" href="orders.php">Clear</a>
  <?php endif; ?>
</form>

<?php if (empty($orders)): ?>
  <div class="empty-state">
    <div class="empty-state-icon">📋</div>
    <h3>No orders found</h3>
    <p><?= !empty($f_status) ? 'Try adjusting filters.' : 'You haven\'t submitted any RFQs yet.' ?></p>
    <a class="btn blue" href="../index.php#marketplace">Browse Marketplace →</a>
  </div>
<?php else: ?>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Reference</th>
          <th>Type</th>
          <th>Items</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Last Update</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><b style="color: var(--pa-blue);"><?= e($o['order_reference']) ?></b></td>
            <td><?= e($o['request_type'] ?: '—') ?></td>
            <td><?= e($o['total_items']) ?></td>
            <td><span class="badge badge-<?= strtolower($o['status']) ?>"><?= e($o['status']) ?></span></td>
            <td style="font-size: 11px; color: var(--pa-muted);">
              <?= date('M j, Y', strtotime($o['submitted_at'])) ?>
            </td>
            <td style="font-size: 11px; color: var(--pa-muted);">
              <?= $o['updated_at'] ? date('M j, Y', strtotime($o['updated_at'])) : '—' ?>
            </td>
            <td class="actions">
              <a class="btn light" href="order_view.php?id=<?= e($o['id']) ?>">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p style="font-size: 11px; color: var(--pa-muted); margin-top: 12px; text-align: right;">
    Showing latest 100 records.
  </p>
<?php endif; ?>

<?php customer_layout_end(); ?>
