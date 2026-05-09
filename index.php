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

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/style.css">
<?= organization_schema() ?>
</head>
<body>

<!-- ─── TOPBAR ─── -->
<div class="topbar">
  <div class="container">
    <div>📍 <?= e(OFFICE_ADDRESS) ?></div>
    <div>
      📞 <a href="tel:<?= e(OFFICE_PHONE_RAW) ?>"><?= e(OFFICE_PHONE) ?></a> &nbsp;|&nbsp;
      ✉ <a href="mailto:<?= e(OFFICE_EMAIL_RFQ) ?>"><?= e(OFFICE_EMAIL_RFQ) ?></a>
    </div>
  </div>
</div>

<!-- ─── NAV ─── -->
<nav class="nav">
  <div class="container">
    <a class="brand" href="#home">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
      <div>
        <h1>Phoenix Arabia™</h1>
        <p>Industrial Supply &amp; RFQ Marketplace</p>
      </div>
    </a>
    <div class="links">
      <a href="#marketplace">Marketplace</a>
      <a href="#services">Services</a>
      <a href="#brands">Brands</a>
      <a href="#industries">Industries</a>
      <a href="articles.php">Articles</a>
      <a href="#rfq">RFQ</a>
      <a class="btn light" href="customer/login.php">Customer Login</a>
      <a class="btn green" href="#rfq">Submit RFQ</a>
    </div>
  </div>
</nav>

<!-- ─── HERO ─── -->
<header class="hero" id="home">
  <div class="container">
    <div class="hero-wrap">
      <div>
        <span class="kicker">Saudi Aramco Approved Vendor · Industrial Supply Gateway</span>
        <h2>
          Phoenix Arabia™<br>
          <span>Industrial Supply &amp; RFQ Marketplace</span>
        </h2>
        <p>
          One commercial gateway for industrial products, factory supplies, MTO pricing, project procurement, chemical solutions, and mechanical packages. All inquiries, prices, and quotations are managed through Phoenix Arabia.
        </p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <a class="btn light" href="#marketplace">Browse Products</a>
          <a class="btn green" href="#rfq">Upload MTO / BOQ</a>
          <a class="btn dark" href="#suppliers">List Your Products</a>
        </div>
      </div>
      <div class="hero-card">
        <h3>Why Phoenix Arabia</h3>
        <ul>
          <li>All customer inquiries through one RFQ Desk</li>
          <li>Multi-brand RFQ Cart in a single request</li>
          <li>Saudi Aramco Approved Vendor (10112458)</li>
          <li>Show Price · Request Price · MTO · RFQ Only</li>
          <li>Brand and supplier integrity protected</li>
          <li>24-hour response target on complete RFQs</li>
        </ul>
      </div>
    </div>
    <div class="stats">
      <div class="stat"><b>RFQ</b><span>Primary Sales Engine</span></div>
      <div class="stat"><b>MTO</b><span>Material Take Off Pricing</span></div>
      <div class="stat"><b>B2B</b><span>Industrial Customers</span></div>
      <div class="stat"><b>SEO</b><span>Indexed Marketplace</span></div>
    </div>
  </div>
</header>

<!-- ─── MARKETPLACE ─── -->
<section class="section" id="marketplace">
  <div class="container">
    <div class="head">
      <div>
        <h2>Industrial Marketplace &amp; RFQ Catalogue</h2>
        <p>Products are listed by category, brand, and application. Pricing modes: <b>Show Price</b>, <b>Request Price</b>, <b>RFQ Only</b>, or <b>MTO</b>.</p>
      </div>
      <a class="btn green" href="#rfq">Request Multi-Item Quote</a>
    </div>

    <div class="marketbar">
      <input class="search" id="search" placeholder="Search product, part number, brand, category, or application" oninput="filterProducts()">
      <select class="select" id="category" onchange="filterProducts()">
        <option value="all">All Categories</option>
        <?php foreach($categories as $c): ?>
          <option value="<?= e($c['name']) ?>"><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select class="select" id="priceMode" onchange="filterProducts()">
        <option value="all">All Price Modes</option>
        <option>Show Price</option>
        <option>Request Price</option>
        <option>RFQ Only</option>
        <option>MTO</option>
      </select>
    </div>

    <div class="products">
      <?php foreach($products as $p): ?>
        <div class="product"
             data-category="<?= e($p['category']) ?>"
             data-price-mode="<?= e($p['price_mode']) ?>"
             data-search="<?= e(strtolower($p['name'].' '.$p['brand'].' '.$p['category'].' '.$p['sku'].' '.$p['short_description'])) ?>">
          <div class="pimg">
            <img src="<?= e($p['image_path'] ?: 'assets/placeholder-product.svg') ?>" alt="<?= e($p['name']) ?>" loading="lazy">
          </div>
          <div class="pbody">
            <div class="tag"><?= e($p['category']) ?></div>
            <h3><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
            <p><?= e($p['short_description']) ?></p>
            <div class="meta">
              <span><?= e($p['stock_status']) ?></span>
              <?php if(!empty($p['brand']) && $p['manufacturer_visible']): ?>
                <span><?= e($p['brand']) ?></span>
              <?php endif; ?>
            </div>
            <div class="pfoot">
              <span class="<?= $p['price_mode']==='Show Price' ? 'price' : 'request' ?>">
                <?= $p['price_mode']==='Show Price' ? e($p['currency'].' '.number_format((float)$p['fixed_price'],2)) : e($p['price_mode']) ?>
              </span>
              <a class="btn blue" href="add_to_cart.php?product_id=<?= e($p['id']) ?>">Add to RFQ</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="rfq-cart">
      <div>
        <h3>RFQ Cart Concept</h3>
        <p>Add multiple products from different brands and factories into one RFQ. Phoenix Arabia consolidates the request and issues one commercial response.</p>
      </div>
      <a class="btn light" href="customer/cart.php">Submit Combined RFQ →</a>
    </div>
  </div>
