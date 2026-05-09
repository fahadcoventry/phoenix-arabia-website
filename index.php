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

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    color: #1f2933;
}

.container {
    width: 90%;
    max-width: 1180px;
    margin: 0 auto;
}

.hero {
    background: #0b1f3a;
    color: white;
    padding: 60px 0;
    border-bottom: 6px solid #c9a227;
}

.hero h1 {
    margin: 0;
    font-size: 42px;
    font-weight: 700;
}

.hero p {
    margin-top: 12px;
    font-size: 20px;
    color: #d8dee9;
}

.products-section {
    padding: 50px 0;
}

.products-section h2 {
    font-size: 32px;
    margin-bottom: 30px;
    color: #0b1f3a;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.product-card {
    background: white;
    padding: 24px;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 8px 22px rgba(0,0,0,0.06);
}

.product-card h3 {
    margin-top: 0;
    color: #0b1f3a;
    font-size: 21px;
}

.product-card p {
    line-height: 1.6;
}
</style>

</head>

<body>

<header class="hero">
    <div class="container">
        <h1>Phoenix Arabia™</h1>
        <p>Industrial Supply & RFQ Marketplace</p>
    </div>
</header>

<section class="products-section">
    <div class="container">
        <h2>Featured Products</h2>

        <div class="products-grid">
            <?php foreach($products as $product): ?>
                <div class="product-card">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p><?= htmlspecialchars($product['short_description']) ?></p>
                    <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

</body>
</html>
