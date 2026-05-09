<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$product_id = (int)($_GET['product_id'] ?? 0);

if ($product_id <= 0) {
    header('Location: index.php#marketplace');
    exit;
}

$stmt = db()->prepare("SELECT id, name, brand, category, price_mode FROM products WHERE id = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$product_id]);
$p = $stmt->fetch();

if (!$p) {
    header('Location: index.php#marketplace');
    exit;
}

$customer_id = current_customer_id();
$session_id = session_id();

// Prevent duplicate cart entries (same product, same session, status Active)
$check = db()->prepare("
    SELECT id FROM rfq_cart_items
    WHERE product_id = ? AND status = 'Active' AND (
        (customer_id IS NOT NULL AND customer_id = ?)
        OR (customer_id IS NULL AND session_id = ?)
    )
    LIMIT 1
");
$check->execute([$p['id'], $customer_id ?: 0, $session_id]);

if (!$check->fetch()) {
    db()->prepare("
        INSERT INTO rfq_cart_items
        (customer_id, session_id, product_id, product_name, brand, category, price_mode, quantity, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, '', '')
    ")->execute([
        $customer_id,
        $session_id,
        $p['id'],
        $p['name'],
        $p['brand'],
        $p['category'],
        $p['price_mode'],
    ]);
}

// Redirect logic
if ($customer_id) {
    header('Location: customer/cart.php');
} else {
    header('Location: customer/login.php?cart=1');
}
exit;
