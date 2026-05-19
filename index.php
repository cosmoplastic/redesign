<?php
$pageTitle = 'ONE design';
$activePage = 'index';
require 'includes/header.php';
?>

<main class="scrollable">

    <div class="topbar">
        <div class="topbar-greeting">
            <h2>Good morning, <em>designer.</em></h2>
            <p>What are you building today?</p>
        </div>
        <div class="badge">
            <span class="badge-dot"></span>
            4 tools available · more coming
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
                    <span class="tag tag-green">Available</span>
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

        <div class="tool-card coming-soon fade-in-2">
            <div class="card-preview">
                <div class="card-preview-soon">
                    <div>
                        <p class="soon-title">Aa</p>
                        <p class="text-micro">Coming soon</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Contrast checker</span>
                    <span class="tag">Soon</span>
                </div>
                <p class="card-desc">Check WCAG AA and AAA contrast ratios between
                    any two colors. See which stops in your palette are safe for
                    text at any size.</p>
                <div class="card-footer">
                    <span class="card-meta">WCAG 2.1 · APCA</span>
                    <div class="card-arrow disabled"><svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg></div>
                </div>
            </div>
        </div>

        <a href="/gradient/" class="tool-card fade-in-3">
            <div class="card-preview">
                <div id="grad-card-preview" style="width:100%;height:100%;"></div>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Gradient studio</span>
                    <span class="tag tag-green">Available</span>
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

        <a href="/color-picker/" class="tool-card fade-in-4">
            <div class="card-preview">
                <canvas id="picker-preview-canvas" width="120" height="120"></canvas>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Color picker</span>
                    <span class="tag tag-green">Available</span>
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

        <a href="/case-converter/" class="tool-card fade-in-5">
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
                    <span class="tag tag-green">Available</span>
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

    </div>
</main>
</div>

<script src="/assets/color-math.js"></script>
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

    (function () {
        function lerpHue(h1, h2, t) {
            let d = ((h2 - h1) % 360 + 360) % 360;
            if (d > 180) d -= 360;
            return ((h1 + d * t) % 360 + 360) % 360;
        }
        function lerpGrad(hexA, hexB, t) {
            const ra = hexToRgb(hexA), rb = hexToRgb(hexB);
            if (!ra || !rb) return hexA;
            const [La, Ca, Ha] = rgbToOklch(...ra);
            const [Lb, Cb, Hb] = rgbToOklch(...rb);
            return oklchToHex(La + (Lb - La) * t, Ca + (Cb - Ca) * t, lerpHue(Ha, Hb, t));
        }
        const el = document.getElementById('grad-card-preview');
        if (!el) return;
        const hexA = '#2563eb', hexB = '#e11d48';
        const n = 16;
        const ss = Array.from({ length: n + 1 }, (_, i) => {
            const t = i / n;
            return `${lerpGrad(hexA, hexB, t)} ${(t * 100).toFixed(1)}%`;
        }).join(', ');
        el.style.background = `linear-gradient(135deg, ${ss})`;
    })();

</script>

<?php require 'includes/footer.php'; ?>