</section>

<!-- ─── BRANDS ─── -->
<section class="section protection" id="brands">
  <div class="container">
    <div class="head">
      <div>
        <h2>Brand Pages</h2>
        <p>Dedicated SEO pages for brands and supply channels represented through Phoenix Arabia.</p>
      </div>
    </div>
    <div class="grid3">
      <?php foreach($brands as $b): ?>
        <div class="card">
          <h3><a href="brand.php?slug=<?= e($b['slug']) ?>"><?= e($b['brand_name']) ?></a></h3>
          <p><?= e($b['public_description']) ?></p>
          <br>
          <a class="btn outline" href="brand.php?slug=<?= e($b['slug']) ?>">View Brand →</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ─── INDUSTRIES ─── -->
<section class="section" id="industries">
  <div class="container">
    <div class="head">
      <div>
        <h2>Industry Pages</h2>
        <p>SEO-focused pages for Saudi industrial sectors, EPC contractors, and project procurement.</p>
      </div>
    </div>
    <div class="grid3">
      <?php foreach($industries as $i): ?>
        <div class="card">
          <h3><a href="industry.php?slug=<?= e($i['slug']) ?>"><?= e($i['industry_name']) ?></a></h3>
          <p><?= e($i['intro']) ?></p>
          <br>
          <a class="btn outline" href="industry.php?slug=<?= e($i['slug']) ?>">View Industry →</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ─── SERVICES ─── -->
<section class="section dark-section" id="services">
  <div class="container">
    <div class="head">
      <div>
        <h2>Service Categories</h2>
        <p>Services are displayed under Phoenix Arabia. Execution partners remain internal unless disclosure is commercially required.</p>
      </div>
    </div>
    <div class="grid4">
      <div class="card">
        <span class="pill">Chemical Supply</span>
        <div class="icon">🧪</div>
        <h3>Industrial Chemical Supply</h3>
        <p>Sourcing, QA/QC, storage, distribution, and project delivery support.</p>
      </div>
      <div class="card">
        <span class="pill">Oilfield</span>
        <div class="icon">🛢️</div>
        <h3>Oilfield Chemicals</h3>
        <p>Corrosion inhibitors, H₂S scavengers, biocides, and flow assurance.</p>
      </div>
      <div class="card">
        <span class="pill">Pre-Commissioning</span>
        <div class="icon">🔧</div>
        <h3>Pre-Commissioning Support</h3>
        <p>Hydrotesting, flushing, purging, pressure testing, and leak detection.</p>
      </div>
      <div class="card">
        <span class="pill">Logistics</span>
        <div class="icon">🚚</div>
        <h3>Project Logistics</h3>
        <p>Imports, storage, distribution, delivery planning, and supply coordination.</p>
      </div>
    </div>
  </div>
</section>

<!-- ─── SUPPLIERS CTA ─── -->
<section class="section" id="suppliers">
  <div class="container">
    <div class="supplier-cta">
      <div>
        <h2>For Manufacturers</h2>
        <p>Manufacturers can list their products through Phoenix Arabia as a commercial supply channel. Phoenix Arabia manages customer RFQs, sales communication, quotation coordination, and market access.</p>
        <div class="notice">
          ⚠ Supplier visibility is controlled. Manufacturer direct contacts are <b>not displayed</b> to customers. All sales communication goes through Phoenix Arabia RFQ Desk.
        </div>
      </div>
      <form class="rfq-form" action="submit_rfq.php" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="request_type" value="Manufacturer Listing">
        <input class="input" name="company" placeholder="Factory / Manufacturer Name" required>
        <input class="input" name="product_name" placeholder="Product Category">
        <input class="input" name="details" placeholder="Country and Products">
        <input class="input" name="contact_name" placeholder="Contact Person" required>
        <input class="input" name="mobile" placeholder="Mobile" required>
        <input class="input" name="email" type="email" placeholder="Email">
        <button class="btn green" style="width:100%">Apply to Sell Through Phoenix Arabia</button>
      </form>
    </div>
  </div>
