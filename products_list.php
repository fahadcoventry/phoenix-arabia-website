<?php
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/_layout.php';

staff_require('view', 'products');

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    require_csrf();
    if (!staff_can('delete', 'products') && current_staff()['role'] !== 'Super Admin') {
        flash_set('error', 'You do not have permission to delete products.');
    } else {
        $del_id = (int)($_POST['id'] ?? 0);
        if ($del_id > 0) {
            // Soft delete (set is_active = 0) instead of hard delete
            db()->prepare("UPDATE products SET is_active = 0 WHERE id = ?")->execute([$del_id]);
            flash_set('success', 'Product deactivated successfully.');
        }
    }
    header('Location: products_list.php');
    exit;
}

$f_search = trim($_GET['q'] ?? '');
$f_category = trim($_GET['category'] ?? '');
$f_status = trim($_GET['status'] ?? 'active');

$where = [];
$params = [];

if ($f_status === 'active') $where[] = 'is_active = 1';
elseif ($f_status === 'inactive') $where[] = 'is_active = 0';

if (!empty($f_search)) {
    $where[] = '(name LIKE ? OR sku LIKE ? OR brand LIKE ?)';
    $like = '%' . $f_search . '%';
    array_push($params, $like, $like, $like);
}
if (!empty($f_category)) {
    $where[] = 'category = ?';
    $params[] = $f_category;
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = db()->prepare("SELECT * FROM products $where_sql ORDER BY is_featured DESC, id DESC LIMIT 200");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = db()->query("SELECT name FROM categories WHERE is_active=1 ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

layout_start('Products', 'products');
?>

<?= flash_html() ?>

<div class="page-head">
  <div>
    <h1>Products Catalog</h1>
    <p><?= count($products) ?> product(s) shown</p>
  </div>
  <?php if (staff_can('create', 'products')): ?>
    <div class="page-head-actions">
      <a class="btn green" href="product_edit.php">+ New Product</a>
    </div>
  <?php endif; ?>
</div>

<form class="filters-bar" method="get">
  <input type="search" name="q" placeholder="Search by name, SKU, brand..." value="<?= e($f_search) ?>">
  <select name="category">
    <option value="">All categories</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= e($cat) ?>" <?= $f_category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status">
    <option value="active" <?= $f_status === 'active' ? 'selected' : '' ?>>Active only</option>
    <option value="inactive" <?= $f_status === 'inactive' ? 'selected' : '' ?>>Inactive only</option>
    <option value="" <?= $f_status === '' ? 'selected' : '' ?>>All</option>
  </select>
  <button type="submit" class="btn blue">Filter</button>
</form>

<?php if (empty($products)): ?>
  <div class="empty-state">
    <div class="empty-state-icon">📦</div>
    <h3>No products found</h3>
    <p>Try adjusting filters, or add a new product.</p>
  </div>
<?php else: ?>
  <div class="data-table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Name</th>
          <th>Category</th>
          <th>Brand</th>
          <th>Price Mode</th>
          <th>Stock</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td style="font-family: monospace; font-size: 11px;"><?= e($p['sku'] ?: '—') ?></td>
            <td>
              <div style="font-weight: 600;"><?= e($p['name']) ?></div>
              <?php if ($p['is_featured']): ?>
                <span style="font-size: 10px; color: var(--pa-amber); font-weight: 700;">★ Featured</span>
              <?php endif; ?>
            </td>
            <td><?= e($p['category']) ?></td>
            <td><?= e($p['brand']) ?></td>
            <td><?= e($p['price_mode']) ?></td>
            <td style="font-size: 12px;"><?= e($p['stock_status']) ?></td>
            <td>
              <span class="badge badge-<?= $p['is_active'] ? 'active' : 'disabled' ?>">
                <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td class="actions">
              <?php if (staff_can('update', 'products')): ?>
                <a class="btn light" href="product_edit.php?id=<?= e($p['id']) ?>">Edit</a>
              <?php endif; ?>
              <a class="btn light" href="../product.php?slug=<?= e($p['slug']) ?>" target="_blank">View ↗</a>
              <?php if (current_staff()['role'] === 'Super Admin' && $p['is_active']): ?>
                <form method="post" style="display: inline;" onsubmit="return confirm('Deactivate this product?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                  <button type="submit" class="btn light" style="color: #A32D2D;">Deactivate</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php layout_end(); ?>
