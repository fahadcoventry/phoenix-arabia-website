<?php
// ═══════════════════════════════════════════════════════════
// Phoenix Arabia Staff — Auth & Permissions
// ═══════════════════════════════════════════════════════════
// Include this at the TOP of every staff page (except login.php)

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// ── Force login ──
if (empty($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit;
}

// ── Load staff record (cached in session for the request) ──
function current_staff(): ?array {
    static $staff = null;
    if ($staff === null && !empty($_SESSION['staff_id'])) {
        $stmt = db()->prepare("SELECT id, name, email, role, is_active FROM staff_users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['staff_id']]);
        $staff = $stmt->fetch() ?: null;
        // Ensure account is still active
        if ($staff && !$staff['is_active']) {
            session_destroy();
            header('Location: login.php?disabled=1');
            exit;
        }
    }
    return $staff;
}

// ── Role-based permissions ──
// Returns true if current staff can perform $action on $resource
function staff_can(string $action, string $resource): bool {
    $s = current_staff();
    if (!$s) return false;
    $role = $s['role'];
    
    // Super Admin: full access
    if ($role === 'Super Admin') return true;
    
    // Permission matrix (role => resource => actions)
    $permissions = [
        'Sales Agent' => [
            'rfq'       => ['view', 'update'],
            'customers' => ['view'],
            'products'  => ['view'],
        ],
        'Account Manager' => [
            'rfq'       => ['view', 'update', 'assign'],
            'customers' => ['view', 'create', 'update'],
            'products'  => ['view'],
            'orders'    => ['view', 'update'],
        ],
        'Procurement Officer' => [
            'rfq'       => ['view', 'update'],
            'products'  => ['view', 'create', 'update'],
            'suppliers' => ['view', 'create', 'update'],
        ],
        'Data Entry' => [
            'products'  => ['view', 'create', 'update'],
            'brands'    => ['view', 'create', 'update'],
            'industries'=> ['view', 'create', 'update'],
            'articles'  => ['view', 'create', 'update'],
        ],
    ];
    
    $allowed = $permissions[$role][$resource] ?? [];
    return in_array($action, $allowed, true);
}

// ── Hard-deny if no permission (use at top of action pages) ──
function staff_require(string $action, string $resource): void {
    if (!staff_can($action, $resource)) {
        http_response_code(403);
        require __DIR__ . '/../403.php';
        exit;
    }
}

// ── Role label helpers ──
function role_color(string $role): string {
    return match($role) {
        'Super Admin'         => '#A32D2D',
        'Account Manager'     => '#1E3A8A',
        'Sales Agent'         => '#0B7A3B',
        'Procurement Officer' => '#B45309',
        'Data Entry'          => '#4B5563',
        default               => '#6B7280',
    };
}