</section>

<!-- ─── RFQ ─── -->
<section class="section" id="rfq" style="background: var(--pa-bg);">
  <div class="container">
    <div class="rfq-band">
      <div>
        <span class="pill">RFQ Desk</span>
        <h2>Submit RFQ / Upload MTO / BOQ</h2>
        <p>Share your requirement and Phoenix Arabia will handle product matching, supplier coordination, pricing, and quotation through one protected commercial channel.</p>
        <p style="margin-top:14px"><b>Response target:</b> within 24 hours for complete requests.</p>
        <p style="margin-top:8px;font-size:12px;color:var(--pa-muted)">📞 <?= e(OFFICE_PHONE) ?> &nbsp;·&nbsp; ✉ <?= e(OFFICE_EMAIL_RFQ) ?></p>
      </div>
      <form class="rfq-form" action="submit_rfq.php" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-grid">
          <input class="input" name="company" placeholder="Company Name *" required>
          <input class="input" name="contact_name" placeholder="Contact Person *" required>
        </div>
        <div class="form-grid">
          <input class="input" name="mobile" placeholder="Mobile / WhatsApp *" required>
          <input class="input" name="email" type="email" placeholder="Email">
        </div>
        <select class="input" name="request_type">
          <option>Product Purchase</option>
          <option>Request Price Now</option>
          <option>MTO Pricing</option>
          <option>BOQ Upload</option>
          <option>Project Procurement</option>
          <option>Manufacturer Listing</option>
        </select>
        <input class="input" name="product_name" placeholder="Product / Package Name (if applicable)">
        <input class="file" type="file" name="boq_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.dwg">
        <textarea class="textarea" name="details" placeholder="Describe products, quantities, brand preference, delivery location, and project details..."></textarea>
        <button class="btn green" style="width:100%">Send to Phoenix Arabia RFQ Desk →</button>
      </form>
    </div>
  </div>
</section>

<!-- ─── FOOTER ─── -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <h3>Phoenix Arabia™</h3>
        <p>Phoenix Arabia Contracting Co. Ltd. — Industrial Supply &amp; RFQ Marketplace and protected procurement gateway for manufacturers, EPC contractors, factories, and industrial buyers across the Kingdom.</p>
        <div class="employee-link">
          <a href="staff/login.php">Employee Login</a>
        </div>
      </div>
      <div>
        <h4>Customer Account</h4>
        <a href="customer/login.php">Customer Login</a>
        <a href="customer/register.php">Create Account</a>
        <a href="customer/forgot_password.php">Forgot Password</a>
        <a href="customer/dashboard.php">My RFQ Cart</a>
      </div>
      <div>
        <h4>SEO Pages</h4>
        <a href="sitemap.php">Sitemap</a>
        <a href="#brands">Brands</a>
        <a href="#industries">Industries</a>
        <a href="articles.php">Technical Articles</a>
      </div>
      <div>
        <h4>RFQ Desk</h4>
        <a href="tel:<?= e(OFFICE_PHONE_RAW) ?>"><?= e(OFFICE_PHONE) ?></a>
        <a href="https://wa.me/<?= e(OFFICE_WHATSAPP_RAW) ?>" target="_blank" rel="noopener">WhatsApp: <?= e(OFFICE_WHATSAPP) ?></a>
        <a href="mailto:<?= e(OFFICE_EMAIL_RFQ) ?>"><?= e(OFFICE_EMAIL_RFQ) ?></a>
        <a href="<?= e(SITE_URL) ?>"><?= e(SITE_URL) ?></a>
      </div>
    </div>
    <div class="footer-credentials">
      <span>© <?= date('Y') ?> <?= e(SITE_LEGAL_NAME) ?> — All rights reserved.</span>
      <div class="creds">
        <span>C.R. <?= e(CR_NUMBER) ?></span>
        <span>U.N. <?= e(UNIFIED_NUMBER) ?></span>
        <span>Aramco Vendor <?= e(ARAMCO_VENDOR) ?></span>
      </div>
    </div>
  </div>
</footer>

<script>
function filterProducts() {
  const q = (document.getElementById('search').value || '').toLowerCase();
  const c = document.getElementById('category').value;
  const m = document.getElementById('priceMode').value;
  document.querySelectorAll('.product').forEach(el => {
    const okC = c === 'all' || el.dataset.category === c;
    const okM = m === 'all' || el.dataset.priceMode === m;
    const okQ = el.dataset.search.includes(q);
    el.style.display = (okC && okM && okQ) ? '' : 'none';
  });
}
</script>
</body>
</html>
