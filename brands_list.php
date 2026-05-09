<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

staff_require('view', 'brands');

$brands = db()->query("SELECT * FROM brands ORDER BY brand_name ASC")->fetchAll();
layout_start('Brands', 'brands');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1>Brands</h1>
    <p><?= count($brands) ?> brand(s)</p>
  </div>
  <?php if (staff_can('create', 'brands')): ?>
    <div class="page-head-actions">
      <a class="btn green" href="brand_edit.php">+ New Brand</a>
    </div>
  <?php endif; ?>
</div>

<?php if (empty($brands)): ?>
  <div class="empty-state">
    <div class="empty-state-icon">🏷️</div>
    <h3>No brands yet</h3>
    <p>Create brand pages for SEO-optimized supplier representation.</p>
  </div>
<?php else: ?>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Brand Name</th>
          <th>Slug</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($brands as $b): ?>
          <tr>
            <td><b><?= e($b['brand_name']) ?></b></td>
            <td style="font-family: monospace; font-size: 11px; color: var(--pa-muted);">/<?= e($b['slug']) ?></td>
            <td>
              <span class="badge badge-<?= $b['is_visible'] ? 'active' : 'disabled' ?>">
                <?= $b['is_visible'] ? 'Visible' : 'Hidden' ?>
              </span>
            </td>
            <td class="actions">
              <?php if (staff_can('update', 'brands')): ?>
                <a class="btn light" href="brand_edit.php?id=<?= e($b['id']) ?>">Edit</a>
              <?php endif; ?>
              <a class="btn light" href="../brand.php?slug=<?= e($b['slug']) ?>" target="_blank">View ↗</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php layout_end(); ?>
