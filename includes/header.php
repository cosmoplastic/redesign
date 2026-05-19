<?php
// Variables expected from caller:
// $pageTitle  (string) — page <title>
// $activePage (string) — 'index' | 'palette' | 'picker' | 'gradient' | 'case-converter'
// $shellClass (string, optional) — extra class appended to .shell
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,400&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,300&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">
</head>

<body>

    <div class="shell<?= isset($shellClass) ? ' ' . htmlspecialchars($shellClass) : '' ?>">

        <aside>
            <div class="sidebar-logo">
                <a href="/">ONE <em>design</em></a>
                <p class="tagline">For designers</p>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Tools</div>
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
                <a href="#" class="sidebar-link">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                    </svg>
                    Contrast checker
                    <span class="soon">Soon</span>
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
                    Saved palettes
                </a>
                <a href="#" class="sidebar-link">
                    <svg viewBox="0 0 24 24">
                        <circle cx="18" cy="5" r="3" />
                        <circle cx="6" cy="12" r="3" />
                        <circle cx="18" cy="19" r="3" />
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                    </svg>
                    Export history
                    <span class="soon">Soon</span>
                </a>
            </div>
            <div class="sidebar-footer">one.design<br>v1.0 · 4 tools available<br><a href="/admin/"
                    class="sidebar-footer-admin">admin</a></div>
        </aside>