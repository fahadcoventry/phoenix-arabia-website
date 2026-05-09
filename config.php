<?php
// ═══════════════════════════════════════════════════════════
// Phoenix Arabia — Configuration
// IMPORTANT: Edit DB credentials below before deploying
// ═══════════════════════════════════════════════════════════

// ── Database (Hostinger) ──
define('DB_HOST', 'localhost');
define('DB_NAME', 'u665392070_phoenix');
define('DB_USER', 'u665392070_phoenix');
define('DB_PASS', 'CHANGE_THIS_TO_YOUR_DATABASE_PASSWORD');
define('DB_CHARSET', 'utf8mb4');

// ── Site Configuration ──
define('SITE_URL', 'https://www.phoenix.com.sa');
define('SITE_NAME', 'Phoenix Arabia');
define('SITE_LEGAL_NAME', 'Phoenix Arabia Contracting Co. Ltd.');
define('SITE_TAGLINE', 'Industrial Supply & RFQ Marketplace');

// ── Official Identifiers ──
define('CR_NUMBER', '1010871269');
define('UNIFIED_NUMBER', '7035078612');
define('ARAMCO_VENDOR', '10112458');

// ── Contact ──
define('OFFICE_ADDRESS', '8th Floor, Office 8B, Shahad Tower, King Saud Bin Abdulaziz St, Qurtubah, Al Khobar');
define('OFFICE_PHONE', '+966 53 303 3352');
define('OFFICE_PHONE_RAW', '+966533033352');
define('OFFICE_WHATSAPP', '+966 59 771 1094');
define('OFFICE_WHATSAPP_RAW', '966597711094');
define('OFFICE_EMAIL_GENERAL', 'info@phoenix.com.sa');
define('OFFICE_EMAIL_RFQ', 'rfq@phoenix.com.sa');
define('OFFICE_EMAIL_MD', 'fahad@phoenix.com.sa');

// ── Security ──
// Generate a unique random key. Run: php -r "echo bin2hex(random_bytes(32));"
define('CSRF_SECRET', 'CHANGE_THIS_TO_A_64_CHARACTER_RANDOM_HEX_STRING_USING_THE_COMMAND_ABOVE');
define('SESSION_LIFETIME', 7200); // 2 hours

// ── File Uploads ──
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10 MB
define('UPLOAD_ALLOWED_EXT', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'dwg']);
define('UPLOAD_ALLOWED_MIME', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'image/x-dwg',
    'application/x-dwg',
]);

// ── Environment ──
define('IS_PRODUCTION', strpos($_SERVER['HTTP_HOST'] ?? '', 'phoenix.com.sa') !== false);

// Error reporting based on environment
if (IS_PRODUCTION) {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// ── Session Security ──
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
if (IS_PRODUCTION) {
    ini_set('session.cookie_secure', '1');
}
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Timezone ──
date_default_timezone_set('Asia/Riyadh');

// ── Default language ──
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}
