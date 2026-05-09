<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

$id = (int)($_GET['id'] ?? 0);
$is_new = ($id === 0);

if ($is_new) {
    staff_require('create', 'products');
} else {
    staff_require('update', 'products');
}

// Load existing product
$p = [
    'name' => '', 'slug' => '', 'brand' => '', 'category' => '', 'sku' => '',
    'price_mode' => 'RFQ Only', 'fixed_price' => '', 'currency' => 'SAR', 'unit' => '',
    'short_description' => '', 'description' => '',
    'image_path' => '', 'datasheet_path' => '',
    'manufacturer_visible' => 0, 'manufacturer_name' => '',
    'internal_supplier_name' => '', 'country_of_origin' => '',
    'minimum_order_qty' => '', 'lead_time' => '',
    'stock_status' => 'Upon Request',
    'seo_title' => '', 'meta_description' => '', 'keywords' => '',
    'is_featured' => 0, 'is_active' => 1,
];

if (!$is_new) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $loaded = $stmt->fetch();
    if (!$loaded) {
        flash_set('error', 'Product not found.');
        header('Location: products_list.php');
        exit;
    }
    $p = $loaded;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $data = [
        'name'                   => trim($_POST['name'] ?? ''),
        'slug'                   => trim($_POST['slug'] ?? '') ?: slugify($_POST['name'] ?? ''),
        'brand'                  => trim($_POST['brand'] ?? ''),
        'category'               => trim($_POST['category'] ?? ''),
        'sku'                    => trim($_POST['sku'] ?? ''),
        'price_mode'             => $_POST['price_mode'] ?? 'RFQ Only',
        'fixed_price'            => (($_POST['fixed_price'] ?? '') === '') ? null : (float)$_POST['fixed_price'],
        'currency'               => trim($_POST['currency'] ?? 'SAR'),
        'unit'                   => trim($_POST['unit'] ?? ''),
        'short_description'      => trim($_POST['short_description'] ?? ''),
        'description'            => trim($_POST['description'] ?? ''),
        'image_path'             => trim($_POST['image_path'] ?? ''),
        'manufacturer_visible'   => isset($_POST['manufacturer_visible']) ? 1 : 0,
        'manufacturer_name'      => trim($_POST['manufacturer_name'] ?? ''),
        'internal_supplier_name' => trim($_POST['internal_supplier_name'] ?? ''),
        'country_of_origin'      => trim($_POST['country_of_origin'] ?? ''),
        'minimum_order_qty'      => trim($_POST['minimum_order_qty'] ?? ''),
        'lead_time'              => trim($_POST['lead_time'] ?? ''),
        'stock_status'           => $_POST['stock_status'] ?? 'Upon Request',
        'seo_title'              => trim($_POST['seo_title'] ?? ''),
        'meta_description'       => trim($_POST['meta_description'] ?? ''),
        'keywords'               => trim($_POST['keywords'] ?? ''),
        'is_featured'            => isset($_POST['is_featured']) ? 1 : 0,
        'is_active'              => isset($_POST['is_active']) ? 1 : 0,
    ];

    // Validation
    $errors = [];
    if (empty($data['name'])) $errors[] = 'Name is required.';
    if (empty($data['category'])) $errors[] = 'Category is required.';
    if (empty($data['slug'])) $errors[] = 'Slug could not be generated.';

    // Handle image upload
    if (!empty($_FILES['product_image']['name'])) {
        try {
            $uploaded = upload_file('product_image', 'products');
            if ($uploaded) $data['image_path'] = $uploaded;
        } catch (RuntimeException $ex) {
            $errors[] = 'Image upload: ' . $ex->getMessage();
        }
    }

    // Handle datasheet upload
    if (!empty($_FILES['datasheet']['name'])) {
        try {
            $uploaded = upload_file('datasheet', 'datasheets');
            if ($uploaded) $data['datasheet_path'] = $uploaded;
        } catch (RuntimeException $ex) {
            $errors[] = 'Datasheet upload: ' . $ex->getMessage();
        }
    } else {
        $data['datasheet_path'] = $p['datasheet_path'] ?? '';
    }

    if (empty($errors)) {
        if ($is_new) {
            $cols = array_keys($data);
            $placeholders = array_fill(0, count($cols), '?');
            $sql = "INSERT INTO products (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
            db()->prepare($sql)->execute(array_values($data));
            $new_id = (int)db()->lastInsertId();
            flash_set('success', 'Product created successfully.');
            header('Location: product_edit.php?id=' . $new_id);
            exit;
        } else {
            $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
            $vals = array_values($data);
            $vals[] = $id;
            db()->prepare("UPDATE products SET $set WHERE id = ?")->execute($vals);
            flash_set('success', 'Product updated successfully.');
            header('Location: product_edit.php?id=' . $id);
            exit;
        }
    } else {
        // Re-fill form with submitted values + show errors
        $p = array_merge($p, $data);
        $form_errors = $errors;
    }
}

