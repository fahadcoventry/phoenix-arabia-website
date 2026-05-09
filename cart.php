<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

customer_required();
$c = current_customer();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'remove') {
        $item_id = (int)($_POST['item_id'] ?? 0);
        if ($item_id > 0) {
            db()->prepare("UPDATE rfq_cart_items SET status = 'Removed' WHERE id = ? AND customer_id = ?")
                ->execute([$item_id, $c['id']]);
            cust_flash_set('success', 'Item removed from cart.');
        }
    } elseif ($action === 'update') {
        foreach (($_POST['items'] ?? []) as $item_id => $data) {
            $iid = (int)$item_id;
            $qty = trim($data['quantity'] ?? '');
            $notes = trim($data['notes'] ?? '');
            if ($iid > 0) {
                db()->prepare("UPDATE rfq_cart_items SET quantity = ?, notes = ? WHERE id = ? AND customer_id = ? AND status = 'Active'")
                    ->execute([substr($qty, 0, 80), substr($notes, 0, 1000), $iid, $c['id']]);
            }
        }
        cust_flash_set('success', 'Cart updated.');
    } elseif ($action === 'clear') {
        db()->prepare("UPDATE rfq_cart_items SET status = 'Removed' WHERE customer_id = ? AND status = 'Active'")
            ->execute([$c['id']]);
        cust_flash_set('success', 'Cart cleared.');
    } elseif ($action === 'submit') {
        // Submit all active cart items as one combined RFQ
        $items_stmt = db()->prepare("SELECT * FROM rfq_cart_items WHERE customer_id = ? AND status = 'Active'");
        $items_stmt->execute([$c['id']]);
        $items = $items_stmt->fetchAll();
        
        if (empty($items)) {
            cust_flash_set('error', 'Your cart is empty.');
        } else {
            // Build details from cart
            $details_lines = [];
            foreach ($items as $it) {
                $line = '• ' . $it['product_name'];
                if ($it['quantity']) $line .= ' — Qty: ' . $it['quantity'];
                if ($it['brand']) $line .= ' [' . $it['brand'] . ']';
                if ($it['notes']) $line .= ' (Notes: ' . $it['notes'] . ')';
                $details_lines[] = $line;
            }
            $details = "Multi-Item RFQ from customer portal.\n\nItems:\n" . implode("\n", $details_lines);
            
            $extra_notes = trim($_POST['extra_notes'] ?? '');
            if (!empty($extra_notes)) {
                $details .= "\n\nAdditional Notes:\n" . $extra_notes;
            }
            
            // Create RFQ
            db()->prepare("
                INSERT INTO rfq_requests (customer_id, company, contact_name, mobile, email, request_type, product_name, details, source)
                VALUES (?, ?, ?, ?, ?, 'Multi-Item RFQ', 'Cart Submission', ?, 'Customer Portal')
            ")->execute([
                $c['id'],
                $c['company_name'] ?: $c['full_name'],
                $c['full_name'],
                $c['mobile'],
                $c['email'],
                $details
            ]);
            $rfq_id = (int)db()->lastInsertId();
            $ref = 'PA-RFQ-' . str_pad((string)$rfq_id, 5, '0', STR_PAD_LEFT);
            
            // Create order record
            db()->prepare("
                INSERT INTO customer_orders (customer_id, rfq_id, order_reference, request_type, total_items, status)
                VALUES (?, ?, ?, 'Multi-Item RFQ', ?, 'Submitted')
            ")->execute([$c['id'], $rfq_id, $ref, count($items)]);
            
            // Mark cart items as submitted
            db()->prepare("UPDATE rfq_cart_items SET status = 'Submitted' WHERE customer_id = ? AND status = 'Active'")
                ->execute([$c['id']]);
            
            cust_flash_set('success', "RFQ submitted successfully. Reference: $ref");
            header('Location: orders.php');
            exit;
        }
    }
    
    header('Location: cart.php');
    exit;
}

