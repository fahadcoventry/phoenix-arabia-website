<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

customer_required();
$c = current_customer();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: orders.php');
    exit;
}

// Load order (must belong to this customer)
$stmt = db()->prepare("SELECT * FROM customer_orders WHERE id = ? AND customer_id = ? LIMIT 1");
$stmt->execute([$id, $c['id']]);
$order = $stmt->fetch();

if (!$order) {
    cust_flash_set('error', 'Order not found.');
    header('Location: orders.php');
    exit;
}

// Load related RFQ
$rfq = null;
if (!empty($order['rfq_id'])) {
    $rstmt = db()->prepare("SELECT * FROM rfq_requests WHERE id = ? LIMIT 1");
    $rstmt->execute([$order['rfq_id']]);
    $rfq = $rstmt->fetch();
}

customer_layout_start('Order ' . $order['order_reference'], 'orders');
?>

<?= cust_flash_html() ?>

<div class="page-head">
  <div>
    <h1>RFQ <?= e($order['order_reference']) ?></h1>
    <p>Submitted <?= date('F j, Y \a\t g:i A', strtotime($order['submitted_at'])) ?></p>
  </div>
  <a class="btn light" href="orders.php">← Back to Orders</a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;" class="order-grid">
  <!-- Left: Details -->
  <div>
    <div class="form-card" style="margin-bottom: 16px;">
      <h2 style="font-size: 16px; font-weight: 800; color: var(--pa-blue); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid var(--pa-blue-soft);">Order Details</h2>
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div>
          <div style="font-size: 11px; color: var(--pa-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 4px;">Reference</div>
          <div style="font-size: 16px; font-weight: 800; color: var(--pa-blue);"><?= e($order['order_reference']) ?></div>
        </div>
        <div>
          <div style="font-size: 11px; color: var(--pa-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 4px;">Status</div>
          <div><span class="badge badge-<?= strtolower($order['status']) ?>"><?= e($order['status']) ?></span></div>
        </div>
        <div>
          <div style="font-size: 11px; color: var(--pa-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 4px;">Type</div>
          <div style="font-size: 14px; font-weight: 600;"><?= e($order['request_type'] ?: '—') ?></div>
        </div>
        <div>
          <div style="font-size: 11px; color: var(--pa-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 4px;">Items</div>
          <div style="font-size: 14px; font-weight: 600;"><?= e($order['total_items']) ?></div>
        </div>
      </div>
    </div>
    
    <?php if ($rfq): ?>
      <div class="form-card">
        <h2 style="font-size: 16px; font-weight: 800; color: var(--pa-blue); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid var(--pa-blue-soft);">RFQ Submission</h2>
        
        <?php if (!empty($rfq['product_name'])): ?>
          <div style="margin-bottom: 16px;">
            <div style="font-size: 11px; color: var(--pa-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 4px;">Product / Package</div>
            <div style="font-size: 14px; color: var(--pa-charcoal);"><?= e($rfq['product_name']) ?></div>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($rfq['file_path'])): ?>
          <div style="margin-bottom: 16px;">
            <div style="font-size: 11px; color: var(--pa-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 4px;">Attached File</div>
            <a href="../<?= e($rfq['file_path']) ?>" target="_blank" class="btn light" style="font-size: 12px;">📄 Download Attachment</a>
          </div>
        <?php endif; ?>
        
        <div>
          <div style="font-size: 11px; color: var(--pa-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 8px;">Details Submitted</div>
          <div style="font-size: 13px; color: var(--pa-charcoal); line-height: 1.7; white-space: pre-wrap; background: var(--pa-bg); padding: 14px; border-radius: 6px;"><?= e($rfq['details'] ?: 'No additional details provided.') ?></div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Right: Status / Contact -->
  <div>
    <div class="form-card" style="margin-bottom: 16px;">
      <h2 style="font-size: 14px; font-weight: 800; color: var(--pa-blue); margin-bottom: 16px;">Status Timeline</h2>
      
      <?php
      $statuses = ['Submitted', 'Reviewing', 'Quoted', 'Won'];
      $current = $order['status'];
      $current_idx = array_search($current, $statuses);
      ?>
      <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach ($statuses as $idx => $st): ?>
          <?php
          $is_current = ($current === $st);
          $is_past = ($current_idx !== false && $idx < $current_idx);
          $color = $is_current ? 'var(--pa-blue)' : ($is_past ? 'var(--pa-green)' : 'var(--pa-muted)');
          $bg = $is_current ? 'var(--pa-blue-soft)' : ($is_past ? 'var(--pa-green-soft)' : 'var(--pa-bg)');
          ?>
          <div style="display: flex; align-items: center; gap: 10px; padding: 10px; background: <?= $bg ?>; border-radius: 6px;">
            <div style="width: 24px; height: 24px; border-radius: 50%; background: <?= $color ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">
              <?= $is_past ? '✓' : ($is_current ? '●' : ($idx + 1)) ?>
            </div>
            <span style="font-size: 13px; font-weight: <?= $is_current ? '700' : '500' ?>; color: <?= $color ?>;">
              <?= $st ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
      
      <?php if (in_array($order['status'], ['Lost', 'Closed'])): ?>
        <div style="margin-top: 12px; padding: 10px; background: #FEE2E2; border-radius: 6px; font-size: 12px; color: #991B1B; text-align: center;">
          Status: <b><?= e($order['status']) ?></b>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="form-card">
      <h2 style="font-size: 14px; font-weight: 800; color: var(--pa-blue); margin-bottom: 16px;">Need Help?</h2>
      <p style="font-size: 12px; color: var(--pa-slate); line-height: 1.6; margin-bottom: 12px;">
        For questions about this RFQ, reach out to our team:
      </p>
      <a href="tel:+966533033352" class="btn blue" style="width: 100%; margin-bottom: 6px; justify-content: center; font-size: 12px;">📞 +966 53 303 3352</a>
      <a href="https://wa.me/966597711094" target="_blank" class="btn green" style="width: 100%; margin-bottom: 6px; justify-content: center; font-size: 12px;">💬 WhatsApp</a>
      <a href="mailto:rfq@phoenix.com.sa?subject=Re: <?= e($order['order_reference']) ?>" class="btn light" style="width: 100%; justify-content: center; font-size: 12px;">✉ Email</a>
    </div>
  </div>
</div>

<style>
@media (max-width: 1024px) {
  .order-grid { grid-template-columns: 1fr !important; }
}
</style>

<?php customer_layout_end(); ?>
