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

$stmt = db()->prepare("SELECT * FROM industry_pages WHERE slug = ? AND is_visible = 1 LIMIT 1");
$stmt->execute([$slug]);
$i = $stmt->fetch();

if (!$i) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Featured products to display under this industry
$products = db()->query("SELECT id, slug, name, short_description, category, image_path, price_mode FROM products WHERE is_active = 1 ORDER BY is_featured DESC, id DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= seo_title($i['seo_title'] ?: $i['industry_name']) ?></title>
<meta name="description" content="<?= seo_description($i['meta_description']) ?>">
<?php if (!empty($i['keywords'])): ?>
<meta name="keywords" content="<?= e($i['keywords']) ?>">
<?php endif; ?>
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= canonical('industry.php?slug=' . $i['slug']) ?>">

<?= og_tags($i['industry_name'] . ' | Phoenix Arabia', $i['intro'] ?? '') ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<?= organization_schema() ?>
<?= breadcrumb_schema([
    ['name' => 'Home',                  'url' => SITE_URL],
    ['name' => 'Industries',            'url' => SITE_URL . '/index.php#industries'],
    ['name' => $i['industry_name'],     'url' => SITE_URL . '/industry.php?slug=' . $i['slug']],
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
      <div><h1>Phoenix Arabia™</h1><p>Industry Page</p></div>
    </a>
    <div class="links">
      <a href="index.php#marketplace">Marketplace</a>
      <a href="index.php#industries">Industries</a>
      <a href="articles.php">Articles</a>
      <a class="btn green" href="index.php#rfq">Submit RFQ</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero" style="padding:60px 0">
  <div class="container">
    <span class="kicker">Saudi Industrial Sector · Phoenix Arabia</span>
    <h2><?= e($i['headline'] ?: $i['industry_name']) ?></h2>
    <p style="max-width:720px"><?= e($i['intro']) ?></p>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px">
      <a class="btn green" href="index.php#rfq">Submit RFQ</a>
      <a class="btn light" href="#related">Related Products</a>
    </div>
  </div>
</section>

<!-- INDUSTRY VALUE PROPOSITIONS -->
<section class="section">
  <div class="container">
    <div class="grid3">
      <div class="card">
        <div class="icon">📋</div>
        <h3>RFQ Support</h3>
        <p>Submit BOQ or MTO and Phoenix Arabia will consolidate supplier pricing through one commercial response.</p>
      </div>
      <div class="card">
        <div class="icon">🛡️</div>
        <h3>Protected Supplier Network</h3>
        <p>Supplier coordination remains internal. All commercial dialogue happens through Phoenix Arabia.</p>
      </div>
      <div class="card">
        <div class="icon">🏗️</div>
        <h3>Project Procurement</h3>
        <p>Support for EPC contractors, industrial plants, and large industrial buyers across the Kingdom.</p>
      </div>
    </div>
  </div>
</section>

<!-- RELATED PRODUCTS -->
<section class="section" id="related" style="background:var(--pa-bg)">
  <div class="container">
    <div class="head">
      <div>
        <h2>Relevant Products</h2>
        <p>A selection of products commonly requested by <?= e($i['industry_name']) ?> projects.</p>
      </div>
      <a class="btn green" href="index.php#rfq">Multi-Item RFQ</a>
    </div>

    <?php if (empty($products)): ?>
      <div class="card" style="text-align:center;padding:48px">
        <p style="color:var(--pa-slate)">No products are listed at this time. Please submit an RFQ for a tailored response.</p>
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
