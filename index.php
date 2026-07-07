<?php
$pageTitle = 'ONE design — Tools for Designers who Care About the Details';
$pageDescription = 'A growing collection of free design tools — OKLCH palette generator, color picker, gradient builder, and more. Made by a designer, for designers.';
$activePage = 'index';
require 'includes/header.php';
?>

<main class="scrollable home-scrollable">

    <div class="topbar">
        <div class="topbar-greeting">
            <h2>Tools for designers who <br><em>care about the details</em></h2>
            <p>A growing collection of tools for designers — made by <a
                    href="https://rydesignstudios.com/?utm_source=onedesign" target="_blank" rel="noopener"
                    style="color: inherit;">a designer</a>.</p>
        </div>
    </div>

    <p class="section-label">Design</p>
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
                    <span class="card-title">OKLCH Color picker</span>
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

        <a href="/type-guide/" class="tool-card fade-in-4">
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
        <a href="/button-maker/" class="tool-card fade-in-5">
            <div class="card-preview card-preview--buttons">
                <div class="bm-home-preview">
                    <div class="bm-home-row">
                        <div class="bm-home-btn bm-home-btn--filled">Primary</div>
                        <div class="bm-home-btn bm-home-btn--outlined">Secondary</div>
                    </div>
                    <div class="bm-home-row">
                        <div class="bm-home-btn bm-home-btn--filled bm-home-btn--sm">Button</div>
                        <div class="bm-home-btn bm-home-btn--outlined bm-home-btn--sm">Button</div>
                        <div class="bm-home-btn bm-home-btn--ghost bm-home-btn--sm">Ghost</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Button maker</span>
                </div>
                <p class="card-desc">Design primary and secondary buttons in three sizes. Dial in border radius,
                    padding, font size, and weight — then export production-ready CSS.</p>
                <div class="card-footer">
                    <span class="card-meta">Primary · Secondary · 3 sizes · CSS export</span>
                    <div class="card-arrow"><svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg></div>
                </div>
            </div>
        </a>

        <a href="/shadow/" class="tool-card fade-in-6">
            <div class="card-preview card-preview--shadow">
                <div class="shadow-card-demo">
                    <div class="demo-card demo-card--xs"></div>
                    <div class="demo-card demo-card--md"></div>
                    <div class="demo-card demo-card--xl"></div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Shadow &amp; elevation</span>
                </div>
                <p class="card-desc">Build a semantic shadow scale tinted from your palette. Preview on light and dark
                    surfaces and export as CSS tokens or Figma JSON.</p>
                <div class="card-footer">
                    <span class="card-meta">Palette tint · Light &amp; dark · CSS export</span>
                    <div class="card-arrow"><svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg></div>
                </div>
            </div>
        </a>

        <a href="/border-glow/" class="tool-card fade-in-7">
            <div class="card-preview card-preview--glow">
                <div class="glow-home-demo beam-card--md beam-preset-vivid">
                    <div class="beam-bloom"></div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-header">
                    <span class="card-title">Border glow</span>
                </div>
                <p class="card-desc">Wrap a card, button, or search field in an animated conic-gradient border beam.
                    Tune palette, geometry, and motion, then export pure CSS.</p>
                <div class="card-footer">
                    <span class="card-meta">Card · Button · Search · CSS export</span>
                    <div class="card-arrow"><svg viewBox="0 0 24 24">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg></div>
                </div>
            </div>
        </a>

    </div>

    <p class="section-label">Tools</p>
    <div class="tools-grid">

        <a href="/case-converter/" class="tool-card fade-in-8">
            <div class="card-preview">
                <div class="card-preview-tags">
                    <div class="card-preview-tags-row">
                        <span class="card-preview-tag">camelCase</span>
                        <span class="card-preview-tag">PascalCase</span>
                    </div>
                    <div class="card-preview-tags-row">
                        <span class="card-preview-tag">snake_case</span>
                        <span class="card-preview-tag">kebab-case</span>
                    </div>
                    <div class="card-preview-tags-row">
                        <span class="card-preview-tag">SCREAMING_SNAKE</span>
                        <span class="card-preview-tag">dot.case</span>
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
<style>
    .home-scrollable {
        max-width: 1700px;
        width: 100%;
        margin: 0 auto;
    }

    .card-preview--shadow {
        align-items: center;
        justify-content: center;
    }

    .shadow-card-demo {
        display: flex;
        align-items: flex-end;
        gap: 40px;
    }

    .demo-card {
        background: rgba(54, 53, 51, 0.01);
        border-radius: 0.375rem;
    }

    .demo-card--xs {
        width: 36px;
        height: 36px;
        box-shadow: 0 4px 65px 0 rgba(255, 255, 255, 0.25), 0 4px 45px 0 rgba(255, 255, 255, 0.25), 0 4px 25px 0 rgba(255, 255, 255, 0.25), 0 1px 5px 0 rgba(255, 255, 255, 0.26);
    }

    .demo-card--md {
        width: 52px;
        height: 52px;
        box-shadow: 0 4px 65px 0 rgba(255, 255, 255, 0.25), 0 4px 45px 0 rgba(255, 255, 255, 0.25), 0 4px 25px 0 rgba(255, 255, 255, 0.25), 0 1px 5px 0 rgba(255, 255, 255, 0.26);
    }

    .demo-card--xl {
        width: 36px;
        height: 70px;
        box-shadow: 0 4px 65px 0 rgba(255, 255, 255, 0.25), 0 4px 45px 0 rgba(255, 255, 255, 0.25), 0 4px 25px 0 rgba(255, 255, 255, 0.25), 0 1px 5px 0 rgba(255, 255, 255, 0.26);
    }

    .card-preview--glow {
        align-items: center;
        justify-content: center;
    }

    .glow-home-demo.beam-card--md {
        --beam-angle: 42deg;
        --beam-stroke-opacity: 0.260;
        --beam-inner-opacity: 0.420;
        --beam-bloom-opacity: 0.240;
        --beam-bloom-blur: 8px;
        --beam-brightness: 1.30;
        --beam-saturate: 1.20;
        width: 98px;
        height: 62px;
        border-radius: 12px;
        background: #1d1d1f;
        position: relative;
        overflow: hidden;
    }

    .glow-home-demo.beam-card--md.beam-preset-vivid {
        --beam-angle: 42deg;
        --beam-stroke-opacity: 0.28;
        --beam-inner-opacity: 0.44;
        --beam-bloom-opacity: 0.25;
        --beam-bloom-blur: 8px;
        --beam-brightness: 1.34;
        --beam-saturate: 1.24;
    }

    .glow-home-demo.beam-card--md::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 12px;
        padding: 1px;
        clip-path: inset(0 round 12px);
        background:
            conic-gradient(from var(--beam-angle),
                transparent 0%, transparent 54%,
                rgba(255, 255, 255, 0.1) 57%, rgba(255, 255, 255, 0.3) 60%, rgba(255, 255, 255, 0.6) 63%, rgba(255, 255, 255, 0.75) 66%,
                rgba(255, 255, 255, 0.6) 69%, rgba(255, 255, 255, 0.3) 72%, rgba(255, 255, 255, 0.1) 75%,
                transparent 78%, transparent 100%),
            radial-gradient(ellipse 38px 22px at 33% -7.4%, rgb(255, 50, 100), transparent),
            radial-gradient(ellipse 33px 20px at 12% -5%, rgb(40, 140, 255), transparent),
            radial-gradient(ellipse 22px 38px at 2.1% 68.3%, rgb(50, 200, 80), transparent),
            radial-gradient(ellipse 11px 20px at 2.1% 68.3%, rgb(30, 185, 170), transparent),
            radial-gradient(ellipse 96px 17px at 74.4% 100%, rgb(100, 70, 255), transparent),
            radial-gradient(ellipse 45px 14px at 55% 100%, rgb(40, 140, 255), transparent),
            radial-gradient(ellipse 40px 17px at 93.9% 0%, rgb(255, 120, 40), transparent),
            radial-gradient(ellipse 14px 23px at 100% 27.1%, rgb(240, 50, 180), transparent),
            radial-gradient(ellipse 28px 26px at 100% 27.1%, rgb(180, 40, 240), transparent);
        -webkit-mask:
            conic-gradient(from var(--beam-angle),
                transparent 0%, transparent 30%,
                rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,
                white 52%, white 80%,
                rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,
                transparent 95%, transparent 100%),
            linear-gradient(#fff 0 0) content-box,
            linear-gradient(#fff 0 0);
        -webkit-mask-composite: source-in, xor;
        mask:
            conic-gradient(from var(--beam-angle),
                transparent 0%, transparent 30%,
                rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,
                white 52%, white 80%,
                rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,
                transparent 95%, transparent 100%),
            linear-gradient(#fff 0 0) content-box,
            linear-gradient(#fff 0 0);
        mask-composite: intersect, exclude;
        pointer-events: none;
        z-index: 2;
        opacity: var(--beam-stroke-opacity);
        filter: hue-rotate(0deg) brightness(var(--beam-brightness)) saturate(var(--beam-saturate));
    }

    .glow-home-demo.beam-card--md::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 12px;
        clip-path: inset(0 round 12px);
        background:
            radial-gradient(ellipse 34px 20px at 33% -7.4%, rgba(255, 50, 100, 0.45), transparent),
            radial-gradient(ellipse 29px 18px at 12% -5%, rgba(40, 140, 255, 0.45), transparent),
            radial-gradient(ellipse 19px 34px at 2.1% 68.3%, rgba(50, 200, 80, 0.45), transparent),
            radial-gradient(ellipse 10px 18px at 2.1% 68.3%, rgba(30, 185, 170, 0.45), transparent),
            radial-gradient(ellipse 86px 16px at 74.4% 100%, rgba(100, 70, 255, 0.45), transparent),
            radial-gradient(ellipse 41px 12px at 55% 100%, rgba(40, 140, 255, 0.45), transparent),
            radial-gradient(ellipse 36px 16px at 93.9% 0%, rgba(255, 120, 40, 0.45), transparent),
            radial-gradient(ellipse 12px 20px at 100% 27.1%, rgba(240, 50, 180, 0.45), transparent),
            radial-gradient(ellipse 25px 23px at 100% 27.1%, rgba(180, 40, 240, 0.45), transparent);
        box-shadow: inset 0 0 9px 1px rgba(255, 255, 255, 0.27);
        -webkit-mask-image:
            conic-gradient(from var(--beam-angle),
                transparent 0%, transparent 30%,
                rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,
                white 52%, white 80%,
                rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,
                transparent 95%, transparent 100%),
            linear-gradient(white, transparent 18px, transparent calc(100% - 18px), white),
            linear-gradient(to right, white, transparent 18px, transparent calc(100% - 18px), white);
        -webkit-mask-composite: source-in, source-over;
        mask-image:
            conic-gradient(from var(--beam-angle),
                transparent 0%, transparent 30%,
                rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,
                white 52%, white 80%,
                rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,
                transparent 95%, transparent 100%),
            linear-gradient(white, transparent 18px, transparent calc(100% - 18px), white),
            linear-gradient(to right, white, transparent 18px, transparent calc(100% - 18px), white);
        mask-composite: intersect, add;
        pointer-events: none;
        z-index: 1;
        opacity: var(--beam-inner-opacity);
        filter: hue-rotate(0deg) brightness(var(--beam-brightness)) saturate(var(--beam-saturate));
    }

    .glow-home-demo .beam-bloom {
        position: absolute;
        inset: 0;
        border-radius: 12px;
        clip-path: inset(0 round 12px);
        background: conic-gradient(from var(--beam-angle),
                transparent 0%, transparent 58%,
                rgba(255, 255, 255, 0.03) 62%, rgba(255, 255, 255, 0.08) 65%,
                rgba(255, 255, 255, 0.2) 67%, rgba(255, 255, 255, 0.45) 69%,
                rgba(255, 255, 255, 0.85) 70%, rgba(255, 255, 255, 0.85) 70.5%,
                rgba(255, 255, 255, 0.45) 71.5%, rgba(255, 255, 255, 0.2) 73%,
                rgba(255, 255, 255, 0.08) 75%, rgba(255, 255, 255, 0.03) 78%,
                transparent 82%);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask-composite: exclude;
        padding: 1px;
        filter: blur(var(--beam-bloom-blur)) brightness(var(--beam-brightness)) saturate(var(--beam-saturate));
        pointer-events: none;
        z-index: 3;
        opacity: var(--beam-bloom-opacity);
    }
</style>
<?php require 'includes/footer.php'; ?>