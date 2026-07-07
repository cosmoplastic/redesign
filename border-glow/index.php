<?php
$pageTitle = 'Border Glow — ONE design';
$pageDescription = 'A live configurator for animated glowing card borders — tune the palette, geometry, and motion, then copy the generated CSS.';
$activePage = 'border-glow';
$shellClass = 'full-height';
require '../includes/header.php';
?>

<style>
  /* Opt out of the site-wide inherited uppercase; explicit labels below re-apply it. */
  .border-glow, .border-glow .bg-pre { text-transform: none; }

  /* ── Right area: preview + generated code ── */
  .border-glow .bg-right {
    flex: 1;
    overflow-y: auto;
    padding: 28px;
    display: flex;
    flex-direction: column;
    gap: 24px;
  }
  .border-glow .bg-stage {
    flex-shrink: 0;
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    min-height: 340px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 32px;
    transition: background 0.25s ease;
  }
  .border-glow .beam-card { width: min(380px, 100%); }
  .border-glow .beam-card .content { position: relative; padding: 32px 28px; z-index: 0; }
  .border-glow .beam-card .content h3 { font-family: var(--serif); font-size: 18px; font-weight: 300; margin-bottom: 8px; letter-spacing: -0.01em; }
  .border-glow .beam-card .content p { font-family: var(--mono); font-size: 12.5px; line-height: 1.6; }

  /* ── Control rows (left panel) ── */
  .border-glow .bg-row { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
  .border-glow .bg-row:last-child { margin-bottom: 0; }
  .border-glow .bg-row label {
    font-family: var(--mono);
    font-size: 11.5px;
    color: var(--color-text-400);
    width: 74px;
    flex-shrink: 0;
    line-height: 1.35;
  }
  .border-glow .val {
    font-family: var(--mono);
    font-size: 11.5px;
    color: var(--color-text-100);
    min-width: 38px;
    text-align: right;
    font-variant-numeric: tabular-nums;
    flex-shrink: 0;
  }
  .border-glow .bg-toggles { display: flex; gap: 5px; flex-wrap: wrap; }
  .border-glow .toggle-btn {
    font-family: var(--mono);
    font-size: 11px;
    letter-spacing: 0.02em;
    color: var(--color-text-300);
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    padding: 5px 9px;
    cursor: pointer;
    transition: color .13s, border-color .13s, background .13s;
  }
  .border-glow .toggle-btn:hover { color: var(--color-text-100); border-color: var(--border2); }
  .border-glow .toggle-btn.active { color: var(--color-text-50); border-color: var(--border3); background: var(--bg4); }

  .border-glow input[type="color"] {
    -webkit-appearance: none; appearance: none;
    width: 34px; height: 26px;
    border: 1px solid var(--border2); border-radius: var(--r-sm);
    background: transparent; padding: 2px; cursor: pointer; flex-shrink: 0;
  }
  .border-glow input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
  .border-glow input[type="color"]::-webkit-color-swatch { border: none; border-radius: 4px; }
  .border-glow input[type="color"]::-moz-color-swatch { border: none; border-radius: 4px; }

  .border-glow input[type="range"] {
    -webkit-appearance: none; appearance: none;
    flex: 1; min-width: 0; height: 3px; border-radius: 2px;
    background: var(--border2); outline: none; cursor: pointer; padding: 0;
  }
  .border-glow input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none; width: 14px; height: 14px; border-radius: 50%;
    background: var(--color-text-100); border: 2px solid var(--bg); transition: transform .12s;
  }
  .border-glow input[type="range"]::-webkit-slider-thumb:hover { transform: scale(1.2); }
  .border-glow input[type="range"]::-moz-range-thumb {
    width: 12px; height: 12px; border-radius: 50%;
    background: var(--color-text-100); border: 2px solid var(--bg);
  }

  .border-glow .custom-colors { display: none; }
  .border-glow .custom-colors.visible { display: block; }

  /* ── Generated code ── */
  .border-glow .bg-code-head {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;
  }
  .border-glow .bg-code-head span {
    font-family: var(--mono); font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-text-400);
  }
  .border-glow .copy-btn {
    font-family: var(--mono); font-size: 11px; color: var(--color-text-300);
    background: var(--bg3); border: 1px solid var(--border2); border-radius: var(--r-sm);
    padding: 6px 14px; cursor: pointer; transition: border-color .13s, color .13s;
  }
  .border-glow .copy-btn:hover { border-color: var(--border3); color: var(--color-text-100); }
  .border-glow .copy-btn.done { color: var(--green); border-color: var(--green-border); }
  .border-glow .bg-pre {
    background: var(--bg2); border: 1px solid var(--border); border-radius: var(--r-lg);
    padding: 18px 20px; font-family: var(--mono); font-size: 12px; line-height: 1.7;
    color: #c9c5be; overflow-x: auto; tab-size: 2; white-space: pre; margin: 0;
  }
