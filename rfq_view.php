<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

staff_require('view', 'rfq');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'Invalid RFQ ID');
    header('Location: rfq_list.php');
    exit;
}

// Handle update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    staff_require('update', 'rfq');

    $new_status = $_POST['status'] ?? '';
    $new_priority = $_POST['priority'] ?? '';
    $estimated_value = $_POST['estimated_value'] ?? '';

    $allowed_statuses = ['New','Reviewing','Quoted','Won','Lost','Closed'];
    $allowed_priorities = ['Low','Normal','High','Urgent'];

    if (!in_array($new_status, $allowed_statuses, true) || !in_array($new_priority, $allowed_priorities, true)) {
        flash_set('error', 'Invalid status or priority value.');
    } else {
        $est = ($estimated_value === '' || $estimated_value === null) ? null : (float)$estimated_value;
        db()->prepare("UPDATE rfq_requests SET status = ?, priority = ?, estimated_value = ? WHERE id = ?")
            ->execute([$new_status, $new_priority, $est, $id]);
        flash_set('success', 'RFQ updated successfully.');
    }

    header('Location: rfq_view.php?id=' . $id);
    exit;
}

// Load RFQ
$stmt = db()->prepare("SELECT * FROM rfq_requests WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$rfq = $stmt->fetch();

if (!$rfq) {
    flash_set('error', 'RFQ not found.');
    header('Location: rfq_list.php');
    exit;
}

// Customer details if linked
$customer = null;
if (!empty($rfq['customer_id'])) {
    $cstmt = db()->prepare("SELECT id, full_name, company_name, mobile, email FROM customer_accounts WHERE id = ? LIMIT 1");
    $cstmt->execute([$rfq['customer_id']]);
    $customer = $cstmt->fetch();
}

$ref = 'PA-RFQ-' . str_pad((string)$rfq['id'], 5, '0', STR_PAD_LEFT);

layout_start('RFQ ' . $ref, 'rfq');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1>RFQ <?= e($ref) ?></h1>
    <p>Submitted <?= date('F j, Y \a\t g:i A', strtotime($rfq['created_at'])) ?></p>
  </div>
  <div class="page-head-actions">
    <a class="btn light" href="rfq_list.php">← Back to List</a>
  </div>
</div>

<div class="detail-grid">
  <!-- LEFT: RFQ details -->
  <div>
    <!-- Customer / contact -->
    <div class="detail-card" style="margin-bottom: 16px;">
      <h3>Contact Information</h3>
      <div class="detail-row">
        <span class="lbl">Company</span>
        <span class="val"><?= e($rfq['company']) ?></span>
      </div>
      <div class="detail-row">
        <span class="lbl">Contact Person</span>
        <span class="val"><?= e($rfq['contact_name']) ?></span>
      </div>
      <div class="detail-row">
        <span class="lbl">Mobile / WhatsApp</span>
        <span class="val">
          <a href="tel:<?= e($rfq['mobile']) ?>"><?= e($rfq['mobile']) ?></a>
          &nbsp;·&nbsp;
          <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $rfq['mobile'])) ?>" target="_blank">WhatsApp</a>
        </span>
      </div>
      <?php if (!empty($rfq['email'])): ?>
        <div class="detail-row">
          <span class="lbl">Email</span>
          <span class="val"><a href="mailto:<?= e($rfq['email']) ?>"><?= e($rfq['email']) ?></a></span>
        </div>
      <?php endif; ?>
      <?php if ($customer): ?>
        <div class="detail-row">
          <span class="lbl">Customer Account</span>
          <span class="val"><a href="customers_list.php?id=<?= e($customer['id']) ?>">View Profile</a></span>
        </div>
      <?php else: ?>
        <div class="detail-row">
          <span class="lbl">Source</span>
          <span class="val" style="color: var(--pa-muted);">Anonymous (no account)</span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Request details -->
    <div class="detail-card" style="margin-bottom: 16px;">
      <h3>Request Details</h3>
      <div class="detail-row">
        <span class="lbl">Type</span>
        <span class="val"><?= e($rfq['request_type'] ?: '—') ?></span>
      </div>
      <?php if (!empty($rfq['product_name'])): ?>
        <div class="detail-row">
          <span class="lbl">Product / Package</span>
          <span class="val"><?= e($rfq['product_name']) ?></span>
        </div>
      <?php endif; ?>
      <?php if (!empty($rfq['file_path'])): ?>
        <div class="detail-row">
          <span class="lbl">Attached File</span>
          <span class="val">
            <a href="../<?= e($rfq['file_path']) ?>" target="_blank">📄 Download</a>
          </span>
        </div>
      <?php endif; ?>
      <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--pa-border-soft);">
        <div style="font-size: 11px; color: var(--pa-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 8px;">
          Customer Notes
        </div>
        <div style="font-size: 13px; color: var(--pa-charcoal); line-height: 1.7; white-space: pre-wrap;"><?= e($rfq['details'] ?: 'No additional details provided.') ?></div>
      </div>
    </div>
  </div>

  <!-- RIGHT: Status panel -->
  <div>
    <?php if (staff_can('update', 'rfq')): ?>
      <div class="detail-card">
        <h3>Update Status</h3>
        <form method="post">
          <?= csrf_field() ?>

          <div class="form-field">
            <label>Status</label>
            <select name="status">
              <?php foreach (['New','Reviewing','Quoted','Won','Lost','Closed'] as $st): ?>
                <option value="<?= $st ?>" <?= $rfq['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-field">
            <label>Priority</label>
            <select name="priority">
              <?php foreach (['Low','Normal','High','Urgent'] as $pr): ?>
                <option value="<?= $pr ?>" <?= $rfq['priority'] === $pr ? 'selected' : '' ?>><?= $pr ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-field">
            <label>Estimated Value (SAR)</label>
            <input type="number" step="0.01" min="0" name="estimated_value" value="<?= e($rfq['estimated_value'] ?? '') ?>" placeholder="0.00">
            <div class="hint">Optional — for pipeline forecasting</div>
          </div>

          <button type="submit" class="btn blue" style="width: 100%;">Save Changes</button>
        </form>
      </div>
    <?php else: ?>
      <div class="detail-card">
        <h3>Current Status</h3>
        <div class="detail-row">
          <span class="lbl">Status</span>
          <span class="val"><span class="badge badge-<?= strtolower($rfq['status']) ?>"><?= e($rfq['status']) ?></span></span>
        </div>
        <div class="detail-row">
          <span class="lbl">Priority</span>
          <span class="val priority-<?= strtolower($rfq['priority']) ?>"><?= e($rfq['priority']) ?></span>
        </div>
        <?php if (!empty($rfq['estimated_value'])): ?>
          <div class="detail-row">
            <span class="lbl">Est. Value</span>
            <span class="val"><?= number_format((float)$rfq['estimated_value'], 2) ?> SAR</span>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Quick actions -->
    <div class="detail-card" style="margin-top: 16px;">
      <h3>Quick Actions</h3>
      <a href="tel:<?= e($rfq['mobile']) ?>" class="btn blue" style="width: 100%; margin-bottom: 8px; justify-content: center;">📞 Call</a>
      <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $rfq['mobile'])) ?>" target="_blank" class="btn green" style="width: 100%; margin-bottom: 8px; justify-content: center;">💬 WhatsApp</a>
      <?php if (!empty($rfq['email'])): ?>
        <a href="mailto:<?= e($rfq['email']) ?>?subject=Re: RFQ <?= e($ref) ?>" class="btn light" style="width: 100%; justify-content: center;">✉ Email</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php layout_end(); ?>
