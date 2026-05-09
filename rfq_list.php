<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

staff_require('view', 'rfq');

// Filters
$f_status = trim($_GET['status'] ?? '');
$f_priority = trim($_GET['priority'] ?? '');
$f_search = trim($_GET['q'] ?? '');

// Build query
$where = [];
$params = [];

if (!empty($f_status)) {
    $where[] = 'status = ?';
    $params[] = $f_status;
}
if (!empty($f_priority)) {
    $where[] = 'priority = ?';
    $params[] = $f_priority;
}
if (!empty($f_search)) {
    $where[] = '(company LIKE ? OR contact_name LIKE ? OR mobile LIKE ? OR email LIKE ?)';
    $like = '%' . $f_search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT * FROM rfq_requests $where_sql ORDER BY id DESC LIMIT 200";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rfqs = $stmt->fetchAll();

layout_start('RFQ Requests', 'rfq');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1>RFQ Requests</h1>
    <p><?= count($rfqs) ?> request(s) <?= !empty($where) ? '(filtered)' : '' ?></p>
  </div>
</div>

<!-- Filters -->
<form class="filters-bar" method="get">
  <input type="search" name="q" placeholder="Search by company, contact, mobile, email..." value="<?= e($f_search) ?>">
  <select name="status">
    <option value="">All statuses</option>
    <?php foreach (['New','Reviewing','Quoted','Won','Lost','Closed'] as $st): ?>
      <option value="<?= $st ?>" <?= $f_status === $st ? 'selected' : '' ?>><?= $st ?></option>
    <?php endforeach; ?>
  </select>
  <select name="priority">
    <option value="">All priorities</option>
    <?php foreach (['Low','Normal','High','Urgent'] as $pr): ?>
      <option value="<?= $pr ?>" <?= $f_priority === $pr ? 'selected' : '' ?>><?= $pr ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn blue">Filter</button>
  <?php if (!empty($where)): ?>
    <a class="btn light" href="rfq_list.php">Clear</a>
  <?php endif; ?>
</form>

<?php if (empty($rfqs)): ?>
  <div class="empty-state">
    <div class="empty-state-icon">📭</div>
    <h3>No RFQs found</h3>
    <p><?= !empty($where) ? 'Try adjusting your filters.' : 'When customers submit RFQs through the website, they will appear here.' ?></p>
  </div>
<?php else: ?>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Reference</th>
          <th>Company / Contact</th>
          <th>Mobile</th>
          <th>Type</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rfqs as $r): ?>
          <tr>
            <td>
              <b style="color: var(--pa-blue);">PA-RFQ-<?= str_pad((string)$r['id'], 5, '0', STR_PAD_LEFT) ?></b>
            </td>
            <td>
              <div style="font-weight: 600;"><?= e($r['company']) ?></div>
              <div style="font-size: 11px; color: var(--pa-muted);"><?= e($r['contact_name']) ?></div>
            </td>
            <td style="font-family: monospace; font-size: 12px;">
              <?= e($r['mobile']) ?>
            </td>
            <td><?= e($r['request_type'] ?: '—') ?></td>
            <td>
              <span class="priority-<?= strtolower($r['priority']) ?>"><?= e($r['priority']) ?></span>
            </td>
            <td>
              <span class="badge badge-<?= strtolower($r['status']) ?>"><?= e($r['status']) ?></span>
            </td>
            <td style="font-size: 11px; color: var(--pa-muted);">
              <?= date('M j, Y', strtotime($r['created_at'])) ?>
            </td>
            <td class="actions">
              <a class="btn blue" href="rfq_view.php?id=<?= e($r['id']) ?>">Open</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p style="font-size: 11px; color: var(--pa-muted); margin-top: 12px; text-align: right;">
    Showing latest 200 records.
  </p>
<?php endif; ?>

<?php layout_end(); ?>