</style>

<main class="panel border-glow">

  <div class="topstrip">
    <span class="topstrip-title">Border <em>glow</em></span>
    <div class="topstrip-actions">
      <button class="btn" id="bg-export-btn" type="button">
        <svg viewBox="0 0 24 24">
          <rect x="9" y="9" width="13" height="13" rx="2" />
          <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
        </svg>
        Export
      </button>
    </div>
  </div>

  <div class="workspace">

    <!-- ── LEFT: controls ────────────────────────────── -->
    <div class="grad-panel">

      <div class="grad-section">
        <label class="field-label">Palette</label>
        <div class="bg-row">
          <label>Variant</label>
          <div class="bg-toggles" id="variants"></div>
        </div>
        <div class="custom-colors" id="customwrap">
          <div class="bg-row">
            <label for="cc1">Custom A</label>
            <input type="color" id="cc1" value="#7c3aed">
            <span class="val" id="cc1v">#7c3aed</span>
          </div>
          <div class="bg-row">
            <label for="cc2">Custom B</label>
            <input type="color" id="cc2" value="#06b6d4">
            <span class="val" id="cc2v">#06b6d4</span>
          </div>
        </div>
        <div class="bg-row">
          <label>Theme</label>
          <div class="bg-toggles">
            <button class="toggle-btn active" id="theme-dark" type="button">Dark</button>
            <button class="toggle-btn" id="theme-light" type="button">Light</button>
          </div>
        </div>
        <div class="bg-row">
          <label for="cardbg">Card</label>
          <input type="color" id="cardbg" value="#1d1d1f">
          <span class="val" id="cardbgv">#1d1d1f</span>
        </div>
        <div class="bg-row">
          <label for="pagebg">Stage</label>
          <input type="color" id="pagebg" value="#0f0f10">
          <span class="val" id="pagebgv">#0f0f10</span>
        </div>
      </div>

      <div class="grad-section">
        <label class="field-label">Geometry</label>
        <div class="bg-row">
          <label for="rad">Radius</label>
          <input type="range" id="rad" min="0" max="40" step="1" value="16">
          <span class="val" id="radv">16px</span>
        </div>
        <div class="bg-row">
          <label for="bw">Border width</label>
          <input type="range" id="bw" min="1" max="4" step="1" value="1">
          <span class="val" id="bwv">1px</span>
        </div>
      </div>

      <div class="grad-section">
        <label class="field-label">Glow tuning</label>
        <div class="bg-row">
          <label for="strength">Strength</label>
          <input type="range" id="strength" min="0" max="2" step="0.05" value="1">
          <span class="val" id="strengthv">1.00</span>
        </div>
        <div class="bg-row">
          <label for="brightness">Brightness</label>
          <input type="range" id="brightness" min="0.5" max="2.5" step="0.05" value="1.3">
          <span class="val" id="brightnessv">1.30</span>
        </div>
        <div class="bg-row">
          <label for="saturation">Saturation</label>
          <input type="range" id="saturation" min="0.5" max="2.5" step="0.05" value="1.2">
          <span class="val" id="saturationv">1.20</span>
        </div>
        <div class="bg-row">
          <label for="huerange">Hue range</label>
          <input type="range" id="huerange" min="0" max="90" step="5" value="30">
          <span class="val" id="huerangev">30&deg;</span>
        </div>
      </div>

      <div class="grad-section">
        <label class="field-label">Motion</label>
        <div class="bg-row">
          <label for="dur">Duration</label>
          <input type="range" id="dur" min="0.5" max="8" step="0.02" value="1.96">
          <span class="val" id="durv">1.96s</span>
        </div>
        <div class="bg-row">
          <label>Hue shift</label>
          <div class="bg-toggles">
            <button class="toggle-btn active" id="hueshift" type="button">Animated</button>
          </div>
        </div>
        <div class="bg-row">
          <label>Playback</label>
          <div class="bg-toggles">
            <button class="toggle-btn active" id="play" type="button">Playing</button>
          </div>
        </div>
      </div>

    </div>

    <!-- ── RIGHT: preview + generated code ───────────── -->
    <div class="grad-main">
      <div class="bg-right">

        <div class="bg-stage" id="stage">
          <div class="beam-card" id="card">
            <div class="beam-bloom"></div>
            <div class="content" id="cardcontent">
              <h3>Your card</h3>
              <p>Any content sits inside the glowing border. The beam is pure CSS plus a tiny rAF driver, so it animates
                in every browser.</p>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>

  <!-- ── Export modal ──────────────────────────────── -->
  <div class="export-modal" id="export-modal">
    <div class="export-modal-backdrop" data-close></div>
    <div class="export-modal-box">
      <div class="export-modal-header">
        <span style="font-family:var(--mono);font-size:12px;color:var(--color-text-300)">Generated CSS + markup + driver</span>
        <div class="export-modal-actions">
          <button class="copy-btn" id="copy" type="button">Copy</button>
          <button class="export-modal-close" data-close aria-label="Close">&times;</button>
        </div>
      </div>
      <div class="export-modal-body">
        <pre class="export-modal-code" id="css"></pre>
      </div>
    </div>
  </div>
