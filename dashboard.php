<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

$s = current_staff();

// ── Key metrics ──
$stats = [
    'rfq_total'      => (int)db()->query("SELECT COUNT(*) FROM rfq_requests")->fetchColumn(),
    'rfq_new'        => (int)db()->query("SELECT COUNT(*) FROM rfq_requests WHERE status='New'")->fetchColumn(),
    'rfq_reviewing'  => (int)db()->query("SELECT COUNT(*) FROM rfq_requests WHERE status='Reviewing'")->fetchColumn(),
    'rfq_quoted'     => (int)db()->query("SELECT COUNT(*) FROM rfq_requests WHERE status='Quoted'")->fetchColumn(),
    'rfq_won'        => (int)db()->query("SELECT COUNT(*) FROM rfq_requests WHERE status='Won'")->fetchColumn(),
    'products'       => (int)db()->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn(),
    'customers'      => (int)db()->query("SELECT COUNT(*) FROM customer_accounts WHERE account_status='Active'")->fetchColumn(),
];

// Recent RFQs
$recent_rfqs = db()->query("SELECT id, company, contact_name, request_type, status, priority, created_at FROM rfq_requests ORDER BY id DESC LIMIT 8")->fetchAll();

layout_start('Dashboard', 'dashboard');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1>Welcome, <?= e(explode(' ', $s['name'])[0]) ?> 👋</h1>
    <p>Phoenix Arabia Staff Console — <?= date('l, F j, Y') ?></p>
  </div>
  <?php if (staff_can('view', 'rfq')): ?>
    <div class="page-head-actions">
      <a class="btn blue" href="rfq_list.php">📋 View All RFQs</a>
    </div>
  <?php endif; ?>
</div>

<!-- Key metrics -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-card-label">Total RFQs</div>
    <div class="stat-card-value"><?= $stats['rfq_total'] ?></div>
    <div class="stat-card-sub">All time</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">New RFQs</div>
    <div class="stat-card-value amber"><?= $stats['rfq_new'] ?></div>
    <div class="stat-card-sub">Awaiting review</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Reviewing</div>
    <div class="stat-card-value"><?= $stats['rfq_reviewing'] ?></div>
    <div class="stat-card-sub">In progress</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Quoted</div>
    <div class="stat-card-value green"><?= $stats['rfq_quoted'] ?></div>
    <div class="stat-card-sub">Awaiting customer</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Won Deals</div>
    <div class="stat-card-value green"><?= $stats['rfq_won'] ?></div>
    <div class="stat-card-sub">Closed successfully</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Active Products</div>
    <div class="stat-card-value"><?= $stats['products'] ?></div>
    <div class="stat-card-sub">In catalog</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Active Customers</div>
    <div class="stat-card-value"><?= $stats['customers'] ?></div>
    <div class="stat-card-sub">Registered accounts</div>
  </div>
</div>

<!-- Recent RFQs -->
<?php if (staff_can('view', 'rfq')): ?>
  <div class="page-head" style="margin-top: 8px; padding-bottom: 12px;">
    <div>
      <h1 style="font-size: 18px;">Recent RFQ Requests</h1>
      <p>Latest 8 inquiries received</p>
    </div>
    <a class="btn light" href="rfq_list.php">View All →</a>
  </div>

  <?php if (empty($recent_rfqs)): ?>
    <div class="empty-state">
      <div class="empty-state-icon">📭</div>
      <h3>No RFQs yet</h3>
      <p>When customers submit RFQs through the website, they'll appear here.</p>
    </div>
  <?php else: ?>
    <div class="data-table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Company</th>
            <th>Contact</th>
            <th>Type</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_rfqs as $r): ?>
            <tr>
              <td><b>PA-RFQ-<?= str_pad((string)$r['id'], 5, '0', STR_PAD_LEFT) ?></b></td>
              <td><?= e($r['company']) ?></td>
              <td><?= e($r['contact_name']) ?></td>
              <td><?= e($r['request_type'] ?: '—') ?></td>
              <td>
                <span class="priority-<?= strtolower($r['priority']) ?>"><?= e($r['priority']) ?></span>
              </td>
              <td>
                <span class="badge badge-<?= strtolower($r['status']) ?>"><?= e($r['status']) ?></span>
              </td>
              <td style="font-size: 12px; color: var(--pa-muted);">
                <?= date('M j, Y', strtotime($r['created_at'])) ?>
              </td>
              <td class="actions">
                <a class="btn light" href="rfq_view.php?id=<?= e($r['id']) ?>">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php
layout_end();
