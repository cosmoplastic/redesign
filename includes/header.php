<?php
require_once __DIR__ . '/version.php';
// Variables expected from caller:
// $pageTitle       (string) — page <title>
// $pageDescription (string, optional) — meta description / og:description
// $pageRobots      (string, optional) — robots meta content
// $activePage      (string) — 'index' | 'palette' | 'picker' | 'gradient' | 'case-converter' | 'type-guide' | 'saved-palettes' | 'export-history'
// $shellClass      (string, optional) — extra class appended to .shell

$canonicalHost = 'https://oneredesigns.com';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($requestPath !== '/' && substr($requestPath, -1) !== '/') {
    $requestPath .= '/';
}
$canonicalUrl = $canonicalHost . $requestPath;

$pageLabelMap = [
    'index' => 'All tools',
    'palette' => 'Palette generator',
    'picker' => 'Color picker',
    'gradient' => 'Gradient studio',
    'type-guide' => 'Type guide',
    'shadow' => 'Shadow & Elevation',
    'button-maker' => 'Button maker',
    'border-glow' => 'Border glow',
    'case-converter' => 'Case converter',
    'saved-palettes' => 'Saved work',
    'export-history' => 'Export history',
];

$appSchemaMap = [
    'palette' => 'DesignApplication',
    'picker' => 'DesignApplication',
    'gradient' => 'DesignApplication',
    'type-guide' => 'DesignApplication',
    'shadow' => 'DesignApplication',
    'button-maker' => 'DesignApplication',
    'border-glow' => 'DesignApplication',
    'case-converter' => 'DeveloperApplication',
];

$structuredData = [];

if (($activePage ?? '') === 'index') {
    $structuredData[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'ONE design',
        'url' => $canonicalHost . '/',
        'description' => $pageDescription ?? '',
    ];

    $structuredData[] = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'ONE design',
        'url' => $canonicalHost . '/',
        'logo' => $canonicalHost . '/assets/favicon/favicon.svg',
    ];
} elseif (!empty($activePage) && isset($pageLabelMap[$activePage]) && empty($pageRobots)) {
    $breadcrumbItems = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'All tools',
            'item' => $canonicalHost . '/',
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => $pageLabelMap[$activePage],
            'item' => $canonicalUrl,
        ],
    ];

    $structuredData[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumbItems,
    ];

    if (isset($appSchemaMap[$activePage])) {
        $structuredData[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => $pageLabelMap[$activePage],
            'url' => $canonicalUrl,
            'description' => $pageDescription ?? '',
            'applicationCategory' => $appSchemaMap[$activePage],
            'operatingSystem' => 'Web',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'ONE design',
                'url' => $canonicalHost . '/',
            ],
        ];
    }
}

$socialImageAlt = 'Social preview for ' . $pageTitle;


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    <?php if (!empty($pageRobots)): ?>
        <meta name="robots" content="<?= htmlspecialchars($pageRobots) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:site_name" content="ONE design">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <?php if (!empty($pageDescription)): ?>
        <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
        <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <?php endif; ?>
    <meta property="og:image" content="https://oneredesigns.com/assets/social-thumb.jpg">
    <meta property="og:image:alt" content="<?= htmlspecialchars($socialImageAlt) ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="https://oneredesigns.com/assets/social-thumb.jpg">
    <meta name="twitter:image:alt" content="<?= htmlspecialchars($socialImageAlt) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,300&display=swap"
        rel="stylesheet">
    <?php if (!empty($structuredData)): ?>
        <script
            type="application/ld+json"><?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
    <script>
        (function () { try { var t = localStorage.getItem('site-theme'); if (t) { var v = JSON.parse(t), r = document.documentElement; for (var k in v) r.style.setProperty(k, v[k]); } } catch (e) { } })();
    </script>
    <link rel="stylesheet" href="<?= asset_versioned_path('/assets/style.css') ?>">
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/assets/favicon/site.webmanifest">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-082H85B6GJ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-082H85B6GJ');
    </script>
</head>