</main>
</div>

<!-- Runtime-generated beam layers land here -->
<style id="beam-style"></style>

<script>
  (function () {
    'use strict';
    const $ = (id) => document.getElementById(id);

    /* ================================================================
       Data ported verbatim from border-beam/src/styles.ts
       ================================================================ */

    // colorPalettes[variant].border — 9 stationary radial ellipses (md size)
    const colorPalettes = {
      colorful: [
        { color: [255, 50, 100], pos: '33% -7.4%', size: [70, 40] },
        { color: [40, 140, 255], pos: '12% -5%', size: [60, 35] },
        { color: [50, 200, 80], pos: '2.1% 68.3%', size: [40, 70] },
        { color: [30, 185, 170], pos: '2.1% 68.3%', size: [20, 35] },
        { color: [100, 70, 255], pos: '74.4% 100%', size: [180, 32] },
        { color: [40, 140, 255], pos: '55% 100%', size: [85, 26] },
        { color: [255, 120, 40], pos: '93.9% 0%', size: [74, 32] },
        { color: [240, 50, 180], pos: '100% 27.1%', size: [26, 42] },
        { color: [180, 40, 240], pos: '100% 27.1%', size: [52, 48] }
      ],
      mono: [
        { color: [180, 180, 180], pos: '33% -7.4%', size: [70, 40] },
        { color: [140, 140, 140], pos: '12% -5%', size: [60, 35] },
        { color: [160, 160, 160], pos: '2.1% 68.3%', size: [40, 70] },
        { color: [130, 130, 130], pos: '2.1% 68.3%', size: [20, 35] },
        { color: [170, 170, 170], pos: '74.4% 100%', size: [180, 32] },
        { color: [150, 150, 150], pos: '55% 100%', size: [85, 26] },
        { color: [190, 190, 190], pos: '93.9% 0%', size: [74, 32] },
        { color: [145, 145, 145], pos: '100% 27.1%', size: [26, 42] },
        { color: [165, 165, 165], pos: '100% 27.1%', size: [52, 48] }
      ],
      ocean: [
        { color: [100, 80, 220], pos: '33% -7.4%', size: [70, 40] },
        { color: [60, 120, 255], pos: '12% -5%', size: [60, 35] },
        { color: [80, 100, 200], pos: '2.1% 68.3%', size: [40, 70] },
        { color: [50, 140, 220], pos: '2.1% 68.3%', size: [20, 35] },
        { color: [120, 80, 255], pos: '74.4% 100%', size: [180, 32] },
        { color: [70, 130, 255], pos: '55% 100%', size: [85, 26] },
        { color: [140, 100, 240], pos: '93.9% 0%', size: [74, 32] },
        { color: [90, 110, 230], pos: '100% 27.1%', size: [26, 42] },
        { color: [130, 70, 255], pos: '100% 27.1%', size: [52, 48] }
      ],
      sunset: [
        { color: [255, 80, 50], pos: '33% -7.4%', size: [70, 40] },
        { color: [255, 160, 40], pos: '12% -5%', size: [60, 35] },
        { color: [255, 120, 60], pos: '2.1% 68.3%', size: [40, 70] },
        { color: [255, 200, 50], pos: '2.1% 68.3%', size: [20, 35] },
        { color: [255, 100, 80], pos: '74.4% 100%', size: [180, 32] },
        { color: [255, 180, 60], pos: '55% 100%', size: [85, 26] },
        { color: [255, 60, 60], pos: '93.9% 0%', size: [74, 32] },
        { color: [255, 140, 50], pos: '100% 27.1%', size: [26, 42] },
        { color: [255, 90, 70], pos: '100% 27.1%', size: [52, 48] }
      ]
    };

    // sizeThemePresets.md — tuned layer opacities per theme
    const themePresets = {
      dark: { strokeOpacity: 0.26, innerOpacity: 0.42, bloomOpacity: 0.24, innerShadow: 'rgba(255, 255, 255, 0.27)', saturation: 1.2 },
      light: { strokeOpacity: 0.12, innerOpacity: 0.26, bloomOpacity: 0.34, innerShadow: 'rgba(0, 0, 0, 0.14)', saturation: 1.5 }
    };

    /* ================================================================
       State
       ================================================================ */

    const state = {
      variant: 'colorful',
      theme: 'dark',
      cc1: '#7c3aed', cc2: '#06b6d4',
      cardbg: '#1d1d1f', pagebg: '#0f0f10',
      radius: 16, borderWidth: 1,
      strength: 1, brightness: 1.3, saturation: 1.2, hueRange: 30,
      duration: 1.96,
      hueShift: true,
      playing: true,
      satTouched: false
    };

    /* ================================================================
       Palette helpers
       ================================================================ */

    function hexToRgb(hex) {
      return [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16));
    }

    function mix(a, b, t) {
      return a.map((v, i) => Math.round(v + (b[i] - v) * t));
    }

    // Custom variant: user's two colors distributed over the original
    // 9 ellipse positions/sizes with varied mix fractions for depth.
    const customMixes = [0, 0.3, 0.6, 0.45, 0.9, 0.2, 0.75, 1, 0.55];

    function getPalette(s) {
      if (s.variant !== 'custom') return colorPalettes[s.variant];
      const a = hexToRgb(s.cc1), b = hexToRgb(s.cc2);
      return colorPalettes.colorful.map((e, i) => ({
        color: mix(a, b, customMixes[i]), pos: e.pos, size: e.size
      }));
    }

    // getColorGradients — the stroke's stationary color field
    function colorGradients(palette, indent) {
      return palette.map((e) =>
        'radial-gradient(ellipse ' + e.size[0] + 'px ' + e.size[1] + 'px at ' + e.pos +
        ', rgb(' + e.color.join(', ') + '), transparent)'
      ).join(',\n' + indent);
    }

    // getInnerGradients — same field at 0.45 alpha (0.225 for mono), 90% size
    function innerGradients(palette, isMono, indent) {
      const alpha = isMono ? 0.225 : 0.45;
      return palette.map((e) =>
        'radial-gradient(ellipse ' + Math.round(e.size[0] * 0.9) + 'px ' + Math.round(e.size[1] * 0.9) +
        'px at ' + e.pos + ', rgba(' + e.color.join(', ') + ', ' + alpha + '), transparent)'
      ).join(',\n' + indent);
    }

    /* ================================================================
       CSS generation — mirrors generateBorderVariantCSS in styles.ts,
       with the @property spin replaced by a rAF-driven --beam-angle
       so it animates in every browser.
       ================================================================ */

    function whiteGradient(isDark) {
      const c = isDark
        ? ['rgba(255, 255, 255, 0.1)', 'rgba(255, 255, 255, 0.3)', 'rgba(255, 255, 255, 0.6)', 'rgba(255, 255, 255, 0.75)']
        : ['rgba(0, 0, 0, 0.08)', 'rgba(0, 0, 0, 0.2)', 'rgba(0, 0, 0, 0.4)', 'rgba(0, 0, 0, 0.55)'];
      return 'conic-gradient(\n      from var(--beam-angle, 0deg),\n      transparent 0%, transparent 54%,\n      ' +
        c[0] + ' 57%,\n      ' + c[1] + ' 60%,\n      ' + c[2] + ' 63%,\n      ' + c[3] + ' 66%,\n      ' +
        c[2] + ' 69%,\n      ' + c[1] + ' 72%,\n      ' + c[0] + ' 75%,\n      transparent 78%, transparent 100%\n    )';
    }

    function windowMask() {
      return 'conic-gradient(\n      from var(--beam-angle, 0deg),\n      transparent 0%, transparent 30%,\n      rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,\n      white 52%, white 80%,\n      rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,\n      transparent 95%, transparent 100%\n    )';
    }

    function bloomGradient(isDark) {
      const p = isDark ? '255, 255, 255' : '0, 0, 0';
      const peak = isDark ? '0.85' : '0.6';
      return 'conic-gradient(\n      from var(--beam-angle, 0deg),\n      transparent 0%, transparent 58%,\n      rgba(' + p + ', 0.03) 62%,\n      rgba(' + p + ', 0.08) 65%,\n      rgba(' + p + ', 0.2) 67%,\n      rgba(' + p + ', 0.45) 69%,\n      rgba(' + p + ', ' + peak + ') 70%,\n      rgba(' + p + ', ' + peak + ') 70.5%,\n      rgba(' + p + ', 0.45) 71.5%,\n      rgba(' + p + ', 0.2) 73%,\n      rgba(' + p + ', 0.08) 75%,\n      rgba(' + p + ', 0.03) 78%,\n      transparent 82%\n    )';
    }

    function generateBeamCSS(s) {
      const isDark = s.theme === 'dark';
      const preset = themePresets[s.theme];
      const isMono = s.variant === 'mono';
      const monoMul = isMono ? 0.5 : 1;

      const stroke = (preset.strokeOpacity * monoMul * s.strength).toFixed(3);
      const inner = (preset.innerOpacity * monoMul * s.strength).toFixed(3);
      const bloom = (preset.bloomOpacity * monoMul * s.strength).toFixed(3);
      const innerRadius = Math.max(0, s.radius - s.borderWidth);
      const palette = getPalette(s);
      const br = s.brightness.toFixed(2);
      const sat = s.saturation.toFixed(2);

      const hueAnim = s.hueShift
        ? '\n  animation: beam-hue-shift 12s ease-in-out infinite;'
        : '\n  filter: brightness(' + br + ') saturate(' + sat + ');';

      const hueKeyframes = s.hueShift
        ? '\n@keyframes beam-hue-shift {\n' +
        '  0%   { filter: hue-rotate(-' + s.hueRange + 'deg) brightness(' + br + ') saturate(' + sat + '); }\n' +
        '  50%  { filter: hue-rotate(' + s.hueRange + 'deg) brightness(' + br + ') saturate(' + sat + '); }\n' +
        '  100% { filter: hue-rotate(-' + s.hueRange + 'deg) brightness(' + br + ') saturate(' + sat + '); }\n' +
        '}\n'
        : '';

      return '.beam-card {\n' +
        '  position: relative;\n' +
        '  border-radius: ' + s.radius + 'px;\n' +
        '  background: ' + s.cardbg + ';\n' +
        '  overflow: hidden;\n' +
        '}\n\n' +
        '/* Stroke — stationary color field revealed by a rotating conic window */\n' +
        '.beam-card::after {\n' +
        '  content: "";\n' +
        '  position: absolute;\n' +
        '  inset: 0;\n' +
        '  border-radius: ' + innerRadius + 'px;\n' +
        '  padding: ' + s.borderWidth + 'px;\n' +
        '  clip-path: inset(0 round ' + s.radius + 'px);\n' +
        '  background:\n    ' + whiteGradient(isDark) + ',\n    ' + colorGradients(palette, '    ') + ';\n' +
        '  -webkit-mask:\n    ' + windowMask() + ',\n    linear-gradient(#fff 0 0) content-box,\n    linear-gradient(#fff 0 0);\n' +
        '  -webkit-mask-composite: source-in, xor;\n' +
        '  mask:\n    ' + windowMask() + ',\n    linear-gradient(#fff 0 0) content-box,\n    linear-gradient(#fff 0 0);\n' +
        '  mask-composite: intersect, exclude;\n' +
        '  pointer-events: none;\n' +
        '  z-index: 2;\n' +
        '  opacity: ' + stroke + ';' + hueAnim + '\n' +
        '}\n\n' +
        '/* Inner glow — soft color bleed inward, edge-faded 28px */\n' +
        '.beam-card::before {\n' +
        '  content: "";\n' +
        '  position: absolute;\n' +
        '  inset: 0;\n' +
        '  border-radius: ' + s.radius + 'px;\n' +
        '  background:\n    ' + innerGradients(palette, isMono, '    ') + ';\n' +
        '  box-shadow: inset 0 0 9px 1px ' + preset.innerShadow + ';\n' +
        '  -webkit-mask-image:\n    ' + windowMask() + ',\n' +
        '    linear-gradient(white, transparent 28px, transparent calc(100% - 28px), white),\n' +
        '    linear-gradient(to right, white, transparent 28px, transparent calc(100% - 28px), white);\n' +
        '  -webkit-mask-composite: source-in, source-over;\n' +
        '  mask-image:\n    ' + windowMask() + ',\n' +
        '    linear-gradient(white, transparent 28px, transparent calc(100% - 28px), white),\n' +
        '    linear-gradient(to right, white, transparent 28px, transparent calc(100% - 28px), white);\n' +
        '  mask-composite: intersect, add;\n' +
        '  pointer-events: none;\n' +
        '  z-index: 1;\n' +
        '  clip-path: inset(0 round ' + s.radius + 'px);\n' +
        '  opacity: ' + inner + ';' + hueAnim + '\n' +
        '}\n\n' +
        '/* Bloom — blurred highlight ring riding the beam head */\n' +
        '.beam-card .beam-bloom {\n' +
        '  position: absolute;\n' +
        '  inset: 0;\n' +
        '  border-radius: ' + innerRadius + 'px;\n' +
        '  clip-path: inset(0 round ' + s.radius + 'px);\n' +
        '  background: ' + bloomGradient(isDark) + ';\n' +
        '  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);\n' +
        '  -webkit-mask-composite: xor;\n' +
        '  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);\n' +
        '  mask-composite: exclude;\n' +
        '  padding: ' + s.borderWidth + 'px;\n' +
        '  filter: blur(8px) brightness(' + br + ') saturate(' + sat + ');\n' +
        '  pointer-events: none;\n' +
        '  z-index: 3;\n' +
        '  opacity: ' + bloom + ';\n' +
        '}\n' + hueKeyframes;
    }

    function generateDriverJS(s) {
      return '<' + 'script>\n' +
        '// Drives --beam-angle with rAF: works in every browser, unlike\n' +
        '// @property custom-property animation which the original relies on.\n' +
        '(function () {\n' +
        '  var cards = document.querySelectorAll(".beam-card");\n' +
        '  if (!cards.length) return;\n' +
        '  if (matchMedia("(prefers-reduced-motion: reduce)").matches) return;\n' +
        '  var dur = ' + Math.round(s.duration * 1000) + '; // ms per revolution\n' +
        '  function tick(t) {\n' +
        '    var deg = ((t / dur) % 1) * 360;\n' +
        '    for (var i = 0; i < cards.length; i++) {\n' +
        '      cards[i].style.setProperty("--beam-angle", deg + "deg");\n' +
        '    }\n' +
        '    requestAnimationFrame(tick);\n' +
        '  }\n' +
        '  requestAnimationFrame(tick);\n' +
        '})();\n' +
        '<' + '/script>';
    }

    function generateOutput(s) {
      return '/* Border beam — "md" preset, ported from\n' +
        '   github.com/Jakubantalik/border-beam (MIT) */\n\n' +
        generateBeamCSS(s) + '\n' +
        '/* Markup:\n' +
        '<div class="beam-card">\n' +
        '  <div class="beam-bloom"></div>\n' +
        '  <div class="content">Your content</div>\n' +
        '</div>\n\n' +
        '.content needs position: relative; z-index: 0;\n' +
        '*/\n\n' +
        '/* Driver — place before </body>: */\n' +
        generateDriverJS(s);
    }

    /* ================================================================
       Live preview wiring
       ================================================================ */

    const beamStyle = $('beam-style');
    const card = $('card');
    const stage = $('stage');
    const content = $('cardcontent');
    const out = $('css');

    function render() {
      const s = state;
      beamStyle.textContent = generateBeamCSS(s);
      stage.style.background = s.pagebg;
      const isDark = s.theme === 'dark';
      content.querySelector('h3').style.color = isDark ? '#fafaf9' : '#1c1c1e';
      content.querySelector('p').style.color = isDark ? '#a1a1aa' : '#5f5f66';

      $('cc1v').textContent = s.cc1;
      $('cc2v').textContent = s.cc2;
      $('cardbgv').textContent = s.cardbg;
      $('pagebgv').textContent = s.pagebg;
      $('radv').textContent = s.radius + 'px';
      $('bwv').textContent = s.borderWidth + 'px';
      $('strengthv').textContent = s.strength.toFixed(2);
      $('brightnessv').textContent = s.brightness.toFixed(2);
      $('saturationv').textContent = s.saturation.toFixed(2);
      $('huerangev').textContent = s.hueRange + '°';
      $('durv').textContent = s.duration.toFixed(2) + 's';
      $('customwrap').classList.toggle('visible', s.variant === 'custom');

      out.textContent = generateOutput(s);
    }

    /* rAF angle driver for the preview */
    let rafId = null;
    function startDriver() {
      if (rafId) return;
      const loop = (t) => {
        const deg = ((t / (state.duration * 1000)) % 1) * 360;
        card.style.setProperty('--beam-angle', deg.toFixed(2) + 'deg');
        rafId = requestAnimationFrame(loop);
      };
      rafId = requestAnimationFrame(loop);
    }
    function stopDriver() {
      if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
    }

    /* ---------------- controls ---------------- */

    const variantNames = ['colorful', 'mono', 'ocean', 'sunset', 'custom'];
    const variantWrap = $('variants');
    variantNames.forEach((v) => {
      const b = document.createElement('button');
      b.className = 'toggle-btn' + (v === state.variant ? ' active' : '');
      b.type = 'button';
      b.textContent = v.charAt(0).toUpperCase() + v.slice(1);
      b.dataset.variant = v;
      b.addEventListener('click', () => {
        state.variant = v;
        variantWrap.querySelectorAll('.toggle-btn').forEach((x) =>
          x.classList.toggle('active', x.dataset.variant === v));
        render();
      });
      variantWrap.appendChild(b);
    });

    function setTheme(theme) {
      state.theme = theme;
      if (!state.satTouched) {
        state.saturation = themePresets[theme].saturation;
        $('saturation').value = state.saturation;
      }
      if (theme === 'light') {
        state.cardbg = '#ffffff'; state.pagebg = '#f2f2f4';
      } else {
        state.cardbg = '#1d1d1f'; state.pagebg = '#0f0f10';
      }
      $('cardbg').value = state.cardbg;
      $('pagebg').value = state.pagebg;
      $('theme-dark').classList.toggle('active', theme === 'dark');
      $('theme-light').classList.toggle('active', theme === 'light');
      render();
    }
    $('theme-dark').addEventListener('click', () => setTheme('dark'));
    $('theme-light').addEventListener('click', () => setTheme('light'));

    [['cc1', 'cc1'], ['cc2', 'cc2'], ['cardbg', 'cardbg'], ['pagebg', 'pagebg']].forEach(([id, key]) => {
      $(id).addEventListener('input', (e) => { state[key] = e.target.value; render(); });
    });

    [['rad', 'radius', parseInt], ['bw', 'borderWidth', parseInt],
    ['strength', 'strength', parseFloat], ['brightness', 'brightness', parseFloat],
    ['huerange', 'hueRange', parseInt], ['dur', 'duration', parseFloat]].forEach(([id, key, cast]) => {
      $(id).addEventListener('input', (e) => { state[key] = cast(e.target.value); render(); });
    });

    $('saturation').addEventListener('input', (e) => {
      state.saturation = parseFloat(e.target.value);
      state.satTouched = true;
      render();
    });

    $('hueshift').addEventListener('click', () => {
      state.hueShift = !state.hueShift;
      $('hueshift').textContent = state.hueShift ? 'Animated' : 'Static';
      $('hueshift').classList.toggle('active', state.hueShift);
      render();
    });

    $('play').addEventListener('click', () => {
      state.playing = !state.playing;
      $('play').textContent = state.playing ? 'Playing' : 'Paused';
      $('play').classList.toggle('active', state.playing);
      if (state.playing) startDriver(); else stopDriver();
    });

    $('copy').addEventListener('click', async () => {
      const btn = $('copy');
      try {
        await navigator.clipboard.writeText(out.textContent);
        btn.textContent = 'Copied';
        btn.classList.add('done');
      } catch (err) {
        const range = document.createRange();
        range.selectNodeContents(out);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        btn.textContent = 'Press ⌘C';
      }
      setTimeout(() => { btn.textContent = 'Copy'; btn.classList.remove('done'); }, 1600);
    });

    render();
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) startDriver();
  })();
</script>

<?php require '../includes/footer.php'; ?>
