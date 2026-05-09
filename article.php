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

$stmt = db()->prepare("SELECT * FROM technical_articles WHERE slug = ? AND is_published = 1 LIMIT 1");
$stmt->execute([$slug]);
$a = $stmt->fetch();

if (!$a) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Article schema
$article_schema = [
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => $a['title'],
    'description'   => $a['excerpt'],
    'datePublished' => $a['created_at'] ?? date('c'),
    'author'        => ['@type' => 'Organization', 'name' => SITE_LEGAL_NAME],
    'publisher'     => [
        '@type' => 'Organization',
        'name'  => SITE_LEGAL_NAME,
        'logo'  => ['@type' => 'ImageObject', 'url' => SITE_URL . '/assets/logo.png'],
    ],
    'mainEntityOfPage' => SITE_URL . '/article.php?slug=' . $a['slug'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= seo_title($a['seo_title'] ?: $a['title']) ?></title>
<meta name="description" content="<?= seo_description($a['meta_description'] ?: $a['excerpt']) ?>">
<?php if (!empty($a['keywords'])): ?>
<meta name="keywords" content="<?= e($a['keywords']) ?>">
<?php endif; ?>
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= canonical('article.php?slug=' . $a['slug']) ?>">

<?= og_tags($a['title'], $a['excerpt'] ?? '') ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<?= organization_schema() ?>
<script type="application/ld+json"><?= json_encode($article_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
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
      <div><h1>Phoenix Arabia™</h1><p>Technical Article</p></div>
    </a>
    <div class="links">
      <a href="articles.php">All Articles</a>
      <a href="index.php#marketplace">Marketplace</a>
      <a class="btn green" href="index.php#rfq">Submit RFQ</a>
    </div>
  </div>
</nav>

<section class="section">
  <div class="container">
    <div class="article">
      <span class="pill"><?= e($a['category']) ?></span>
      <h1><?= e($a['title']) ?></h1>
      <p style="font-size:17px;color:var(--pa-slate);font-style:italic;margin:0 0 24px"><?= e($a['excerpt']) ?></p>
      <hr>
      <div style="margin-top:24px">
        <?= nl2br(e($a['content'])) ?>
      </div>
      <hr style="margin-top:32px">
      <div style="text-align:center;padding-top:24px">
        <p style="font-size:14px;color:var(--pa-slate);margin-bottom:16px">Need pricing or technical support related to this topic?</p>
        <a class="btn green" href="index.php#rfq">Submit Related RFQ →</a>
        <a class="btn outline" href="articles.php" style="margin-left:8px">← All Articles</a>
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
        <span>Aramco Vendor <?= e(ARAMCO_VENDOR) ?></span>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