<body>

    <div class="shell<?= isset($shellClass) ? ' ' . htmlspecialchars($shellClass) : '' ?>">

        <div class="mob-header">
            <button class="mob-menu-btn" onclick="document.querySelector('.shell').classList.toggle('nav-open')"
                aria-label="Open menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <line x1="3" y1="12" x2="21" y2="12" />
                    <line x1="3" y1="18" x2="21" y2="18" />
                </svg>
            </button>
            <a href="/" class="mob-logo">ONE <em>design</em></a>
        </div>
        <div class="mob-backdrop" onclick="document.querySelector('.shell').classList.remove('nav-open')"></div>

        <aside>
            <div class="sidebar-logo">
                <a href="/">ONE <em>design</em></a>
                <p class="tagline">For designers</p>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Design</div>
                <a href="/" class="sidebar-link<?= $activePage === 'index' ? ' active' : '' ?>">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>
                    All tools
                </a>
                <a href="/palette/" class="sidebar-link<?= $activePage === 'palette' ? ' active' : '' ?>">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 2a10 10 0 010 20" />
                        <path d="M2 12h10" />
                    </svg>
                    Palette generator
                </a>
                <a href="/color-picker/" class="sidebar-link<?= $activePage === 'picker' ? ' active' : '' ?>">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M3 12h3M18 12h3M12 3v3M12 18v3" />
                    </svg>
                    Color picker
                </a>
                <a href="/gradient/" class="sidebar-link<?= $activePage === 'gradient' ? ' active' : '' ?>">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                        <line x1="4" y1="22" x2="4" y2="15" />
                    </svg>
                    Gradient studio
                </a>
                <a href="/type-guide/" class="sidebar-link<?= $activePage === 'type-guide' ? ' active' : '' ?>">
                    <svg viewBox="0 0 24 24">
                        <line x1="3" y1="5" x2="21" y2="5" />
                        <line x1="3" y1="10" x2="18" y2="10" />
                        <line x1="3" y1="15" x2="14" y2="15" />
                        <line x1="3" y1="20" x2="9" y2="20" />
                    </svg>
                    Type guide
                </a>
                <a href="/shadow/" class="sidebar-link <?= $activePage === 'shadow' ? 'active' : '' ?>">
                    <!-- Shadow icon: stacked layers -->
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="8" width="18" height="10" rx="2" />
                        <rect x="5" y="5" width="14" height="3" rx="1" opacity=".5" />
                    </svg>
                    Shadow &amp; Elevation
                </a>
                <a href="/button-maker/" class="sidebar-link<?= $activePage === 'button-maker' ? ' active' : '' ?>">
                    <svg viewBox="0 0 24 24">
                        <rect x="2" y="8" width="20" height="8" rx="3" />
                    </svg>
                    Button maker
                </a>
                <a href="/border-glow/" class="sidebar-link<?= $activePage === 'border-glow' ? ' active' : '' ?>">
                    <!-- Border glow icon: rounded card with a spark -->
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="5" />
                        <path d="M12 8.5l1 2.5 2.5 1-2.5 1-1 2.5-1-2.5L8.5 12l2.5-1z" />
                    </svg>
                    Border glow
                </a>

            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Tools</div>
                <a href="/case-converter/" class="sidebar-link<?= $activePage === 'case-converter' ? ' active' : '' ?>">
                    <svg viewBox="0 0 24 24">
                        <polyline points="4 7 4 4 20 4 20 7" />
                        <line x1="9" y1="20" x2="15" y2="20" />
                        <line x1="12" y1="4" x2="12" y2="20" />
                    </svg>
                    Case converter
                </a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Workspace</div>
                <a href="/saved-palettes/" class="sidebar-link<?= $activePage === 'saved-palettes' ? ' active' : '' ?>">
                    <svg viewBox="0 0 24 24">
                        <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z" />
                    </svg>
                    Saved
                </a>
                <a href="/export-history/" class="sidebar-link<?= $activePage === 'export-history' ? ' active' : '' ?>">
                    <svg viewBox="0 0 24 24">
                        <circle cx="18" cy="5" r="3" />
                        <circle cx="6" cy="12" r="3" />
                        <circle cx="18" cy="19" r="3" />
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                    </svg>
                    Export history
                </a>
            </div>
            <div class="sidebar-footer"><a href="/admin/" class="sidebar-footer-admin">admin</a><br>Built, not
                bought<br><a href="https://rydesignstudios.com/?utm_source=onedesign" target="_blank" rel="noopener"
                    class="sidebar-footer-link">Ryan Pugh</a></div>
        </aside>