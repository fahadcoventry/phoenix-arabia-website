<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/seo.php';

$articles = db()->query("SELECT * FROM technical_articles WHERE is_published = 1 ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= seo_title('Technical Articles | Phoenix Arabia Industrial Procurement Insights') ?></title>
<meta name="description" content="<?= seo_description('Technical procurement articles, MTO insights, RFQ guides, and industrial supply commentary for buyers and EPC contractors in Saudi Arabia.') ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= canonical('articles.php') ?>">

<?= og_tags('Technical Articles | Phoenix Arabia', 'Procurement insights for Saudi industrial buyers and EPC contractors.') ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<?= organization_schema() ?>
</head>
<body>

<div class="topbar">
  <div class="container">
    <div>📍 <?= e(OFFICE_ADDRESS) ?></div>
    <div>📞 <a href="tel:<?= e(OFFICE_PHONE_RAW) ?>"><?= e(OFFICE_PHONE) ?></a></div>
  </div>
</div>

<nav class="nav">
  <div class="container">
    <a class="brand" href="index.php">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
      <div><h1>Phoenix Arabia™</h1><p>Technical Article Engine</p></div>
    </a>
    <div class="links">
      <a href="index.php#marketplace">Marketplace</a>
      <a href="index.php#brands">Brands</a>
      <a href="index.php#industries">Industries</a>
      <a class="btn green" href="index.php#rfq">Submit RFQ</a>
    </div>
  </div>
</nav>

<section class="hero" style="padding:60px 0">
  <div class="container">
    <span class="kicker">Technical Articles · Procurement Insights</span>
    <h2>Industrial Procurement <span>Knowledge Base</span></h2>
    <p style="max-width:720px">Practical articles on RFQ workflows, MTO pricing, supplier coordination, and industrial procurement for buyers and EPC contractors in Saudi Arabia.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="head">
      <div>
        <h2>All Articles</h2>
        <p><?= count($articles) ?> articles published</p>
      </div>
    </div>
    <?php if (empty($articles)): ?>
      <div class="card" style="text-align:center;padding:48px">
        <p style="color:var(--pa-slate)">No articles published yet. Check back soon.</p>
      </div>
    <?php else: ?>
      <div class="grid3">
        <?php foreach ($articles as $a): ?>
          <div class="card">
            <span class="pill"><?= e($a['category']) ?></span>
            <h3><a href="article.php?slug=<?= e($a['slug']) ?>"><?= e($a['title']) ?></a></h3>
            <p><?= e($a['excerpt']) ?></p>
            <br>
            <a class="btn outline" href="article.php?slug=<?= e($a['slug']) ?>">Read Article →</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

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
