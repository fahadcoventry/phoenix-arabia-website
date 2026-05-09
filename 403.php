<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 — Access Forbidden | Phoenix Arabia</title>
<meta name="robots" content="noindex">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<nav class="nav">
  <div class="container">
    <a class="brand" href="/">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
      <div><h1>Phoenix Arabia™</h1><p>Industrial Supply &amp; RFQ Marketplace</p></div>
    </a>
  </div>
</nav>

<section class="section">
  <div class="container">
    <div class="card" style="max-width:640px;margin:80px auto;text-align:center;padding:48px">
      <div style="font-size:96px;font-weight:900;color:#A32D2D;line-height:1;margin-bottom:8px">403</div>
      <h2 style="font-size:24px;font-weight:800;color:var(--pa-black);margin-bottom:12px">Access Forbidden</h2>
      <p style="color:var(--pa-slate);margin-bottom:24px;line-height:1.7">
        You don't have permission to access this resource. If you believe this is an error, please contact us at <a href="mailto:<?= e(OFFICE_EMAIL_GENERAL) ?>"><?= e(OFFICE_EMAIL_GENERAL) ?></a>.
      </p>
      <a class="btn blue" href="/">← Back to Homepage</a>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">
    <div class="footer-credentials" style="border-top:1px solid rgba(255,255,255,0.1);padding-top:20px">
      <span>© <?= date('Y') ?> <?= e(SITE_LEGAL_NAME) ?></span>
    </div>
  </div>
</footer>
</body>
</html>
