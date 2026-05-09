<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/seo.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$stmt = db()->prepare("SELECT * FROM products WHERE slug = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$p = $stmt->fetch();

if (!$p) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Related products (same category, exclude current)
$related_stmt = db()->prepare("SELECT id, slug, name, short_description, category, image_path, price_mode FROM products WHERE category = ? AND id <> ? AND is_active = 1 LIMIT 4");
$related_stmt->execute([$p['category'], $p['id']]);
$related = $related_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= seo_title($p['seo_title'] ?: $p['name']) ?></title>
<meta name="description" content="<?= seo_description($p['meta_description'] ?: $p['short_description']) ?>">
<?php if (!empty($p['keywords'])): ?>
<meta name="keywords" content="<?= e($p['keywords']) ?>">
<?php endif; ?>
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= canonical('product.php?slug=' . $p['slug']) ?>">

<?= og_tags($p['name'], $p['short_description'] ?? '', SITE_URL . '/' . ltrim($p['image_path'] ?: 'assets/placeholder-product.svg', '/')) ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<?= organization_schema() ?>
<?= product_schema($p) ?>
<?= breadcrumb_schema([
    ['name' => 'Home',                  'url' => SITE_URL],
    ['name' => $p['category'] ?? 'Marketplace', 'url' => SITE_URL . '/index.php#marketplace'],
    ['name' => $p['name'],              'url' => SITE_URL . '/product.php?slug=' . $p['slug']],
]) ?>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="container">
    <div>📍 <?= e(OFFICE_ADDRESS) ?></div>
    <div>
      📞 <a href="tel:<?= e(OFFICE_PHONE_RAW) ?>"><?= e(OFFICE_PHONE) ?></a> &nbsp;|&nbsp;
      ✉ <a href="mailto:<?= e(OFFICE_EMAIL_RFQ) ?>"><?= e(OFFICE_EMAIL_RFQ) ?></a>
    </div>
  </div>
</div>

<!-- NAV -->
<nav class="nav">
  <div class="container">
    <a class="brand" href="index.php">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
      <div><h1>Phoenix Arabia™</h1><p>Industrial Supply &amp; RFQ Marketplace</p></div>
    </a>
    <div class="links">
      <a href="index.php#marketplace">Marketplace</a>
      <a href="index.php#brands">Brands</a>
      <a href="index.php#industries">Industries</a>
      <a href="articles.php">Articles</a>
      <a class="btn green" href="add_to_cart.php?product_id=<?= e($p['id']) ?>">Add to RFQ</a>
    </div>
  </div>
</nav>

<!-- BREADCRUMBS -->
<div class="container" style="padding-top:18px;font-size:12px;color:var(--pa-muted)">
  <a href="index.php" style="color:var(--pa-muted)">Home</a> &raquo;
  <a href="index.php#marketplace" style="color:var(--pa-muted)"><?= e($p['category']) ?></a> &raquo;
  <span style="color:var(--pa-charcoal)"><?= e($p['name']) ?></span>
</div>

<!-- PRODUCT DETAIL -->
<section class="section" style="padding-top:24px">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:32px" class="product-detail-grid">
      <!-- Image -->
      <div class="card" style="padding:24px;text-align:center">
        <img src="<?= e($p['image_path'] ?: 'assets/placeholder-product.svg') ?>"
             alt="<?= e($p['name']) ?>"
             style="width:100%;max-height:340px;object-fit:contain;border-radius:8px">
      </div>

      <!-- Info -->
      <div>
        <span class="pill"><?= e($p['category']) ?></span>
        <h1 style="font-size:30px;font-weight:800;color:var(--pa-black);margin:8px 0 16px;line-height:1.2"><?= e($p['name']) ?></h1>

        <?php if (!empty($p['sku'])): ?>
          <p style="font-size:13px;color:var(--pa-muted);margin-bottom:12px">SKU: <b><?= e($p['sku']) ?></b></p>
        <?php endif; ?>

        <p style="color:var(--pa-charcoal);line-height:1.7;margin-bottom:24px"><?= nl2br(e($p['description'] ?: $p['short_description'])) ?></p>

        <!-- Specs grid -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px">
          <div style="background:var(--pa-bg);border:1px solid var(--pa-border);border-radius:8px;padding:14px">
            <div style="font-size:11px;color:var(--pa-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;margin-bottom:4px">Brand</div>
            <div style="font-size:14px;color:var(--pa-black);font-weight:700"><?= e($p['brand'] ?: 'Phoenix Arabia') ?></div>
          </div>
          <div style="background:var(--pa-bg);border:1px solid var(--pa-border);border-radius:8px;padding:14px">
            <div style="font-size:11px;color:var(--pa-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;margin-bottom:4px">Price Mode</div>
            <div style="font-size:14px;color:var(--pa-blue);font-weight:700"><?= e($p['price_mode']) ?></div>
          </div>
          <div style="background:var(--pa-bg);border:1px solid var(--pa-border);border-radius:8px;padding:14px">
            <div style="font-size:11px;color:var(--pa-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;margin-bottom:4px">Stock</div>
            <div style="font-size:14px;color:var(--pa-green);font-weight:700"><?= e($p['stock_status']) ?></div>
          </div>
          <?php if (!empty($p['lead_time'])): ?>
            <div style="background:var(--pa-bg);border:1px solid var(--pa-border);border-radius:8px;padding:14px">
              <div style="font-size:11px;color:var(--pa-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;margin-bottom:4px">Lead Time</div>
              <div style="font-size:14px;color:var(--pa-black);font-weight:700"><?= e($p['lead_time']) ?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($p['minimum_order_qty'])): ?>
            <div style="background:var(--pa-bg);border:1px solid var(--pa-border);border-radius:8px;padding:14px">
              <div style="font-size:11px;color:var(--pa-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;margin-bottom:4px">Min. Order</div>
              <div style="font-size:14px;color:var(--pa-black);font-weight:700"><?= e($p['minimum_order_qty']) ?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($p['unit'])): ?>
            <div style="background:var(--pa-bg);border:1px solid var(--pa-border);border-radius:8px;padding:14px">
              <div style="font-size:11px;color:var(--pa-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;margin-bottom:4px">Unit</div>
              <div style="font-size:14px;color:var(--pa-black);font-weight:700"><?= e($p['unit']) ?></div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Price block -->
        <?php if ($p['price_mode'] === 'Show Price' && !empty($p['fixed_price'])): ?>
          <div style="background:var(--pa-blue-soft);border:1px solid rgba(30,58,138,0.2);border-radius:12px;padding:20px;margin-bottom:20px">
            <div style="font-size:11px;color:var(--pa-blue);text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin-bottom:6px">Listed Price</div>
            <div style="font-size:28px;font-weight:800;color:var(--pa-blue)"><?= e($p['currency'] ?? 'SAR') ?> <?= number_format((float)$p['fixed_price'], 2) ?></div>
            <div style="font-size:12px;color:var(--pa-muted);margin-top:4px">Final pricing subject to MTO confirmation and project terms.</div>
          </div>
        <?php else: ?>
          <div style="background:var(--pa-green-soft);border:1px solid var(--pa-green-border);border-radius:12px;padding:20px;margin-bottom:20px">
            <div style="font-size:11px;color:var(--pa-green-dark);text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin-bottom:6px">Pricing Mode: <?= e($p['price_mode']) ?></div>
            <div style="font-size:14px;color:var(--pa-charcoal);line-height:1.6">Submit an RFQ to receive a tailored commercial quotation from Phoenix Arabia within 24 hours.</div>
          </div>
        <?php endif; ?>

        <!-- Supply channel notice -->
        <p style="font-size:13px;color:var(--pa-muted);margin-bottom:20px">
          <b>Supply Channel:</b>
          <?= $p['manufacturer_visible'] && !empty($p['manufacturer_name'])
              ? e($p['manufacturer_name'])
              : 'Phoenix Arabia Protected Supply Channel' ?>
        </p>

        <!-- Actions -->
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <a class="btn green" href="add_to_cart.php?product_id=<?= e($p['id']) ?>">Add to RFQ</a>
          <a class="btn blue" href="index.php#rfq">Quick RFQ</a>
          <?php if (!empty($p['datasheet_path'])): ?>
            <a class="btn light" href="<?= e($p['datasheet_path']) ?>" target="_blank" rel="noopener">📄 Datasheet</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
      <div class="head" style="margin-top:64px">
        <div>
          <h2>Related Products</h2>
          <p>More from <?= e($p['category']) ?></p>
        </div>
      </div>
      <div class="products">
        <?php foreach ($related as $r): ?>
          <div class="product">
            <div class="pimg">
              <img src="<?= e($r['image_path'] ?: 'assets/placeholder-product.svg') ?>" alt="<?= e($r['name']) ?>" loading="lazy">
            </div>
            <div class="pbody">
              <div class="tag"><?= e($r['category']) ?></div>
              <h3><a href="product.php?slug=<?= e($r['slug']) ?>"><?= e($r['name']) ?></a></h3>
              <p><?= e($r['short_description']) ?></p>
              <div class="pfoot">
                <span class="request"><?= e($r['price_mode']) ?></span>
                <a class="btn blue" href="add_to_cart.php?product_id=<?= e($r['id']) ?>">Add to RFQ</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="footer-credentials" style="border-top:1px solid rgba(255,255,255,0.1);padding-top:20px">
      <span>© <?= date('Y') ?> <?= e(SITE_LEGAL_NAME) ?></span>
      <div class="creds">
        <span>C.R. <?= e(CR_NUMBER) ?></span>
        <span>U.N. <?= e(UNIFIED_NUMBER) ?></span>
        <span>Aramco Vendor <?= e(ARAMCO_VENDOR) ?></span>
      </div>
    </div>
  </div>
</footer>

<style>
  @media (max-width: 900px) {
    .product-detail-grid { grid-template-columns: 1fr !important; }
  }
</style>

</body>
</html>
