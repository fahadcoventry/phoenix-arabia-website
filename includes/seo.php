<?php
// ═══════════════════════════════════════════════════════════
// Phoenix Arabia — SEO Helpers (Meta + Schema.org)
// ═══════════════════════════════════════════════════════════

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// ── Title (with site suffix) ──
function seo_title(?string $title = null): string {
    if (empty($title)) {
        return 'Phoenix Arabia | Industrial Supply & RFQ Marketplace | Saudi Arabia';
    }
    $title = trim($title);
    if (stripos($title, 'phoenix') === false) {
        $title .= ' | Phoenix Arabia';
    }
    return e($title);
}

// ── Meta description ──
function seo_description(?string $desc = null): string {
    $default = 'Phoenix Arabia Contracting Co. Ltd. — Saudi Aramco Approved Vendor. Industrial supply, RFQ marketplace, MTO pricing, and procurement gateway for EPC contractors and industrial buyers in the Kingdom.';
    return e($desc ?: $default);
}

// ── Canonical URL ──
function canonical(string $path): string {
    $path = ltrim($path, '/');
    return e(SITE_URL . '/' . $path);
}

// ── Schema.org: Organization ──
function organization_schema(): string {
    $data = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => SITE_LEGAL_NAME,
        'alternateName' => 'Phoenix Arabia Industrial & Energy Services',
        'url'      => SITE_URL,
        'logo'     => SITE_URL . '/assets/logo.png',
        'description' => 'Saudi Aramco Approved Vendor providing industrial supply, RFQ marketplace, and procurement gateway for EPC contractors and industrial buyers.',
        'address' => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => '8th Floor, Office 8B, Shahad Tower, King Saud Bin Abdulaziz St, Qurtubah',
            'addressLocality' => 'Al Khobar',
            'addressRegion'   => 'Eastern Province',
            'addressCountry'  => 'SA',
        ],
        'telephone' => OFFICE_PHONE_RAW,
        'email'     => OFFICE_EMAIL_GENERAL,
        'taxID'     => CR_NUMBER,
        'identifier' => [
            ['@type' => 'PropertyValue', 'name' => 'Commercial Registration', 'value' => CR_NUMBER],
            ['@type' => 'PropertyValue', 'name' => 'Unified Number',         'value' => UNIFIED_NUMBER],
            ['@type' => 'PropertyValue', 'name' => 'Aramco Vendor Code',     'value' => ARAMCO_VENDOR],
        ],
    ];
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

// ── Schema.org: Product ──
function product_schema(array $p): string {
    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $p['name'] ?? '',
        'description' => $p['description'] ?? $p['short_description'] ?? '',
        'sku'         => $p['sku'] ?? '',
        'brand'       => ['@type' => 'Brand', 'name' => $p['brand'] ?? 'Phoenix Arabia'],
        'category'    => $p['category'] ?? '',
        'image'       => SITE_URL . '/' . ltrim($p['image_path'] ?? 'assets/placeholder-product.svg', '/'),
    ];
    
    if (!empty($p['fixed_price']) && ($p['price_mode'] ?? '') === 'Show Price') {
        $data['offers'] = [
            '@type'         => 'Offer',
            'price'         => $p['fixed_price'],
            'priceCurrency' => $p['currency'] ?? 'SAR',
            'availability'  => 'https://schema.org/InStock',
            'seller'        => ['@type' => 'Organization', 'name' => SITE_LEGAL_NAME],
        ];
    } else {
        $data['offers'] = [
            '@type'  => 'Offer',
            'priceSpecification' => [
                '@type'       => 'PriceSpecification',
                'description' => 'Request a Quotation',
            ],
            'availability' => 'https://schema.org/InStock',
            'seller'       => ['@type' => 'Organization', 'name' => SITE_LEGAL_NAME],
        ];
    }
    
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

// ── Schema.org: BreadcrumbList ──
function breadcrumb_schema(array $items): string {
    $list = [];
    foreach ($items as $i => $item) {
        $list[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $item['name'],
            'item'     => $item['url'] ?? null,
        ];
    }
    $data = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $list,
    ];
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

// ── Open Graph + Twitter Card ──
function og_tags(string $title, string $description, ?string $image = null, ?string $url = null): string {
    $img = $image ?: (SITE_URL . '/assets/og-default.jpg');
    $u = $url ?: (SITE_URL . $_SERVER['REQUEST_URI']);
    
    return '
<meta property="og:type" content="website">
<meta property="og:title" content="' . e($title) . '">
<meta property="og:description" content="' . e($description) . '">
<meta property="og:url" content="' . e($u) . '">
<meta property="og:image" content="' . e($img) . '">
<meta property="og:site_name" content="Phoenix Arabia">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="' . e($title) . '">
<meta name="twitter:description" content="' . e($description) . '">
<meta name="twitter:image" content="' . e($img) . '">';
}

