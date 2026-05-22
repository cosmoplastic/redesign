<?php
$pageTitle = 'ONE design';
$activePage = 'index';
require 'includes/header.php';
?>

<main class="scrollable">

    <div class="topbar">
        <div class="topbar-greeting">
            <h2>Tools for designers who <br><em>care about the details</em></h2>
            <p>A growing collection of tools for designers — made by <a href="https://rydesignstudios.com/?utm_source=onedesign" target="_blank" rel="noopener" style="color: inherit;">a designer</a>.</p>
        </div>
    </div>

    <p class="section-label">Tools</p>
    <div class="tools-grid">

        <a href="/palette/" class="tool-card fade-in-1">
            <div class="card-preview">
                <div class="card-preview-swatches" id="preview-swatches"></div>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Palette generator</span>
                </div>
                <p class="card-desc">Generate full 50–900 shade scales from any
                    color using perceptually uniform OKLCH math. Export as CSS
                    variables or Figma-ready JSON.</p>
                <div class="card-footer">
                    <span class="card-meta">Up to 4 scales · 10 stops each</span>
                    <div class="card-arrow"><svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg></div>
                </div>
            </div>
        </a>

        <a href="/gradient/" class="tool-card fade-in-2">
            <div class="card-preview">
                <div id="grad-card-preview"></div>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Gradient studio</span>
                </div>
                <p class="card-desc">Build gradients that actually look good.
                    Interpolate through OKLCH to avoid the grey, muddy band that
                    ruins most CSS gradients.</p>
                <div class="card-footer">
                    <span class="card-meta">Linear · Radial · Copy CSS</span>
                    <div class="card-arrow"><svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg></div>
                </div>
            </div>
        </a>

        <a href="/color-picker/" class="tool-card fade-in-3">
            <div class="card-preview">
                <canvas id="picker-preview-canvas" width="120" height="120"></canvas>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Color picker</span>
                </div>
                <p class="card-desc">Pick colors natively in the OKLCH space. Drag
                    the gamut canvas, spin the hue wheel, and export in any format —
                    hex, oklch, rgb, hsl.</p>
                <div class="card-footer">
                    <span class="card-meta">Harmonies · WCAG contrast · 6
                        formats</span>
                    <div class="card-arrow"><svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg></div>
                </div>
            </div>
        </a>

        <a href="/case-converter/" class="tool-card fade-in-4">
            <div class="card-preview">
                <div class="card-preview-tags">
                    <div class="card-preview-tags-row">
                        <span class="card-preview-tag">camelCase</span>
                        <span class="card-preview-tag">snake_case</span>
                        <span class="card-preview-tag">UPPER CASE</span>
                    </div>
                    <div class="card-preview-tags-row">
                        <span class="card-preview-tag">PascalCase</span>
                        <span class="card-preview-tag">kebab-case</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Case converter</span>
                </div>
                <p class="card-desc">Transform text between 13 different cases and
                    formats — sentence, title, camel, snake, kebab, slug, and more.
                    Plus copy clean-up utilities.</p>
                <div class="card-footer">
                    <span class="card-meta">13 transforms · live preview</span>
                    <div class="card-arrow"><svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg></div>
                </div>
            </div>
        </a>

        <a href="/type-guide/" class="tool-card fade-in-5">
            <div class="card-preview">
                <div class="card-preview-type">
                    <div class="card-preview-type-line"></div>
                    <div class="card-preview-type-line"></div>
                    <div class="card-preview-type-line"></div>
                    <div class="card-preview-type-line"></div>
                    <div class="card-preview-type-line"></div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Type guide</span>
                </div>
                <p class="card-desc">Set typography standards for desktop and mobile.
                    Choose a modular scale ratio, load Google Fonts, and export
                    CSS variables or utility classes.</p>
                <div class="card-footer">
                    <span class="card-meta">Modular scale · Desktop + Mobile · CSS export</span>
                    <div class="card-arrow"><svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg></div>
                </div>
            </div>
        </a>

    </div>
</main>
</div>

<script src="/assets/color-math.js?v=<?= APP_VERSION ?>"></script>
<script>
    const previewColors = ['#2563eb', '#e11d48'];
    const previewContainer = document.getElementById('preview-swatches');
    previewColors.forEach(hex => {
        genScale(hex).forEach(sh => {
            const d = document.createElement('div');
            d.className = 'ps'; d.style.background = sh;
            previewContainer.appendChild(d);
        });
    });

    (function () {
        const c = document.getElementById('picker-preview-canvas');
        if (!c) return;
        c.style.cssText = 'border-radius:50%;opacity:.9;display:block;margin:auto;align-self:center;';
        const ctx = c.getContext('2d'), cx = 60, cy = 60, outer = 58, inner = 38;
        for (let i = 0; i < 360; i++) {
            const a1 = (i / 360) * Math.PI * 2 - Math.PI / 2, a2 = ((i + 1) / 360) * Math.PI * 2 - Math.PI / 2;
            const [r, g, b] = oklchToRgb(0.65, 0.18, i);
            ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, outer, a1, a2); ctx.closePath();
            ctx.fillStyle = `rgb(${r},${g},${b})`; ctx.fill();
        }
        ctx.globalCompositeOperation = 'destination-out';
        ctx.beginPath(); ctx.arc(cx, cy, inner, 0, Math.PI * 2); ctx.fill();
        ctx.globalCompositeOperation = 'source-over';
        ctx.beginPath(); ctx.arc(cx, cy, 14, 0, Math.PI * 2);
        ctx.fillStyle = oklchToHex(0.6, 0.178, 264); ctx.fill();
    })();


</script>

<?php require 'includes/footer.php'; ?>