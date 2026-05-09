<?php
// ═══════════════════════════════════════════════════════════
// Staff Layout — call layout_start($title) and layout_end()
// ═══════════════════════════════════════════════════════════

function layout_start(string $title, string $active = ''): void {
    $s = current_staff();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> | Phoenix Arabia Staff</title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="staff.css">
</head>
<body class="staff-body">

<!-- Top bar -->
<div class="staff-topbar">
  <div class="staff-topbar-inner">
    <a class="staff-brand" href="dashboard.php">
      <div class="mark"><span class="b"></span><span class="g"></span></div>
      <div>
        <h1>Phoenix Arabia™</h1>
        <p>Staff Console</p>
      </div>
    </a>
    <div class="staff-user">
      <div class="staff-user-info">
        <div class="staff-user-name"><?= e($s['name']) ?></div>
        <div class="staff-user-role" style="color: <?= role_color($s['role']) ?>"><?= e($s['role']) ?></div>
      </div>
      <a class="btn light" href="_change_password.php">🔑 Password</a>
      <a class="btn light" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="staff-shell">
  <!-- Sidebar -->
  <aside class="staff-sidebar">
    <div class="sidebar-section">
      <div class="sidebar-head">Operations</div>
      <a href="dashboard.php" class="sidebar-link <?= $active === 'dashboard' ? 'active' : '' ?>">
        <span class="ico">📊</span> Dashboard
      </a>
      <?php if (staff_can('view', 'rfq')): ?>
        <a href="rfq_list.php" class="sidebar-link <?= $active === 'rfq' ? 'active' : '' ?>">
          <span class="ico">📋</span> RFQ Requests
        </a>
      <?php endif; ?>
      <?php if (staff_can('view', 'customers')): ?>
        <a href="customers_list.php" class="sidebar-link <?= $active === 'customers' ? 'active' : '' ?>">
          <span class="ico">👥</span> Customers
        </a>
      <?php endif; ?>
    </div>

    <?php if (staff_can('view', 'products') || staff_can('view', 'brands') || staff_can('view', 'industries') || staff_can('view', 'articles')): ?>
    <div class="sidebar-section">
      <div class="sidebar-head">Catalog</div>
      <?php if (staff_can('view', 'products')): ?>
        <a href="products_list.php" class="sidebar-link <?= $active === 'products' ? 'active' : '' ?>">
          <span class="ico">📦</span> Products
        </a>
      <?php endif; ?>
      <?php if (staff_can('view', 'brands')): ?>
        <a href="brands_list.php" class="sidebar-link <?= $active === 'brands' ? 'active' : '' ?>">
          <span class="ico">🏷️</span> Brands
        </a>
      <?php endif; ?>
      <?php if (staff_can('view', 'industries')): ?>
        <a href="industries_list.php" class="sidebar-link <?= $active === 'industries' ? 'active' : '' ?>">
          <span class="ico">🏭</span> Industries
        </a>
      <?php endif; ?>
      <?php if (staff_can('view', 'articles')): ?>
        <a href="articles_list.php" class="sidebar-link <?= $active === 'articles' ? 'active' : '' ?>">
          <span class="ico">📝</span> Articles
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($s['role'] === 'Super Admin'): ?>
    <div class="sidebar-section">
      <div class="sidebar-head">Administration</div>
      <a href="staff_list.php" class="sidebar-link <?= $active === 'staff' ? 'active' : '' ?>">
        <span class="ico">👤</span> Staff Users
      </a>
    </div>
    <?php endif; ?>

    <div class="sidebar-section">
      <a href="../index.php" class="sidebar-link" target="_blank">
        <span class="ico">🌐</span> View Website ↗
      </a>
    </div>
  </aside>

  <!-- Main content -->
  <main class="staff-main">
<?php
}

function layout_end(): void {
    ?>
  </main>
</div>

<script>
// Auto-close flash messages after 5s
setTimeout(() => {
  document.querySelectorAll('.flash').forEach(f => f.style.display = 'none');
}, 5000);
</script>
</body>
</html>
<?php
}

// ── Helper to render flash messages ──
function flash_html(): string {
    $msg = $_SESSION['flash'] ?? null;
    if (!$msg) return '';
    unset($_SESSION['flash']);
    $type = $msg['type'] ?? 'info';
    $text = $msg['text'] ?? '';
    return '<div class="flash flash-' . e($type) . '">' . e($text) . '</div>';
}

function flash_set(string $type, string $text): void {
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}
