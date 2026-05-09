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

$stmt = db()->prepare("SELECT * FROM brands WHERE slug = ? AND is_visible = 1 LIMIT 1");
$stmt->execute([$slug]);
$b = $stmt->fetch();

if (!$b) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$products_stmt = db()->prepare("SELECT id, slug, name, short_description, category, image_path, price_mode FROM products WHERE brand = ? AND is_active = 1 ORDER BY is_featured DESC, id DESC");
$products_stmt->execute([$b['brand_name']]);
$products = $products_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= seo_title($b['seo_title'] ?: $b['brand_name'] . ' Supplier in Saudi Arabia') ?></title>
<meta name="description" content="<?= seo_description($b['meta_description']) ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= canonical('brand.php?slug=' . $b['slug']) ?>">

<?= og_tags($b['brand_name'] . ' | Phoenix Arabia', $b['public_description'] ?? '') ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<?= organization_schema() ?>
<?= breadcrumb_schema([
    ['name' => 'Home',            'url' => SITE_URL],
    ['name' => 'Brands',          'url' => SITE_URL . '/index.php#brands'],
    ['name' => $b['brand_name'],  'url' => SITE_URL . '/brand.php?slug=' . $b['slug']],
]) ?>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="container">
    <div>📍 <?= e(OFFICE_ADDRESS) ?></div>
    <div>📞 <a href="tel:<?= e(OFFICE_PHONE_RAW) ?>"><?= e(OFFICE_PHONE) ?></a></div>
  </div>
</div>

<!-- NAV -->
<nav class="nav">
  <div class="container">
    <a class="brand" href="index.php">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
      <div><h1>Phoenix Arabia™</h1><p>Brand Page</p></div>
    </a>
    <div class="links">
      <a href="index.php#marketplace">Marketplace</a>
      <a href="index.php#brands">Brands</a>
      <a href="articles.php">Articles</a>
      <a class="btn green" href="index.php#rfq">Submit RFQ</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero" style="padding:60px 0">
  <div class="container">
    <span class="kicker">Brand Supply Channel · Phoenix Arabia</span>
    <h2>
      <?= e($b['brand_name']) ?><br>
      <span>Supplier in Saudi Arabia</span>
    </h2>
    <p style="max-width:720px"><?= e($b['public_description']) ?></p>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px">
      <a class="btn light" href="#products">View Products</a>
      <a class="btn green" href="index.php#rfq">Request Quotation</a>
    </div>
  </div>
</section>

<!-- PRODUCTS -->
<section class="section" id="products">
  <div class="container">
    <div class="head">
      <div>
        <h2><?= e($b['brand_name']) ?> Products</h2>
        <p>All inquiries managed through Phoenix Arabia RFQ Desk.</p>
      </div>
      <a class="btn green" href="index.php#rfq">Multi-Item Quote</a>
    </div>

    <?php if (empty($products)): ?>
      <div class="card" style="text-align:center;padding:48px">
        <p style="color:var(--pa-slate);margin-bottom:16px">No products listed under this brand yet.</p>
        <a class="btn blue" href="index.php#rfq">Submit a custom RFQ</a>
      </div>
    <?php else: ?>
      <div class="products">
        <?php foreach ($products as $p): ?>
          <div class="product">
            <div class="pimg">
              <img src="<?= e($p['image_path'] ?: 'assets/placeholder-product.svg') ?>" alt="<?= e($p['name']) ?>" loading="lazy">
            </div>
            <div class="pbody">
              <div class="tag"><?= e($p['category']) ?></div>
              <h3><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
              <p><?= e($p['short_description']) ?></p>
              <div class="pfoot">
                <span class="request"><?= e($p['price_mode']) ?></span>
                <a class="btn blue" href="add_to_cart.php?product_id=<?= e($p['id']) ?>">Add to RFQ</a>
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
</body>
</html>