$categories = db()->query("SELECT name FROM categories WHERE is_active=1 ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$brands = db()->query("SELECT brand_name FROM brands WHERE is_visible=1 ORDER BY brand_name")->fetchAll(PDO::FETCH_COLUMN);

layout_start($is_new ? 'New Product' : 'Edit Product', 'products');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1><?= $is_new ? 'New Product' : 'Edit Product' ?></h1>
    <?php if (!$is_new): ?>
      <p>ID: <?= e($p['id']) ?> · Created <?= date('M j, Y', strtotime($p['created_at'])) ?></p>
    <?php endif; ?>
  </div>
  <a class="btn light" href="products_list.php">← Back to List</a>
</div>

<?php if (!empty($form_errors)): ?>
  <div class="flash flash-error">
    <ul style="margin: 0; padding-left: 20px;">
      <?php foreach ($form_errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form-card">
  <?= csrf_field() ?>

  <h3 style="font-size: 14px; color: var(--pa-blue); margin-bottom: 14px; padding-bottom: 8px; border-bottom: 2px solid var(--pa-blue-soft);">Basic Information</h3>

  <div class="form-row">
    <div class="form-field">
      <label>Name *</label>
      <input type="text" name="name" required value="<?= e($p['name']) ?>" maxlength="220">
    </div>
    <div class="form-field">
      <label>SKU</label>
      <input type="text" name="sku" value="<?= e($p['sku']) ?>" maxlength="120">
    </div>
  </div>

  <div class="form-row cols-3">
    <div class="form-field">
      <label>Category *</label>
      <input type="text" name="category" list="cat-list" required value="<?= e($p['category']) ?>">
      <datalist id="cat-list">
        <?php foreach ($categories as $c): ?>
          <option value="<?= e($c) ?>"><?php endforeach; ?>
      </datalist>
    </div>
    <div class="form-field">
      <label>Brand</label>
      <input type="text" name="brand" list="brand-list" value="<?= e($p['brand']) ?>">
      <datalist id="brand-list">
        <?php foreach ($brands as $b): ?>
          <option value="<?= e($b) ?>"><?php endforeach; ?>
      </datalist>
    </div>
    <div class="form-field">
      <label>Slug (URL)</label>
      <input type="text" name="slug" value="<?= e($p['slug']) ?>" placeholder="auto-generated">
      <div class="hint">Leave blank to auto-generate.</div>
    </div>
  </div>

  <div class="form-field">
    <label>Short Description</label>
    <input type="text" name="short_description" value="<?= e($p['short_description']) ?>" maxlength="255">
  </div>

  <div class="form-field">
    <label>Full Description</label>
    <textarea name="description" rows="5"><?= e($p['description']) ?></textarea>
  </div>

  <h3 style="font-size: 14px; color: var(--pa-blue); margin: 24px 0 14px; padding-bottom: 8px; border-bottom: 2px solid var(--pa-blue-soft);">Pricing & Stock</h3>

  <div class="form-row cols-3">
    <div class="form-field">
      <label>Price Mode *</label>
      <select name="price_mode">
        <?php foreach (['Show Price','Request Price','RFQ Only','MTO'] as $pm): ?>
          <option value="<?= $pm ?>" <?= $p['price_mode'] === $pm ? 'selected' : '' ?>><?= $pm ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-field">
      <label>Fixed Price (if "Show Price")</label>
      <input type="number" step="0.01" min="0" name="fixed_price" value="<?= e($p['fixed_price'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label>Currency</label>
      <input type="text" name="currency" value="<?= e($p['currency'] ?: 'SAR') ?>" maxlength="10">
    </div>
  </div>

  <div class="form-row cols-3">
    <div class="form-field">
      <label>Unit</label>
      <input type="text" name="unit" value="<?= e($p['unit']) ?>" placeholder="e.g. ton, lot, set">
    </div>
    <div class="form-field">
      <label>Min. Order Qty</label>
      <input type="text" name="minimum_order_qty" value="<?= e($p['minimum_order_qty']) ?>" placeholder="e.g. 5T">
    </div>
    <div class="form-field">
      <label>Lead Time</label>
      <input type="text" name="lead_time" value="<?= e($p['lead_time']) ?>" placeholder="e.g. 4-6 weeks">
    </div>
  </div>

  <div class="form-field">
    <label>Stock Status</label>
    <select name="stock_status">
      <?php foreach (['In Stock','Limited Stock','Upon Request','MTO'] as $st): ?>
        <option value="<?= $st ?>" <?= $p['stock_status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <h3 style="font-size: 14px; color: var(--pa-blue); margin: 24px 0 14px; padding-bottom: 8px; border-bottom: 2px solid var(--pa-blue-soft);">Supplier & Origin (Internal)</h3>

  <div class="form-row">
    <div class="form-field">
      <label>Manufacturer Name</label>
      <input type="text" name="manufacturer_name" value="<?= e($p['manufacturer_name']) ?>">
    </div>
    <div class="form-field">
      <label>Country of Origin</label>
      <input type="text" name="country_of_origin" value="<?= e($p['country_of_origin']) ?>">
    </div>
  </div>

  <div class="form-row">
    <div class="form-field">
      <label>Internal Supplier (Hidden)</label>
      <input type="text" name="internal_supplier_name" value="<?= e($p['internal_supplier_name']) ?>">
      <div class="hint">Internal-only — never shown publicly.</div>
    </div>
    <div class="form-field" style="display: flex; align-items: center; padding-top: 24px;">
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; text-transform: none; font-size: 13px; letter-spacing: 0;">
        <input type="checkbox" name="manufacturer_visible" <?= $p['manufacturer_visible'] ? 'checked' : '' ?> style="width: auto;">
        Show manufacturer name publicly
      </label>
    </div>
  </div>

  <h3 style="font-size: 14px; color: var(--pa-blue); margin: 24px 0 14px; padding-bottom: 8px; border-bottom: 2px solid var(--pa-blue-soft);">Media</h3>

  <div class="form-row">
    <div class="form-field">
      <label>Product Image</label>
      <?php if (!empty($p['image_path']) && $p['image_path'] !== 'assets/placeholder-product.svg'): ?>
        <div style="margin-bottom: 8px;">
          <img src="../<?= e($p['image_path']) ?>" alt="" style="max-height: 80px; border: 1px solid var(--pa-border); border-radius: 4px;">
        </div>
      <?php endif; ?>
      <input type="file" name="product_image" accept="image/jpeg,image/png">
      <div class="hint">JPG/PNG. Max 10 MB. Optional.</div>
    </div>
    <div class="form-field">
      <label>Datasheet (PDF)</label>
      <?php if (!empty($p['datasheet_path'])): ?>
        <div style="margin-bottom: 8px;">
          <a href="../<?= e($p['datasheet_path']) ?>" target="_blank">📄 Current datasheet</a>
        </div>
      <?php endif; ?>
      <input type="file" name="datasheet" accept=".pdf">
      <div class="hint">PDF. Max 10 MB. Optional.</div>
    </div>
  </div>

  <h3 style="font-size: 14px; color: var(--pa-blue); margin: 24px 0 14px; padding-bottom: 8px; border-bottom: 2px solid var(--pa-blue-soft);">SEO</h3>

  <div class="form-field">
    <label>SEO Title</label>
    <input type="text" name="seo_title" value="<?= e($p['seo_title']) ?>" maxlength="255" placeholder="Defaults to product name">
  </div>

  <div class="form-field">
    <label>Meta Description</label>
    <textarea name="meta_description" rows="2" maxlength="320"><?= e($p['meta_description']) ?></textarea>
    <div class="hint">Max 160-180 chars recommended.</div>
  </div>

  <div class="form-field">
    <label>Keywords</label>
    <input type="text" name="keywords" value="<?= e($p['keywords']) ?>" placeholder="comma, separated, keywords">
  </div>

  <h3 style="font-size: 14px; color: var(--pa-blue); margin: 24px 0 14px; padding-bottom: 8px; border-bottom: 2px solid var(--pa-blue-soft);">Visibility</h3>

  <div class="form-row">
    <div class="form-field" style="display: flex; align-items: center; padding-top: 8px;">
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; text-transform: none; font-size: 13px; letter-spacing: 0;">
        <input type="checkbox" name="is_active" <?= $p['is_active'] ? 'checked' : '' ?> style="width: auto;">
        Active (visible on website)
      </label>
    </div>
    <div class="form-field" style="display: flex; align-items: center; padding-top: 8px;">
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; text-transform: none; font-size: 13px; letter-spacing: 0;">
        <input type="checkbox" name="is_featured" <?= $p['is_featured'] ? 'checked' : '' ?> style="width: auto;">
        ★ Featured (highlight on homepage)
      </label>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn blue"><?= $is_new ? 'Create Product' : 'Save Changes' ?></button>
    <a class="btn light" href="products_list.php">Cancel</a>
    <?php if (!$is_new && !empty($p['slug'])): ?>
      <a class="btn light" href="../product.php?slug=<?= e($p['slug']) ?>" target="_blank" style="margin-left: auto;">View on Site ↗</a>
    <?php endif; ?>
  </div>
</form>

<?php layout_end(); ?>
