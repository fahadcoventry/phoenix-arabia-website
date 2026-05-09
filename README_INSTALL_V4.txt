═══════════════════════════════════════════════════════════════
PHOENIX ARABIA — V4 INSTALLATION GUIDE
Complete Industrial Supply Platform | Edition 2026
═══════════════════════════════════════════════════════════════

This package contains the complete V4 of the Phoenix Arabia
Industrial Supply & RFQ Marketplace, including:

  ✓ Public Marketplace Website
  ✓ Staff Admin Console (Multi-role)
  ✓ Customer Self-Service Portal (NEW in V4)


───────────────────────────────────────────────────────────────
WHAT'S NEW IN V4
───────────────────────────────────────────────────────────────

Customer Portal (NEW)
• Customer registration with validation
• Email/mobile login with rate limiting
• Forgot password / reset password flow with secure tokens
• Customer dashboard with personal stats
• RFQ Cart management (view, edit qty/notes, remove, clear)
• Combined RFQ submission from cart
• Order history and status tracking
• Profile editing + password change
• Guest cart migration: anonymous adds become customer cart on login

V3 Foundation (carried forward)
• Staff Admin Console with 5 roles and permission matrix
• Phoenix Blue #1E3A8A · Phoenix Green #0B7A3B branding
• All official identifiers visible (C.R., U.N., Aramco Vendor)
• Security hardening (CSRF, prepared statements, MIME validation)
• SEO optimization (Schema.org, OG tags, sitemaps)
• Setup script for safe initial admin creation


───────────────────────────────────────────────────────────────
PACKAGE CONTENTS
───────────────────────────────────────────────────────────────

PUBLIC SITE (root)
  index.php              Homepage with marketplace
  product.php            Product detail page
  brand.php              Brand showcase page
  industry.php           Industry sector page
  article.php            Single article page
  articles.php           Articles list page
  add_to_cart.php        Cart action handler (secured)
  submit_rfq.php         RFQ form handler (CSRF + validated)
  sitemap.php            Dynamic XML sitemap
  404.php, 403.php       Error pages
  setup_admin.php        ONE-TIME SETUP (delete after use)
  .htaccess              Apache security & performance
  robots.txt             Crawler directives

INCLUDES (server-side)
  includes/config.php    Site config + DB credentials
  includes/db.php        PDO database connection
  includes/helpers.php   Security + utility functions
  includes/seo.php       SEO + Schema.org helpers

ASSETS
  assets/style.css       Brand-aligned stylesheet
  assets/placeholder-product.svg

STAFF CONSOLE (/staff/)
  login.php              Staff login (rate-limited)
  logout.php             Logout
  dashboard.php          Metrics dashboard
  _auth.php              Auth + permissions (internal)
  _layout.php            Shared layout (internal)
  _change_password.php   Password change
  staff.css              Staff console styles
  rfq_list.php           RFQ list with filters
  rfq_view.php           RFQ detail + status update
  customers_list.php     Customer accounts list
  products_list.php      Products catalog
  product_edit.php       Product CRUD form
  brands_list.php        Brands list
  brand_edit.php         Brand CRUD
  industries_list.php    Industries list
  industry_edit.php      Industry CRUD
  articles_list.php      Articles list
  article_edit.php       Article CRUD
  staff_list.php         Staff users (Super Admin only)
  staff_edit.php         Staff user CRUD (Super Admin only)
  .htaccess              Internal file protection

CUSTOMER PORTAL (/customer/) — NEW IN V4
  login.php              Customer login (email or mobile)
  register.php           Account creation
  forgot_password.php    Reset request with token generation
  reset_password.php     Token-based password reset
  logout.php             Logout
  _auth.php              Customer auth helpers (internal)
  _layout.php            Customer portal layout (internal)
  customer.css           Customer portal styles
  dashboard.php          Customer overview with stats
  cart.php               RFQ cart management & combined submission
  orders.php             Customer's RFQ orders list
  order_view.php         Single order detail with status timeline
  profile.php            Profile + password edit
  .htaccess              Internal file protection

DATABASE
  install_full_v2.sql    Complete schema (V2 SQL — works for V4)

UPLOAD DIRECTORIES (created automatically)
  uploads/boq/           BOQ/RFQ attachments (denied from public)
  uploads/products/      Product images (publicly accessible)
  uploads/datasheets/    PDF datasheets (publicly accessible)
  uploads/suppliers/     Internal supplier docs (denied)
  uploads/certificates/  Internal certificates (denied)


───────────────────────────────────────────────────────────────
INSTALLATION STEPS
───────────────────────────────────────────────────────────────

STEP 1 — Database Setup (Hostinger)
  • Create a MySQL database
  • Set a strong password (16+ characters)
  • Note the DB name, user, and password

STEP 2 — Import Schema
  • phpMyAdmin → select database → Import → install_full_v2.sql
  • Verify all tables created successfully

STEP 3 — Edit Configuration
  Open includes/config.php and update:

    define('DB_NAME', 'YOUR_DB_NAME');
    define('DB_USER', 'YOUR_DB_USER');
    define('DB_PASS', 'YOUR_DB_PASSWORD');

  Generate a CSRF secret (run on any computer with PHP):
    php -r "echo bin2hex(random_bytes(32));"

  Replace:
    define('CSRF_SECRET', 'PASTE_THE_64_CHAR_HEX_HERE');

STEP 4 — Upload Files
  Upload all files to public_html/ on Hostinger.
  Maintain folder structure exactly.

