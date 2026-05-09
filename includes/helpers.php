<?php
// ═══════════════════════════════════════════════════════════
// Phoenix Arabia — Helper Functions (Security + Utilities)
// ═══════════════════════════════════════════════════════════

require_once __DIR__ . '/config.php';

// ── Output escaping ──
function e($value): string {
    if ($value === null) return '';
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── CSRF Protection ──
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool {
    $token = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';
    if (empty($token) || empty($session_token)) return false;
    return hash_equals($session_token, $token);
}

function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        http_response_code(403);
        die('Security token mismatch. Please refresh and try again.');
    }
}

// ── Mobile normalization ──
function normalize_mobile(string $mobile): string {
    $mobile = preg_replace('/[^\d+]/', '', $mobile);
    // Normalize Saudi numbers
    if (strpos($mobile, '+966') === 0) return $mobile;
    if (strpos($mobile, '966') === 0) return '+' . $mobile;
    if (strpos($mobile, '05') === 0) return '+966' . substr($mobile, 1);
    if (strpos($mobile, '5') === 0 && strlen($mobile) === 9) return '+966' . $mobile;
    return $mobile;
}

// ── Validators ──
function valid_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function valid_mobile(string $mobile): bool {
    $clean = preg_replace('/[^\d]/', '', $mobile);
    return strlen($clean) >= 9 && strlen($clean) <= 15;
}

// ── Slugify ──
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ── File upload (secure) ──
function upload_file(string $field, string $folder = 'general'): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    $file = $_FILES[$field];
    
    // Check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Size limit
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        throw new RuntimeException('File too large (max 10MB)');
    }
    
    // Extension check
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_ALLOWED_EXT)) {
        throw new RuntimeException('File type not allowed');
    }
    
    // MIME check (real type, not user-supplied)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $real_mime = $finfo->file($file['tmp_name']);
    if (!in_array($real_mime, UPLOAD_ALLOWED_MIME)) {
        throw new RuntimeException('File MIME type not allowed');
    }
    
    // Destination
    $upload_dir = __DIR__ . '/../uploads/' . preg_replace('/[^a-z0-9_-]/i', '', $folder);
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate safe filename: timestamp_random.ext
    $safe_name = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $upload_dir . '/' . $safe_name;
    
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Failed to save uploaded file');
    }
    
    // Return relative path for storage in DB
    return 'uploads/' . $folder . '/' . $safe_name;
}

// ── Customer auth helpers ──
function current_customer_id(): ?int {
    return $_SESSION['customer_id'] ?? null;
}

function require_customer(): void {
    if (!current_customer_id()) {
        header('Location: /customer/login.php');
        exit;
    }
}

// ── Staff auth helpers ──
function current_staff_id(): ?int {
    return $_SESSION['staff_id'] ?? null;
}

function require_staff(): void {
    if (!current_staff_id()) {
        header('Location: /staff/login.php');
        exit;
    }
}

// ── Rate limiting (simple, file-based) ──
function rate_limit_check(string $key, int $max_attempts = 5, int $window_seconds = 300): bool {
    $cache_file = sys_get_temp_dir() . '/pa_rate_' . md5($key);
    $now = time();
    $attempts = [];
    
    if (file_exists($cache_file)) {
        $attempts = json_decode(file_get_contents($cache_file), true) ?: [];
        // Filter out old attempts
        $attempts = array_filter($attempts, fn($t) => ($now - $t) < $window_seconds);
    }
    
    if (count($attempts) >= $max_attempts) {
        return false;
    }
    
    $attempts[] = $now;
    file_put_contents($cache_file, json_encode($attempts));
    return true;
}

// ── Format SAR ──
function fmt_sar($amount): string {
    return number_format((float)$amount, 2) . ' SAR';
}

// ── Language helper ──
function lang(): string {
    return $_SESSION['lang'] ?? 'en';
}

function is_ar(): bool {
    return lang() === 'ar';
}

// ── Translation helper ──
function t(string $en, string $ar): string {
    return is_ar() ? $ar : $en;
}

