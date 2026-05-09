<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/seo.php';

$products = db()->query("SELECT * FROM products WHERE is_active=1 ORDER BY is_featured DESC, id DESC")->fetchAll();
$categories = db()->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order ASC, name ASC")->fetchAll();
$brands = db()->query("SELECT * FROM brands WHERE is_visible=1 ORDER BY brand_name")->fetchAll();
$industries = db()->query("SELECT * FROM industry_pages WHERE is_visible=1 ORDER BY industry_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= seo_title('Phoenix Arabia | Industrial Supply & RFQ Marketplace | Saudi Aramco Approved Vendor') ?></title>

<meta name="description" content="<?= seo_description() ?>">

<meta name="keywords" content="Phoenix Arabia, Saudi Aramco vendor, industrial supply Saudi Arabia, RFQ marketplace, EPC procurement, MTO pricing, industrial chemicals KSA, Al Khobar supplier">

<meta name="author" content="Phoenix Arabia Contracting Co. Ltd.">

<meta name="robots" content="index, follow">

<link rel="canonical" href="<?= canonical('index.php') ?>">

<?= og_tags(
  'Phoenix Arabia | Industrial Supply & RFQ Marketplace',
  'Saudi Aramco Approved Vendor providing industrial supply, RFQ workflow, MTO pricing, and procurement gateway for EPC contractors and industrial buyers.'
) ?>

</head>
<body>

<header>
    <h1>Phoenix Arabia™</h1>
    <p>Industrial Supply & RFQ Marketplace</p>
</header>

<section>
    <h2>Featured Products</h2>

    <?php foreach($products as $product): ?>
        <div style="margin-bottom:20px;">
            <h3><?= htmlspecialchars($product['name']) ?></h3>

            <p><?= htmlspecialchars($product['short_description']) ?></p>

            <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></p>

            <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
        </div>
    <?php endforeach; ?>
</section>

</body>
</html>