STEP 5 — Set File Permissions
  Folders:                       755
  PHP files:                     644
  .htaccess (all):               644
  includes/config.php:           600 (most restrictive)
  uploads/ subfolders:           755 (with PHP write access)

STEP 6 — Initial Super Admin (CRITICAL)
  Visit:  https://www.phoenix.com.sa/setup_admin.php
  • Enter your name, email, and a STRONG password
    (12+ chars, mixed case, digit, symbol)
  • Submit
  • IMMEDIATELY DELETE setup_admin.php from the server

STEP 7 — Enable HTTPS
  • Enable Let's Encrypt SSL in Hostinger
  • Edit .htaccess and uncomment the HTTPS redirect block
  • Uncomment the Strict-Transport-Security header

STEP 8 — Test Public Site
  • https://www.phoenix.com.sa/  → homepage with branding
  • /sitemap.php  → XML sitemap
  • /includes/config.php  → must return 403

STEP 9 — Test Staff Console
  • https://www.phoenix.com.sa/staff/login.php
  • Sign in with credentials from Step 6
  • Verify dashboard loads with metrics

STEP 10 — Test Customer Portal
  • https://www.phoenix.com.sa/customer/register.php
  • Create a test customer account
  • Sign in at /customer/login.php
  • Verify dashboard, cart, orders pages all load


───────────────────────────────────────────────────────────────
STAFF CONSOLE — QUICK GUIDE
───────────────────────────────────────────────────────────────

LOGIN URL:  https://www.phoenix.com.sa/staff/login.php
RATE LIMIT: 5 attempts per 10 minutes per IP

ROLES & PERMISSIONS

  Super Admin           Full access including staff management
  Account Manager       RFQs + customers + orders
  Sales Agent           View RFQs + customers + products
  Procurement Officer   RFQs + products + suppliers
  Data Entry            Catalog content (products/brands/etc.)

CREATING STAFF
  Super Admin → Staff Users → + Add Staff Member


───────────────────────────────────────────────────────────────
CUSTOMER PORTAL — QUICK GUIDE
───────────────────────────────────────────────────────────────

REGISTRATION URL:  https://www.phoenix.com.sa/customer/register.php
LOGIN URL:         https://www.phoenix.com.sa/customer/login.php
RATE LIMIT:        5 login attempts / 10 minutes per IP
                   3 registrations / 30 minutes per IP

CUSTOMER FEATURES
  Dashboard          Personal RFQ stats and recent activity
  RFQ Cart           Add products, edit quantities/notes, submit
  My Orders          Track all submitted RFQs by status
  Order Detail       View order with status timeline
  Profile            Edit personal info, change password
  Forgot Password    Self-service reset with email token

CART WORKFLOW
  1. Visitor browses /index.php#marketplace
  2. Clicks "Add to RFQ" → product added to session cart
  3. Site prompts login if not authenticated
  4. After login, guest cart is automatically migrated to account
  5. Customer reviews/edits cart at /customer/cart.php
  6. Customer adds notes and submits combined RFQ
  7. RFQ appears in Staff Console for assignment and quotation

PASSWORD RESET FLOW
  1. Customer requests reset at /customer/forgot_password.php
  2. System generates secure token (1-hour expiry)
  3. Reset link is shown directly (development mode without SMTP)
     OR sent via email (when SMTP is configured)
  4. Customer clicks link → sets new password
  5. Token marked as used (one-time only)

NOTE ON SMTP
  Until SMTP is configured, the password reset link is shown
  on-screen for development. In production, this MUST be replaced
  with email delivery, and the on-screen link MUST be removed.


───────────────────────────────────────────────────────────────
SECURITY CHECKLIST (POST-DEPLOYMENT)
───────────────────────────────────────────────────────────────

[ ] Database password is strong (16+ chars)
[ ] CSRF_SECRET is a fresh 64-char hex string
[ ] setup_admin.php has been DELETED from the server
[ ] Initial Super Admin password is strong
[ ] HTTPS is forced via .htaccess
[ ] HSTS header is enabled
[ ] /includes/config.php returns 403 when accessed directly
[ ] /staff/_auth.php returns 403 when accessed directly
[ ] /customer/_auth.php returns 403 when accessed directly
[ ] File uploads accept only whitelisted extensions
[ ] Backups are configured


───────────────────────────────────────────────────────────────
KNOWN LIMITATIONS / FUTURE PHASES
───────────────────────────────────────────────────────────────

• Email notifications (SMTP) configuration needed for:
  - Customer password reset emails
  - RFQ submission alerts to staff
  - Customer welcome emails
  - Status update notifications to customers

• Arabic translation layer not yet activated
  (helpers.php has t() function ready)

• Bulk product import (Excel/CSV) not yet built

• Two-factor authentication (2FA) for staff and customers

• Quotation generation module (PDF quote builder for staff)

• Customer notifications dashboard (in-portal alerts)


───────────────────────────────────────────────────────────────
SUPPORT
───────────────────────────────────────────────────────────────

Phoenix Arabia Contracting Co. Ltd.
8th Floor, Office 8B, Shahad Tower
King Saud Bin Abdulaziz St, Qurtubah, Al Khobar
Saudi Arabia

Phone:    +966 53 303 3352
WhatsApp: +966 59 771 1094
Email:    info@phoenix.com.sa
Website:  https://www.phoenix.com.sa

C.R. 1010871269
U.N. 7035078612
Aramco Vendor 10112458

═══════════════════════════════════════════════════════════════
END OF V4 INSTALLATION GUIDE
═══════════════════════════════════════════════════════════════
