<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/seo.php';

$products = db()->query("SELECT * FROM products WHERE is_active=1 ORDER BY is_featured DESC, id DESC LIMIT 6")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= seo_title('Phoenix Arabia | Industrial Supply Marketplace') ?></title>
<meta name="description" content="<?= seo_description() ?>">
<meta name="robots" content="index, follow">

<link rel="canonical" href="<?= canonical('index.php') ?>">
<link rel="stylesheet" href="assets/style.css">

</head>

<body>

<header class="top-hero">
    <div class="container">
        <h1>www.phoenix.com.sa</h1>
        <p>Industrial Supply Marketplace</p>
    </div>
</header>

<main class="site-map">

    <section class="map-grid">

        <div class="map-column public">
            <h2>Public Marketplace</h2>
            <p>Open to all visitors</p>

            <a href="index.php">/index.php</a>
            <a href="product.php">/product.php</a>
            <a href="brand.php">/brand.php</a>
            <a href="industry.php">/industry.php</a>
            <a href="articles.php">/articles.php</a>
            <a href="article.php">/article.php</a>
            <a href="submit_rfq.php">submit_rfq.php</a>
            <a href="add_to_cart.php">add_to_cart.php</a>
            <a class="shared" href="sitemap.php">sitemap.php · robots.txt</a>
            <a class="shared" href="404.php">404.php · 403.php</a>
        </div>

        <div class="map-column customer">
            <h2>Customer Portal</h2>
            <p>Login required</p>

            <a href="login.php">/customer/login.php</a>
            <a href="register.php">register.php</a>
            <a href="forgot_password.php">forgot_password.php</a>
            <a href="reset_password.php">reset_password.php</a>
            <a href="dashboard.php">dashboard.php</a>
            <a href="cart.php">cart.php</a>
            <a href="orders.php">orders.php</a>
            <a href="order_view.php">order_view.php</a>
            <a href="profile.php">profile.php</a>
            <a class="shared" href="logout.php">logout.php</a>
        </div>

        <div class="map-column staff">
            <h2>Staff Console</h2>
            <p>Admin only</p>

            <a href="login.php">/staff/login.php</a>
            <a href="dashboard.php">dashboard.php</a>
            <a href="rfq_list.php">rfq_list · rfq_view</a>
            <a href="customers_list.php">customers_list.php</a>
            <a href="products_list.php">products_list · edit</a>
            <a href="brands_list.php">brands_list · edit</a>
            <a href="industries_list.php">industries · articles</a>
            <a href="staff_list.php">staff_list · edit</a>
            <a class="shared" href="_change_password.php">_change_password.php</a>
            <a class="shared" href="logout.php">logout.php</a>
        </div>

    </section>

    <section class="infra-box">
        <h2>Shared Infrastructure</h2>
        <p>/includes config · db · helpers · seo /assets /uploads</p>
    </section>

    <section class="database-box">
        <h2>MySQL Database</h2>
        <p>products · brands · industries · articles · categories</p>
        <p>customer_accounts · rfq_requests · rfq_cart_items · staff_users</p>
    </section>

    <section class="bottom-layer">
        <div class="security-box">
            <h2>Security Layer</h2>
            <p>CSRF · Rate Limit · bcrypt · MIME Check</p>
        </div>

        <div class="seo-box">
            <h2>SEO Layer</h2>
            <p>Schema.org · OG Tags · Sitemap · Canonical</p>
        </div>
    </section>

    <section class="products-preview">
        <h2>Featured Marketplace Items</h2>

        <div class="products-grid">
            <?php foreach($products as $product): ?>
                <div class="product-card">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p><?= htmlspecialchars($product['short_description']) ?></p>
                    <span><?= htmlspecialchars($product['price_mode']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

</body>
</html>