// Load cart items
$stmt = db()->prepare("
    SELECT ci.*, p.image_path, p.short_description, p.fixed_price, p.currency
    FROM rfq_cart_items ci
    LEFT JOIN products p ON p.id = ci.product_id
    WHERE ci.customer_id = ? AND ci.status = 'Active'
    ORDER BY ci.id DESC
");
$stmt->execute([$c['id']]);
$items = $stmt->fetchAll();

customer_layout_start('RFQ Cart', 'cart');
?>

<?= cust_flash_html() ?>

<div class="page-head">
  <div>
    <h1>RFQ Cart</h1>
    <p><?= count($items) ?> item(s) ready for combined RFQ submission</p>
  </div>
  <?php if (!empty($items)): ?>
    <div class="page-head-actions">
      <form method="post" style="display: inline;" onsubmit="return confirm('Clear all items from cart?');">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="clear">
        <button class="btn light" type="submit" style="color: #A32D2D;">Clear Cart</button>
      </form>
    </div>
  <?php endif; ?>
</div>

<?php if (empty($items)): ?>
  <div class="empty-state">
    <div class="empty-state-icon">🛒</div>
    <h3>Your cart is empty</h3>
    <p>Browse the marketplace and add products to build your RFQ.</p>
    <a class="btn blue" href="../index.php#marketplace">Browse Products →</a>
  </div>
<?php else: ?>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update">
    
    <div class="cart-list">
      <?php foreach ($items as $it): ?>
        <div class="cart-item">
          <div class="cart-item-img">
            <?php if (!empty($it['image_path'])): ?>
              <img src="../<?= e($it['image_path']) ?>" alt="" style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;">
            <?php else: ?>
              📦
            <?php endif; ?>
          </div>
          <div class="cart-item-info">
            <h4><?= e($it['product_name']) ?></h4>
            <p>
              <?= e($it['category'] ?: '—') ?>
              <?php if ($it['brand']): ?> · <?= e($it['brand']) ?><?php endif; ?>
              · <span style="color: var(--pa-blue); font-weight: 600;"><?= e($it['price_mode']) ?></span>
            </p>
            <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-top: 10px;">
              <input type="text" name="items[<?= e($it['id']) ?>][quantity]" value="<?= e($it['quantity']) ?>" placeholder="Quantity (e.g. 5T, 100pcs)" style="border: 1px solid var(--pa-border); border-radius: 6px; padding: 8px 12px; font-size: 13px; font-family: inherit; background: var(--pa-off);">
              <input type="text" name="items[<?= e($it['id']) ?>][notes]" value="<?= e($it['notes']) ?>" placeholder="Notes (specs, brand pref, delivery date...)" style="border: 1px solid var(--pa-border); border-radius: 6px; padding: 8px 12px; font-size: 13px; font-family: inherit; background: var(--pa-off);">
            </div>
          </div>
          <div class="cart-item-actions">
            <button type="submit" form="remove-form-<?= e($it['id']) ?>" class="btn light" style="color: #A32D2D; padding: 6px 12px; font-size: 12px;" title="Remove">✕ Remove</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
      <button type="submit" class="btn light">💾 Update Quantities &amp; Notes</button>
    </div>
  </form>
  
  <!-- Hidden remove forms -->
  <?php foreach ($items as $it): ?>
    <form id="remove-form-<?= e($it['id']) ?>" method="post" style="display: none;" onsubmit="return confirm('Remove this item?');">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="remove">
      <input type="hidden" name="item_id" value="<?= e($it['id']) ?>">
    </form>
  <?php endforeach; ?>
  
  <!-- Submit RFQ form -->
  <div class="form-card" style="margin-top: 24px;">
    <h2 style="font-size: 18px; font-weight: 800; color: var(--pa-blue); margin-bottom: 12px;">Submit Combined RFQ</h2>
    <p style="font-size: 13px; color: var(--pa-slate); margin-bottom: 18px; line-height: 1.6;">
      Send all <?= count($items) ?> item(s) as one consolidated RFQ. Phoenix Arabia will respond within 24 hours.
    </p>
    
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="submit">
      
      <div class="form-field">
        <label>Additional Notes (Optional)</label>
        <textarea name="extra_notes" rows="4" placeholder="Project details, delivery timeline, special requirements..."></textarea>
      </div>
      
      <div style="background: var(--pa-blue-soft); border-radius: 8px; padding: 14px; font-size: 12px; color: var(--pa-charcoal); margin-bottom: 16px;">
        <b>📞 Contact on file:</b> <?= e($c['mobile']) ?> · <?= e($c['email']) ?>
        <?php if ($c['company_name']): ?>
          <br><b>🏢 Company:</b> <?= e($c['company_name']) ?>
        <?php endif; ?>
      </div>
      
      <button type="submit" class="btn green" style="width: 100%; padding: 14px; font-size: 15px; justify-content: center;">
        📋 Submit Combined RFQ →
      </button>
    </form>
  </div>
<?php endif; ?>

<?php customer_layout_end(); ?>
