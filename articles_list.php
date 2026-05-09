<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

staff_require('view', 'articles');
$rows = db()->query("SELECT * FROM technical_articles ORDER BY id DESC")->fetchAll();
layout_start('Technical Articles', 'articles');
?>

<?= flash_html() ?>

<div class="page-head">
  <div><h1>Technical Articles</h1><p><?= count($rows) ?> article(s)</p></div>
  <?php if (staff_can('create', 'articles')): ?>
    <div class="page-head-actions"><a class="btn green" href="article_edit.php">+ New Article</a></div>
  <?php endif; ?>
</div>

<?php if (empty($rows)): ?>
  <div class="empty-state"><div class="empty-state-icon">📝</div><h3>No articles yet</h3><p>Publish technical articles to drive SEO and educate customers.</p></div>
<?php else: ?>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $a): ?>
          <tr>
            <td><b><?= e($a['title']) ?></b></td>
            <td><?= e($a['category']) ?></td>
            <td><span class="badge badge-<?= $a['is_published'] ? 'active' : 'disabled' ?>"><?= $a['is_published'] ? 'Published' : 'Draft' ?></span></td>
            <td style="font-size: 11px; color: var(--pa-muted);"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
            <td class="actions">
              <?php if (staff_can('update', 'articles')): ?>
                <a class="btn light" href="article_edit.php?id=<?= e($a['id']) ?>">Edit</a>
              <?php endif; ?>
              <?php if ($a['is_published']): ?>
                <a class="btn light" href="../article.php?slug=<?= e($a['slug']) ?>" target="_blank">View ↗</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php layout_end(); ?>
