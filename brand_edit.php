<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

$id = (int)($_GET['id'] ?? 0);
$is_new = ($id === 0);

if ($is_new) staff_require('create', 'brands');
else staff_require('update', 'brands');

$b = ['brand_name' => '', 'slug' => '', 'public_description' => '', 'seo_title' => '', 'meta_description' => '', 'is_visible' => 1];

if (!$is_new) {
    $stmt = db()->prepare("SELECT * FROM brands WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $loaded = $stmt->fetch();
    if (!$loaded) { flash_set('error', 'Brand not found.'); header('Location: brands_list.php'); exit; }
    $b = $loaded;
}

$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $brand_name = trim($_POST['brand_name'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: slugify($brand_name);
    $data = [
        'brand_name' => $brand_name,
        'slug' => $slug,
        'public_description' => trim($_POST['public_description'] ?? ''),
        'seo_title' => trim($_POST['seo_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
    ];

    if (empty($data['brand_name'])) $form_errors[] = 'Brand name is required.';
    if (empty($data['slug'])) $form_errors[] = 'Slug could not be generated.';

    if (empty($form_errors)) {
        if ($is_new) {
            db()->prepare("INSERT INTO brands (brand_name, slug, public_description, seo_title, meta_description, is_visible) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute(array_values($data));
            flash_set('success', 'Brand created.');
            header('Location: brand_edit.php?id=' . db()->lastInsertId());
            exit;
        } else {
            db()->prepare("UPDATE brands SET brand_name=?, slug=?, public_description=?, seo_title=?, meta_description=?, is_visible=? WHERE id=?")
                ->execute([...array_values($data), $id]);
            flash_set('success', 'Brand updated.');
            header('Location: brand_edit.php?id=' . $id);
            exit;
        }
    }
    $b = array_merge($b, $data);
}

layout_start($is_new ? 'New Brand' : 'Edit Brand', 'brands');
?>

<?= flash_html() ?>

<div class="page-head">
  <div><h1><?= $is_new ? 'New Brand' : 'Edit Brand' ?></h1></div>
  <a class="btn light" href="brands_list.php">← Back</a>
</div>

<?php if (!empty($form_errors)): ?>
  <div class="flash flash-error"><ul><?php foreach ($form_errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="form-card">
  <?= csrf_field() ?>
  <div class="form-row">
    <div class="form-field">
      <label>Brand Name *</label>
      <input type="text" name="brand_name" required value="<?= e($b['brand_name']) ?>">
    </div>
    <div class="form-field">
      <label>Slug</label>
      <input type="text" name="slug" value="<?= e($b['slug']) ?>" placeholder="auto-generated">
    </div>
  </div>
  <div class="form-field">
    <label>Public Description</label>
    <textarea name="public_description" rows="4"><?= e($b['public_description']) ?></textarea>
  </div>
  <div class="form-field">
    <label>SEO Title</label>
    <input type="text" name="seo_title" value="<?= e($b['seo_title']) ?>" maxlength="255">
  </div>
  <div class="form-field">
    <label>Meta Description</label>
    <textarea name="meta_description" rows="2" maxlength="320"><?= e($b['meta_description']) ?></textarea>
  </div>
  <div class="form-field">
    <label style="display: flex; align-items: center; gap: 10px; text-transform: none; letter-spacing: 0; font-size: 13px;">
      <input type="checkbox" name="is_visible" <?= $b['is_visible'] ? 'checked' : '' ?> style="width:auto;">
      Visible on website
    </label>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn blue"><?= $is_new ? 'Create' : 'Save' ?></button>
    <a class="btn light" href="brands_list.php">Cancel</a>
  </div>
</form>

<?php layout_end(); ?>
