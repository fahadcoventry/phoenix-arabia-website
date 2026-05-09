<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 — Page Not Found | Phoenix Arabia</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
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
      <div style="font-size:96px;font-weight:900;color:var(--pa-blue);line-height:1;margin-bottom:8px">404</div>
      <h2 style="font-size:24px;font-weight:800;color:var(--pa-black);margin-bottom:12px">Page Not Found</h2>
      <p style="color:var(--pa-slate);margin-bottom:24px;line-height:1.7">
        The page you're looking for doesn't exist or has been moved. If you arrived here via a link from another website, please let us know.
      </p>
      <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
        <a class="btn blue" href="/">← Back to Homepage</a>
        <a class="btn outline" href="/index.php#marketplace">Browse Marketplace</a>
        <a class="btn green" href="/index.php#rfq">Submit RFQ</a>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">
    <div class="footer-credentials" style="border-top:1px solid rgba(255,255,255,0.1);padding-top:20px">
      <span>© <?= date('Y') ?> <?= e(SITE_LEGAL_NAME) ?></span>
      <div class="creds">
        <span>C.R. <?= e(CR_NUMBER) ?></span>
        <span>U.N. <?= e(UNIFIED_NUMBER) ?></span>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
