<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

staff_require('view', 'industries');
$rows = db()->query("SELECT * FROM industry_pages ORDER BY industry_name ASC")->fetchAll();
layout_start('Industries', 'industries');
?>

<?= flash_html() ?>

<div class="page-head">
  <div><h1>Industry Pages</h1><p><?= count($rows) ?> page(s)</p></div>
  <?php if (staff_can('create', 'industries')): ?>
    <div class="page-head-actions"><a class="btn green" href="industry_edit.php">+ New Industry Page</a></div>
  <?php endif; ?>
</div>

<?php if (empty($rows)): ?>
  <div class="empty-state"><div class="empty-state-icon">🏭</div><h3>No industry pages</h3><p>Create dedicated SEO pages for industrial sectors.</p></div>
<?php else: ?>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Industry</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $i): ?>
          <tr>
            <td><b><?= e($i['industry_name']) ?></b></td>
            <td style="font-family: monospace; font-size: 11px; color: var(--pa-muted);">/<?= e($i['slug']) ?></td>
            <td><span class="badge badge-<?= $i['is_visible'] ? 'active' : 'disabled' ?>"><?= $i['is_visible'] ? 'Visible' : 'Hidden' ?></span></td>
            <td class="actions">
              <?php if (staff_can('update', 'industries')): ?>
                <a class="btn light" href="industry_edit.php?id=<?= e($i['id']) ?>">Edit</a>
              <?php endif; ?>
              <a class="btn light" href="../industry.php?slug=<?= e($i['slug']) ?>" target="_blank">View ↗</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php layout_end(); ?>
