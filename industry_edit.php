<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

$id = (int)($_GET['id'] ?? 0);
$is_new = ($id === 0);
if ($is_new) staff_require('create', 'industries'); else staff_require('update', 'industries');

$i = ['industry_name' => '', 'slug' => '', 'headline' => '', 'intro' => '', 'seo_title' => '', 'meta_description' => '', 'keywords' => '', 'is_visible' => 1];

if (!$is_new) {
    $stmt = db()->prepare("SELECT * FROM industry_pages WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $loaded = $stmt->fetch();
    if (!$loaded) { flash_set('error', 'Not found'); header('Location: industries_list.php'); exit; }
    $i = $loaded;
}

$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $industry_name = trim($_POST['industry_name'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: slugify($industry_name);
    $data = [
        'industry_name' => $industry_name,
        'slug' => $slug,
        'headline' => trim($_POST['headline'] ?? ''),
        'intro' => trim($_POST['intro'] ?? ''),
        'seo_title' => trim($_POST['seo_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'keywords' => trim($_POST['keywords'] ?? ''),
        'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
    ];

    if (empty($data['industry_name'])) $form_errors[] = 'Industry name is required.';

    if (empty($form_errors)) {
        if ($is_new) {
            db()->prepare("INSERT INTO industry_pages (industry_name, slug, headline, intro, seo_title, meta_description, keywords, is_visible) VALUES (?,?,?,?,?,?,?,?)")
                ->execute(array_values($data));
            flash_set('success', 'Industry page created.');
            header('Location: industry_edit.php?id=' . db()->lastInsertId());
            exit;
        } else {
            db()->prepare("UPDATE industry_pages SET industry_name=?, slug=?, headline=?, intro=?, seo_title=?, meta_description=?, keywords=?, is_visible=? WHERE id=?")
                ->execute([...array_values($data), $id]);
            flash_set('success', 'Industry page updated.');
            header('Location: industry_edit.php?id=' . $id);
            exit;
        }
    }
    $i = array_merge($i, $data);
}

layout_start($is_new ? 'New Industry Page' : 'Edit Industry Page', 'industries');
?>

<?= flash_html() ?>

<div class="page-head">
  <div><h1><?= $is_new ? 'New Industry Page' : 'Edit Industry Page' ?></h1></div>
  <a class="btn light" href="industries_list.php">← Back</a>
</div>

<?php if (!empty($form_errors)): ?>
  <div class="flash flash-error"><ul><?php foreach ($form_errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="form-card">
  <?= csrf_field() ?>
  <div class="form-row">
    <div class="form-field"><label>Industry Name *</label><input type="text" name="industry_name" required value="<?= e($i['industry_name']) ?>"></div>
    <div class="form-field"><label>Slug</label><input type="text" name="slug" value="<?= e($i['slug']) ?>"></div>
  </div>
  <div class="form-field"><label>Headline</label><input type="text" name="headline" value="<?= e($i['headline']) ?>" maxlength="255"></div>
  <div class="form-field"><label>Intro Text</label><textarea name="intro" rows="4"><?= e($i['intro']) ?></textarea></div>
  <div class="form-field"><label>SEO Title</label><input type="text" name="seo_title" value="<?= e($i['seo_title']) ?>" maxlength="255"></div>
  <div class="form-field"><label>Meta Description</label><textarea name="meta_description" rows="2" maxlength="320"><?= e($i['meta_description']) ?></textarea></div>
  <div class="form-field"><label>Keywords</label><input type="text" name="keywords" value="<?= e($i['keywords']) ?>"></div>
  <div class="form-field">
    <label style="display: flex; align-items: center; gap: 10px; text-transform: none; letter-spacing: 0; font-size: 13px;">
      <input type="checkbox" name="is_visible" <?= $i['is_visible'] ? 'checked' : '' ?> style="width:auto;"> Visible
    </label>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn blue"><?= $is_new ? 'Create' : 'Save' ?></button>
    <a class="btn light" href="industries_list.php">Cancel</a>
  </div>
</form>

<?php layout_end(); ?>
