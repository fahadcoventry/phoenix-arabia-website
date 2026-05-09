<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php
function urlnode(string $loc, string $changefreq = 'weekly', string $priority = '0.8', ?string $lastmod = null): void {
    echo '<url>';
    echo '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>';
    if ($lastmod) echo '<lastmod>' . htmlspecialchars($lastmod, ENT_XML1) . '</lastmod>';
    echo '<changefreq>' . $changefreq . '</changefreq>';
    echo '<priority>' . $priority . '</priority>';
    echo '</url>';
}

// Static pages
urlnode(SITE_URL . '/', 'daily', '1.0');
urlnode(SITE_URL . '/index.php', 'daily', '1.0');
urlnode(SITE_URL . '/articles.php', 'weekly', '0.7');

// Products
$products_stmt = db()->query("SELECT slug, updated_at, created_at FROM products WHERE is_active = 1");
foreach ($products_stmt as $r) {
    $lastmod = $r['updated_at'] ?: $r['created_at'];
    urlnode(SITE_URL . '/product.php?slug=' . $r['slug'], 'weekly', '0.8', $lastmod ? date('c', strtotime($lastmod)) : null);
}

// Brands
$brands_stmt = db()->query("SELECT slug, created_at FROM brands WHERE is_visible = 1");
foreach ($brands_stmt as $r) {
    $lastmod = $r['created_at'];
    urlnode(SITE_URL . '/brand.php?slug=' . $r['slug'], 'weekly', '0.7', $lastmod ? date('c', strtotime($lastmod)) : null);
}

// Industries
$industries_stmt = db()->query("SELECT slug, created_at FROM industry_pages WHERE is_visible = 1");
foreach ($industries_stmt as $r) {
    $lastmod = $r['created_at'];
    urlnode(SITE_URL . '/industry.php?slug=' . $r['slug'], 'weekly', '0.7', $lastmod ? date('c', strtotime($lastmod)) : null);
}

// Articles
$articles_stmt = db()->query("SELECT slug, created_at FROM technical_articles WHERE is_published = 1");
foreach ($articles_stmt as $r) {
    $lastmod = $r['created_at'];
    urlnode(SITE_URL . '/article.php?slug=' . $r['slug'], 'monthly', '0.6', $lastmod ? date('c', strtotime($lastmod)) : null);
}
?>

</urlset>
