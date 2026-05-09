<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

$id = (int)($_GET['id'] ?? 0);
$is_new = ($id === 0);
if ($is_new) staff_require('create', 'articles'); else staff_require('update', 'articles');

$a = ['title' => '', 'slug' => '', 'category' => '', 'excerpt' => '', 'content' => '', 'seo_title' => '', 'meta_description' => '', 'keywords' => '', 'related_brand' => '', 'related_industry' => '', 'is_published' => 0];

if (!$is_new) {
    $stmt = db()->prepare("SELECT * FROM technical_articles WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $loaded = $stmt->fetch();
    if (!$loaded) { flash_set('error', 'Not found'); header('Location: articles_list.php'); exit; }
    $a = $loaded;
}

$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: slugify($title);
    $data = [
        'title' => $title,
        'slug' => $slug,
        'category' => trim($_POST['category'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'seo_title' => trim($_POST['seo_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'keywords' => trim($_POST['keywords'] ?? ''),
        'related_brand' => trim($_POST['related_brand'] ?? ''),
        'related_industry' => trim($_POST['related_industry'] ?? ''),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
    ];

    if (empty($data['title'])) $form_errors[] = 'Title is required.';
    if (empty($data['content'])) $form_errors[] = 'Content is required.';

    if (empty($form_errors)) {
        if ($is_new) {
            db()->prepare("INSERT INTO technical_articles (title, slug, category, excerpt, content, seo_title, meta_description, keywords, related_brand, related_industry, is_published) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
                ->execute(array_values($data));
            flash_set('success', 'Article created.');
            header('Location: article_edit.php?id=' . db()->lastInsertId());
            exit;
        } else {
            db()->prepare("UPDATE technical_articles SET title=?, slug=?, category=?, excerpt=?, content=?, seo_title=?, meta_description=?, keywords=?, related_brand=?, related_industry=?, is_published=? WHERE id=?")
                ->execute([...array_values($data), $id]);
            flash_set('success', 'Article updated.');
            header('Location: article_edit.php?id=' . $id);
            exit;
        }
    }
    $a = array_merge($a, $data);
}

$brands = db()->query("SELECT brand_name FROM brands WHERE is_visible=1 ORDER BY brand_name")->fetchAll(PDO::FETCH_COLUMN);
$industries = db()->query("SELECT industry_name FROM industry_pages WHERE is_visible=1 ORDER BY industry_name")->fetchAll(PDO::FETCH_COLUMN);

layout_start($is_new ? 'New Article' : 'Edit Article', 'articles');
?>

<?= flash_html() ?>

<div class="page-head">
  <div><h1><?= $is_new ? 'New Article' : 'Edit Article' ?></h1></div>
  <a class="btn light" href="articles_list.php">← Back</a>
</div>

<?php if (!empty($form_errors)): ?>
  <div class="flash flash-error"><ul><?php foreach ($form_errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="form-card">
  <?= csrf_field() ?>
  <div class="form-row">
    <div class="form-field"><label>Title *</label><input type="text" name="title" required value="<?= e($a['title']) ?>"></div>
    <div class="form-field"><label>Slug</label><input type="text" name="slug" value="<?= e($a['slug']) ?>"></div>
  </div>
  <div class="form-row cols-3">
    <div class="form-field"><label>Category</label><input type="text" name="category" value="<?= e($a['category']) ?>" placeholder="e.g. Procurement, Automation"></div>
    <div class="form-field">
      <label>Related Brand</label>
      <input type="text" name="related_brand" list="brand-list" value="<?= e($a['related_brand']) ?>">
      <datalist id="brand-list"><?php foreach ($brands as $b): ?><option value="<?= e($b) ?>"><?php endforeach; ?></datalist>
    </div>
    <div class="form-field">
      <label>Related Industry</label>
      <input type="text" name="related_industry" list="industry-list" value="<?= e($a['related_industry']) ?>">
      <datalist id="industry-list"><?php foreach ($industries as $ix): ?><option value="<?= e($ix) ?>"><?php endforeach; ?></datalist>
    </div>
  </div>
  <div class="form-field"><label>Excerpt</label><textarea name="excerpt" rows="2" maxlength="320"><?= e($a['excerpt']) ?></textarea></div>
  <div class="form-field">
    <label>Content *</label>
    <textarea name="content" rows="14" required><?= e($a['content']) ?></textarea>
    <div class="hint">Plain text. Line breaks will be preserved on the published page.</div>
  </div>
  <div class="form-field"><label>SEO Title</label><input type="text" name="seo_title" value="<?= e($a['seo_title']) ?>" maxlength="255"></div>
  <div class="form-field"><label>Meta Description</label><textarea name="meta_description" rows="2" maxlength="320"><?= e($a['meta_description']) ?></textarea></div>
  <div class="form-field"><label>Keywords</label><input type="text" name="keywords" value="<?= e($a['keywords']) ?>"></div>
  <div class="form-field">
    <label style="display: flex; align-items: center; gap: 10px; text-transform: none; letter-spacing: 0; font-size: 13px;">
      <input type="checkbox" name="is_published" <?= $a['is_published'] ? 'checked' : '' ?> style="width:auto;">
      Published (visible on website)
    </label>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn blue"><?= $is_new ? 'Create' : 'Save' ?></button>
    <a class="btn light" href="articles_list.php">Cancel</a>
  </div>
</form>

<?php layout_end(); ?>
