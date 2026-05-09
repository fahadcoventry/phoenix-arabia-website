<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// CSRF check
require_csrf();

// Rate limiting (3 RFQs per IP per 10 minutes)
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!rate_limit_check('rfq_' . $ip, 3, 600)) {
    http_response_code(429);
    die('Too many submissions. Please wait a few minutes and try again.');
}

// Collect & sanitize
$company  = trim($_POST['company']      ?? '');
$contact  = trim($_POST['contact_name'] ?? '');
$mobile   = normalize_mobile($_POST['mobile'] ?? '');
$email    = trim($_POST['email']        ?? '');
$type     = trim($_POST['request_type'] ?? '');
$product  = trim($_POST['product_name'] ?? '');
$details  = trim($_POST['details']      ?? '');

// Validation
$errors = [];
if (empty($company))  $errors[] = 'Company name is required';
if (empty($contact))  $errors[] = 'Contact person is required';
if (!valid_mobile($mobile)) $errors[] = 'Valid mobile number is required';
if (!empty($email) && !valid_email($email)) $errors[] = 'Email format is invalid';

// Length limits
if (strlen($company) > 190)  $errors[] = 'Company name too long';
if (strlen($contact) > 160)  $errors[] = 'Contact name too long';
if (strlen($details) > 5000) $errors[] = 'Details too long';

if (!empty($errors)) {
    http_response_code(400);
    ?>
    <!DOCTYPE html><html><head><meta charset="UTF-8"><title>Submission Error</title>
    <link rel="stylesheet" href="assets/style.css"></head><body>
    <section class="section"><div class="container">
      <div class="card" style="max-width:640px;margin:80px auto;text-align:center">
        <h2 style="color:#A32D2D">Submission Could Not Be Completed</h2>
        <ul style="text-align:left;margin:16px auto;max-width:400px">
          <?php foreach($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
        <a class="btn blue" href="index.php#rfq">← Back to RFQ Form</a>
      </div>
    </div></section></body></html>
    <?php
    exit;
}

// Handle file upload (with security checks)
$file_path = null;
try {
    $file_path = upload_file('boq_file', 'boq');
} catch (RuntimeException $e) {
    http_response_code(400);
    die('File upload failed: ' . e($e->getMessage()));
}

// Insert RFQ
try {
    $stmt = db()->prepare("
        INSERT INTO rfq_requests
        (customer_id, company, contact_name, mobile, email, request_type, product_name, details, file_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        current_customer_id() ?: null,
        $company, $contact, $mobile, $email, $type, $product, $details, $file_path
    ]);
    $rfq_id = (int)db()->lastInsertId();
} catch (PDOException $e) {
    error_log('RFQ insert error: ' . $e->getMessage());
    die('Submission failed. Please contact us at ' . e(OFFICE_EMAIL_RFQ));
}

// If logged-in customer, also create a customer order record
if (current_customer_id()) {
    $ref = 'PA-RFQ-' . str_pad((string)$rfq_id, 5, '0', STR_PAD_LEFT);
    db()->prepare("
        INSERT INTO customer_orders (customer_id, rfq_id, order_reference, request_type, total_items, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ")->execute([current_customer_id(), $rfq_id, $ref, $type, 0, 'Submitted']);
    
    db()->prepare("
        UPDATE rfq_cart_items SET status='Submitted' WHERE customer_id=? AND status='Active'
    ")->execute([current_customer_id()]);
}

// Regenerate CSRF token (single-use)
unset($_SESSION['csrf_token']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RFQ Submitted Successfully | Phoenix Arabia</title>
<meta name="robots" content="noindex">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar">
  <div class="container">
    <div>📍 <?= e(OFFICE_ADDRESS) ?></div>
    <div>📞 <?= e(OFFICE_PHONE) ?></div>
  </div>
</div>
<nav class="nav">
  <div class="container">
    <a class="brand" href="index.php">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
      <div><h1>Phoenix Arabia™</h1><p>RFQ Confirmation</p></div>
    </a>
  </div>
</nav>

<section class="section">
  <div class="container">
    <div class="card" style="max-width:680px;margin:60px auto;text-align:center;padding:48px">
      <div style="font-size:48px;margin-bottom:12px">✓</div>
      <h2 style="color:var(--pa-green);font-size:28px;margin-bottom:8px">RFQ Submitted Successfully</h2>
      <p style="font-size:16px;color:var(--pa-slate);margin-bottom:8px">Your reference number:</p>
      <p style="font-size:28px;font-weight:800;color:var(--pa-blue);letter-spacing:2px;margin-bottom:24px">
        PA-RFQ-<?= str_pad((string)$rfq_id, 5, '0', STR_PAD_LEFT) ?>
      </p>
      <p style="color:var(--pa-slate);max-width:480px;margin:0 auto 24px;line-height:1.7">
        Our RFQ Desk will review your request and respond within <b>24 hours</b> (working days). For urgent matters, contact us directly:
      </p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:32px">
        <a class="btn blue" href="tel:<?= e(OFFICE_PHONE_RAW) ?>">📞 <?= e(OFFICE_PHONE) ?></a>
        <a class="btn green" href="https://wa.me/<?= e(OFFICE_WHATSAPP_RAW) ?>">💬 WhatsApp</a>
        <a class="btn light" href="mailto:<?= e(OFFICE_EMAIL_RFQ) ?>">✉ Email</a>
      </div>
      <hr style="border:0;border-top:1px solid var(--pa-border);margin:24px 0">
      <p style="font-size:12px;color:var(--pa-muted)">
        C.R. <?= e(CR_NUMBER) ?> &nbsp;·&nbsp; U.N. <?= e(UNIFIED_NUMBER) ?> &nbsp;·&nbsp; Aramco Vendor <?= e(ARAMCO_VENDOR) ?>
      </p>
      <div style="margin-top:24px">
        <a class="btn outline" href="index.php">← Back to Marketplace</a>
        <?php if (current_customer_id()): ?>
          <a class="btn green" href="customer/dashboard.php">My Account</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
</body>
</html>
