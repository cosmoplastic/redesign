<?php
$pageTitle = 'Border Glow — ONE design';
$pageDescription = 'A live configurator for animated glowing card borders — tune the palette, geometry, and motion, then copy the generated CSS.';
$activePage = 'border-glow';
$shellClass = 'full-height';
require '../includes/header.php';
?>

<style>
  /* Opt out of the site-wide inherited uppercase; explicit labels below re-apply it. */
  .border-glow,
  .border-glow .bg-pre {
    text-transform: none;
  }

  .border-glow .topstrip-title {
    text-transform: uppercase;
  }

  /* ── Right area: all three beam types float in the surface, stacked ── */
  .border-glow .bg-stage {
    flex: 1;
    min-height: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 44px 32px;
    overflow: auto;
    transition: background 0.25s ease;
  }

  .border-glow #preview-slot {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: clamp(28px, 7.8vw, 80px);
    width: 100%;
  }

  /* Each element's background / radius / overflow come from the generated beam CSS */
  .border-glow .bg-el {
    position: relative;
    flex-shrink: 0;
  }

  .border-glow .bg-el .content {
    position: relative;
    z-index: 0;
  }

  .border-glow .bg-el--card {
    width: min(360px, 100%);
  }

  .border-glow .bg-el--card .content {
    padding: 30px 28px;
  }

  .border-glow .bg-el--card .content h3 {
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 300;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
  }

  .border-glow .bg-el--card .content p {
    font-family: var(--mono);
    font-size: 12.5px;
    line-height: 1.6;
  }

  .border-glow .bg-el--btn {
    width: 128px;
    height: 40px;
  }

  .border-glow .bg-el--btn .content {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--mono);
    font-size: 12px;
    letter-spacing: 0.02em;
  }

  .border-glow .bg-el--search {
    width: min(440px, 100%);
    height: 48px;
  }

  .border-glow .bg-el--search .content {
    height: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 16px;
    font-family: var(--mono);
    font-size: 13px;
  }

  .border-glow .bg-el--search svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke-width: 2;
    flex-shrink: 0;
  }

  /* ── Control rows (left panel) ── */
  .border-glow .bg-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
  }

  .border-glow .bg-row:last-child {
    margin-bottom: 0;
  }

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

  .border-glow .bg-toggles {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
  }

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

  .border-glow .toggle-btn:hover {
    color: var(--color-text-100);
    border-color: var(--border2);
  }

  .border-glow .toggle-btn.active {
    color: var(--color-text-50);
    border-color: var(--border3);
    background: var(--bg4);
  }

  .border-glow input[type="color"] {
    -webkit-appearance: none;
    appearance: none;
    width: 34px;
    height: 26px;
    border: 1px solid var(--border2);
    border-radius: var(--r-sm);
    background: transparent;
    padding: 2px;
    cursor: pointer;
    flex-shrink: 0;
  }

  .border-glow input[type="color"]::-webkit-color-swatch-wrapper {
    padding: 0;
  }

  .border-glow input[type="color"]::-webkit-color-swatch {
    border: none;
    border-radius: 4px;
  }

  .border-glow input[type="color"]::-moz-color-swatch {
    border: none;
    border-radius: 4px;
  }

  .border-glow .custom-colors {
    display: none;
  }

  .border-glow .custom-colors.visible {
    display: block;
  }

  /* ── Generated code ── */
  .border-glow .bg-code-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
  }

  .border-glow .bg-code-head span {
    font-family: var(--mono);
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--color-text-400);
  }

  .border-glow .copy-btn {
    font-family: var(--mono);
    font-size: 11px;
    color: var(--color-text-300);
    background: var(--bg3);
    border: 1px solid var(--border2);
    border-radius: var(--r-sm);
    padding: 6px 14px;
    cursor: pointer;
    transition: border-color .13s, color .13s;
  }

  .border-glow .copy-btn:hover {
    border-color: var(--border3);
    color: var(--color-text-100);
  }

  .border-glow .copy-btn.done {
    color: var(--green);
    border-color: var(--green-border);
  }

  .border-glow .bg-export-pick {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 13px 22px;
    border-bottom: 1px solid var(--border);
    font-family: var(--mono);
    font-size: 12px;
    flex-wrap: wrap;
  }

  .border-glow .bg-export-pick-label {
    color: var(--color-text-400);
    text-transform: uppercase;
    letter-spacing: 0.09em;
    font-size: 10px;
  }

  .border-glow .bg-export-pick label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--color-text-200);
    cursor: pointer;
    user-select: none;
  }

  .border-glow .bg-export-pick input {
    accent-color: var(--color-text-100);
    width: 14px;
    height: 14px;
    margin: 0;
    cursor: pointer;
  }

  .border-glow .bg-pre {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 18px 20px;
    font-family: var(--mono);
    font-size: 12px;
    line-height: 1.7;
    color: #c9c5be;
    overflow-x: auto;
    tab-size: 2;
    white-space: pre;
    margin: 0;
  }
</style>

<main class="panel border-glow">

  <div class="topstrip">
    <div class="topstrip-head">
      <h1 class="topstrip-title">Border <em>Glow</em></h1>
      <p class="topstrip-intro">Create animated CSS border glow effects for cards, buttons, and inputs. Adjust palette, geometry, brightness, saturation, and motion, then export the finished effect as pure CSS.</p>
    </div>
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
          <label for="cardbg">Element</label>
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
          <input type="range" class="slider" id="rad" min="0" max="40" step="1" value="16">
          <span class="val" id="radv">16px</span>
        </div>
        <div class="bg-row">
          <label for="bw">Border width</label>
          <input type="range" class="slider" id="bw" min="1" max="4" step="1" value="1">
          <span class="val" id="bwv">1px</span>
        </div>
      </div>

      <div class="grad-section">
        <label class="field-label">Glow tuning</label>
        <div class="bg-row">
          <label for="strength">Strength</label>
          <input type="range" class="slider" id="strength" min="0" max="2" step="0.05" value="1">
          <span class="val" id="strengthv">1.00</span>
        </div>
        <div class="bg-row">
          <label for="brightness">Brightness</label>
          <input type="range" class="slider" id="brightness" min="0.5" max="2.5" step="0.05" value="1.3">
          <span class="val" id="brightnessv">1.30</span>
        </div>
        <div class="bg-row">
          <label for="saturation">Saturation</label>
          <input type="range" class="slider" id="saturation" min="0.5" max="2.5" step="0.05" value="1.2">
          <span class="val" id="saturationv">1.20</span>
        </div>
        <div class="bg-row">
          <label for="huerange">Hue range</label>
          <input type="range" class="slider" id="huerange" min="0" max="90" step="5" value="30">
          <span class="val" id="huerangev">30&deg;</span>
        </div>
      </div>

      <div class="grad-section">
        <label class="field-label">Motion</label>
        <div class="bg-row">
          <label for="dur">Duration</label>
          <input type="range" class="slider" id="dur" min="0.5" max="8" step="0.02" value="1.96">
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

    <!-- ── RIGHT: all three beam types (built by JS) ──── -->
    <div class="grad-main">
      <div class="bg-stage" id="stage">
        <div id="preview-slot"></div>
        <section class="tool-seo-section" aria-labelledby="glow-seo-title">
          <h2 id="glow-seo-title">Generate Animated CSS Border Glow Effects</h2>
          <p>This border glow generator helps you build animated highlight treatments for cards, buttons, and input fields without depending on a framework. You can control palette, border width, radius, brightness, saturation, and motion, then preview how the effect feels on different UI elements.</p>
          <p>It is useful when you need a CSS border glow effect for hero cards, premium interface components, or attention-grabbing callouts. If you want a cleaner way to create and export a conic-gradient border beam effect, this page turns that setup into a controllable visual workflow.</p>
        </section>
      </div>
    </div>

  </div>

  <!-- ── Export modal ──────────────────────────────── -->
  <div class="export-modal" id="export-modal">
    <div class="export-modal-backdrop" data-close></div>
    <div class="export-modal-box">
      <div class="export-modal-header">
        <span style="font-family:var(--mono);font-size:12px;color:var(--color-text-300)">Generated CSS + markup +
          driver</span>
        <div class="export-modal-actions">
          <button class="copy-btn" id="copy" type="button">Copy</button>
          <button class="export-modal-close" data-close aria-label="Close">&times;</button>
        </div>
      </div>
      <div class="bg-export-pick">
        <span class="bg-export-pick-label">Include</span>
        <label><input type="checkbox" id="pick-md" checked> Card</label>
        <label><input type="checkbox" id="pick-sm" checked> Button</label>
        <label><input type="checkbox" id="pick-line" checked> Search line</label>
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

    // md: 9 stationary radial ellipses around the perimeter (card ring)
    const mdPalettes = {
      colorful: [
        { c: [255, 50, 100], pos: '33% -7.4%', size: [70, 40] }, { c: [40, 140, 255], pos: '12% -5%', size: [60, 35] },
        { c: [50, 200, 80], pos: '2.1% 68.3%', size: [40, 70] }, { c: [30, 185, 170], pos: '2.1% 68.3%', size: [20, 35] },
        { c: [100, 70, 255], pos: '74.4% 100%', size: [180, 32] }, { c: [40, 140, 255], pos: '55% 100%', size: [85, 26] },
        { c: [255, 120, 40], pos: '93.9% 0%', size: [74, 32] }, { c: [240, 50, 180], pos: '100% 27.1%', size: [26, 42] },
        { c: [180, 40, 240], pos: '100% 27.1%', size: [52, 48] }
      ],
      mono: [
        { c: [180, 180, 180], pos: '33% -7.4%', size: [70, 40] }, { c: [140, 140, 140], pos: '12% -5%', size: [60, 35] },
        { c: [160, 160, 160], pos: '2.1% 68.3%', size: [40, 70] }, { c: [130, 130, 130], pos: '2.1% 68.3%', size: [20, 35] },
        { c: [170, 170, 170], pos: '74.4% 100%', size: [180, 32] }, { c: [150, 150, 150], pos: '55% 100%', size: [85, 26] },
        { c: [190, 190, 190], pos: '93.9% 0%', size: [74, 32] }, { c: [145, 145, 145], pos: '100% 27.1%', size: [26, 42] },
        { c: [165, 165, 165], pos: '100% 27.1%', size: [52, 48] }
      ],
      ocean: [
        { c: [100, 80, 220], pos: '33% -7.4%', size: [70, 40] }, { c: [60, 120, 255], pos: '12% -5%', size: [60, 35] },
        { c: [80, 100, 200], pos: '2.1% 68.3%', size: [40, 70] }, { c: [50, 140, 220], pos: '2.1% 68.3%', size: [20, 35] },
        { c: [120, 80, 255], pos: '74.4% 100%', size: [180, 32] }, { c: [70, 130, 255], pos: '55% 100%', size: [85, 26] },
        { c: [140, 100, 240], pos: '93.9% 0%', size: [74, 32] }, { c: [90, 110, 230], pos: '100% 27.1%', size: [26, 42] },
        { c: [130, 70, 255], pos: '100% 27.1%', size: [52, 48] }
      ],
      sunset: [
        { c: [255, 80, 50], pos: '33% -7.4%', size: [70, 40] }, { c: [255, 160, 40], pos: '12% -5%', size: [60, 35] },
        { c: [255, 120, 60], pos: '2.1% 68.3%', size: [40, 70] }, { c: [255, 200, 50], pos: '2.1% 68.3%', size: [20, 35] },
        { c: [255, 100, 80], pos: '74.4% 100%', size: [180, 32] }, { c: [255, 180, 60], pos: '55% 100%', size: [85, 26] },
        { c: [255, 60, 60], pos: '93.9% 0%', size: [74, 32] }, { c: [255, 140, 50], pos: '100% 27.1%', size: [26, 42] },
        { c: [255, 90, 70], pos: '100% 27.1%', size: [52, 48] }
      ]
    };

    // sm: compact palettes for button-sized elements
    const smPalettes = {
      colorful: { border: [{ c: [50, 200, 80], pos: '2% 68%', size: [9, 18] }, { c: [30, 185, 170], pos: '2% 68%', size: [4, 8] }, { c: [255, 120, 40], pos: '72% -3%', size: [59, 9] }, { c: [100, 70, 255], pos: '74% 100%', size: [42, 7] }, { c: [240, 50, 180], pos: '100% 27%', size: [10, 17] }, { c: [180, 40, 240], pos: '100% 27%', size: [10, 18] }, { c: [40, 140, 255], pos: '100% 27%', size: [5, 10] }, { c: [255, 50, 100], pos: '100% 27%', size: [11, 12] }], innerA: [0.5, 0.45, 0.35, 0.35, 0.3, 0.4, 0.3, 0.3] },
      mono: { border: [{ c: [160, 160, 160], pos: '2% 68%', size: [9, 18] }, { c: [140, 140, 140], pos: '2% 68%', size: [4, 8] }, { c: [180, 180, 180], pos: '72% -3%', size: [59, 9] }, { c: [150, 150, 150], pos: '74% 100%', size: [42, 7] }, { c: [170, 170, 170], pos: '100% 27%', size: [10, 17] }, { c: [155, 155, 155], pos: '100% 27%', size: [10, 18] }, { c: [145, 145, 145], pos: '100% 27%', size: [5, 10] }, { c: [165, 165, 165], pos: '100% 27%', size: [11, 12] }], innerA: [0.25, 0.22, 0.17, 0.17, 0.15, 0.2, 0.15, 0.15] },
      ocean: { border: [{ c: [60, 140, 200], pos: '2% 68%', size: [9, 18] }, { c: [50, 120, 180], pos: '2% 68%', size: [4, 8] }, { c: [100, 80, 220], pos: '72% -3%', size: [59, 9] }, { c: [80, 100, 255], pos: '74% 100%', size: [42, 7] }, { c: [120, 70, 240], pos: '100% 27%', size: [10, 17] }, { c: [90, 80, 220], pos: '100% 27%', size: [10, 18] }, { c: [70, 110, 255], pos: '100% 27%', size: [5, 10] }, { c: [110, 90, 230], pos: '100% 27%', size: [11, 12] }], innerA: [0.5, 0.45, 0.35, 0.35, 0.3, 0.4, 0.3, 0.3] },
      sunset: { border: [{ c: [255, 180, 50], pos: '2% 68%', size: [9, 18] }, { c: [255, 150, 40], pos: '2% 68%', size: [4, 8] }, { c: [255, 80, 60], pos: '72% -3%', size: [59, 9] }, { c: [255, 100, 80], pos: '74% 100%', size: [42, 7] }, { c: [255, 60, 80], pos: '100% 27%', size: [10, 17] }, { c: [255, 120, 60], pos: '100% 27%', size: [10, 18] }, { c: [255, 200, 50], pos: '100% 27%', size: [5, 10] }, { c: [255, 90, 70], pos: '100% 27%', size: [11, 12] }], innerA: [0.5, 0.45, 0.35, 0.35, 0.3, 0.4, 0.3, 0.3] }
    };

    // line: traveling blobs (dark/light geometry differs)
    const lineBorder = {
      colorful: { dark: [{ c: [255, 50, 100], w: 36, h: 36, ox: 0, oy: 2 }, { c: [40, 180, 220], w: 30, h: 32, ox: 39, oy: 0 }, { c: [50, 200, 80], w: 33, h: 28, ox: -36, oy: 2 }, { c: [180, 40, 240], w: 29, h: 34, ox: -54, oy: 0 }, { c: [255, 160, 30], w: 27, h: 30, ox: 51, oy: -1 }, { c: [100, 70, 255], w: 36, h: 24, ox: 21, oy: 1 }, { c: [40, 140, 255], w: 30, h: 22, ox: -21, oy: 0 }, { c: [240, 50, 180], w: 25, h: 28, ox: 66, oy: 1 }, { c: [30, 185, 170], w: 23, h: 30, ox: -66, oy: -1 }], light: [{ c: [255, 50, 100], w: 45, h: 36, ox: 0, oy: 2 }, { c: [40, 140, 255], w: 35, h: 32, ox: 65, oy: 0 }, { c: [50, 200, 80], w: 40, h: 28, ox: -60, oy: 2 }, { c: [180, 40, 240], w: 35, h: 34, ox: -90, oy: 0 }, { c: [30, 185, 170], w: 38, h: 30, ox: 85, oy: -1 }, { c: [100, 70, 255], w: 50, h: 24, ox: 35, oy: 1 }, { c: [40, 140, 255], w: 40, h: 22, ox: -35, oy: 0 }, { c: [255, 120, 40], w: 35, h: 28, ox: 110, oy: 1 }, { c: [240, 50, 180], w: 30, h: 30, ox: -110, oy: -1 }] },
      mono: { dark: [{ c: [200, 200, 200], w: 36, h: 36, ox: 0, oy: 2 }, { c: [170, 170, 170], w: 30, h: 32, ox: 39, oy: 0 }, { c: [155, 155, 155], w: 33, h: 28, ox: -36, oy: 2 }, { c: [185, 185, 185], w: 29, h: 34, ox: -54, oy: 0 }, { c: [165, 165, 165], w: 27, h: 30, ox: 51, oy: -1 }, { c: [180, 180, 180], w: 36, h: 24, ox: 21, oy: 1 }, { c: [160, 160, 160], w: 30, h: 22, ox: -21, oy: 0 }, { c: [175, 175, 175], w: 25, h: 28, ox: 66, oy: 1 }, { c: [190, 190, 190], w: 23, h: 30, ox: -66, oy: -1 }], light: [{ c: [100, 100, 100], w: 45, h: 36, ox: 0, oy: 2 }, { c: [80, 80, 80], w: 35, h: 32, ox: 65, oy: 0 }, { c: [90, 90, 90], w: 40, h: 28, ox: -60, oy: 2 }, { c: [70, 70, 70], w: 35, h: 34, ox: -90, oy: 0 }, { c: [85, 85, 85], w: 38, h: 30, ox: 85, oy: -1 }, { c: [95, 95, 95], w: 50, h: 24, ox: 35, oy: 1 }, { c: [75, 75, 75], w: 40, h: 22, ox: -35, oy: 0 }, { c: [105, 105, 105], w: 35, h: 28, ox: 110, oy: 1 }, { c: [65, 65, 65], w: 30, h: 30, ox: -110, oy: -1 }] },
      ocean: { dark: [{ c: [100, 80, 220], w: 36, h: 36, ox: 0, oy: 2 }, { c: [60, 120, 255], w: 30, h: 32, ox: 39, oy: 0 }, { c: [80, 100, 200], w: 33, h: 28, ox: -36, oy: 2 }, { c: [130, 70, 255], w: 29, h: 34, ox: -54, oy: 0 }, { c: [70, 130, 255], w: 27, h: 30, ox: 51, oy: -1 }, { c: [120, 80, 255], w: 36, h: 24, ox: 21, oy: 1 }, { c: [90, 110, 230], w: 30, h: 22, ox: -21, oy: 0 }, { c: [110, 90, 240], w: 25, h: 28, ox: 66, oy: 1 }, { c: [140, 100, 255], w: 23, h: 30, ox: -66, oy: -1 }], light: [{ c: [80, 60, 200], w: 45, h: 36, ox: 0, oy: 2 }, { c: [50, 100, 220], w: 35, h: 32, ox: 65, oy: 0 }, { c: [70, 90, 190], w: 40, h: 28, ox: -60, oy: 2 }, { c: [110, 60, 220], w: 35, h: 34, ox: -90, oy: 0 }, { c: [60, 110, 230], w: 38, h: 30, ox: 85, oy: -1 }, { c: [100, 70, 240], w: 50, h: 24, ox: 35, oy: 1 }, { c: [80, 100, 210], w: 40, h: 22, ox: -35, oy: 0 }, { c: [90, 80, 225], w: 35, h: 28, ox: 110, oy: 1 }, { c: [120, 90, 245], w: 30, h: 30, ox: -110, oy: -1 }] },
      sunset: { dark: [{ c: [255, 100, 60], w: 36, h: 36, ox: 0, oy: 2 }, { c: [255, 180, 50], w: 30, h: 32, ox: 39, oy: 0 }, { c: [255, 140, 70], w: 33, h: 28, ox: -36, oy: 2 }, { c: [255, 80, 80], w: 29, h: 34, ox: -54, oy: 0 }, { c: [255, 200, 60], w: 27, h: 30, ox: 51, oy: -1 }, { c: [255, 120, 50], w: 36, h: 24, ox: 21, oy: 1 }, { c: [255, 160, 80], w: 30, h: 22, ox: -21, oy: 0 }, { c: [255, 90, 60], w: 25, h: 28, ox: 66, oy: 1 }, { c: [255, 70, 70], w: 23, h: 30, ox: -66, oy: -1 }], light: [{ c: [220, 80, 40], w: 45, h: 36, ox: 0, oy: 2 }, { c: [230, 150, 30], w: 35, h: 32, ox: 65, oy: 0 }, { c: [210, 110, 50], w: 40, h: 28, ox: -60, oy: 2 }, { c: [200, 60, 60], w: 35, h: 34, ox: -90, oy: 0 }, { c: [220, 170, 40], w: 38, h: 30, ox: 85, oy: -1 }, { c: [210, 100, 30], w: 50, h: 24, ox: 35, oy: 1 }, { c: [230, 130, 60], w: 40, h: 22, ox: -35, oy: 0 }, { c: [190, 70, 50], w: 35, h: 28, ox: 110, oy: 1 }, { c: [180, 50, 50], w: 30, h: 30, ox: -110, oy: -1 }] }
    };

    // line inner glow (theme-independent geometry, per-entry alpha)
    const lineInner = {
      colorful: [{ c: [255, 50, 100], a: 0.48, w: 33, h: 30, ox: 0, oy: 0 }, { c: [40, 180, 220], a: 0.42, w: 24, h: 26, ox: 39, oy: -3 }, { c: [50, 200, 80], a: 0.48, w: 27, h: 24, ox: -36, oy: 0 }, { c: [180, 40, 240], a: 0.42, w: 23, h: 28, ox: -54, oy: -2 }, { c: [255, 160, 30], a: 0.50, w: 24, h: 24, ox: 51, oy: -1 }, { c: [100, 70, 255], a: 0.45, w: 30, h: 20, ox: 21, oy: 0 }, { c: [40, 140, 255], a: 0.40, w: 25, h: 18, ox: -21, oy: -2 }, { c: [240, 50, 180], a: 0.45, w: 21, h: 24, ox: 66, oy: 0 }, { c: [30, 185, 170], a: 0.52, w: 18, h: 26, ox: -66, oy: -1 }],
      mono: [{ c: [200, 200, 200], a: 0.48, w: 33, h: 30, ox: 0, oy: 0 }, { c: [170, 170, 170], a: 0.42, w: 24, h: 26, ox: 39, oy: -3 }, { c: [155, 155, 155], a: 0.48, w: 27, h: 24, ox: -36, oy: 0 }, { c: [185, 185, 185], a: 0.42, w: 23, h: 28, ox: -54, oy: -2 }, { c: [165, 165, 165], a: 0.50, w: 24, h: 24, ox: 51, oy: -1 }, { c: [180, 180, 180], a: 0.45, w: 30, h: 20, ox: 21, oy: 0 }, { c: [160, 160, 160], a: 0.40, w: 25, h: 18, ox: -21, oy: -2 }, { c: [175, 175, 175], a: 0.45, w: 21, h: 24, ox: 66, oy: 0 }, { c: [190, 190, 190], a: 0.52, w: 18, h: 26, ox: -66, oy: -1 }],
      ocean: [{ c: [100, 80, 220], a: 0.48, w: 33, h: 30, ox: 0, oy: 0 }, { c: [60, 120, 255], a: 0.42, w: 24, h: 26, ox: 39, oy: -3 }, { c: [80, 100, 200], a: 0.48, w: 27, h: 24, ox: -36, oy: 0 }, { c: [130, 70, 255], a: 0.42, w: 23, h: 28, ox: -54, oy: -2 }, { c: [70, 130, 255], a: 0.50, w: 24, h: 24, ox: 51, oy: -1 }, { c: [120, 80, 255], a: 0.45, w: 30, h: 20, ox: 21, oy: 0 }, { c: [90, 110, 230], a: 0.40, w: 25, h: 18, ox: -21, oy: -2 }, { c: [110, 90, 240], a: 0.45, w: 21, h: 24, ox: 66, oy: 0 }, { c: [140, 100, 255], a: 0.52, w: 18, h: 26, ox: -66, oy: -1 }],
      sunset: [{ c: [255, 100, 60], a: 0.48, w: 33, h: 30, ox: 0, oy: 0 }, { c: [255, 180, 50], a: 0.42, w: 24, h: 26, ox: 39, oy: -3 }, { c: [255, 140, 70], a: 0.48, w: 27, h: 24, ox: -36, oy: 0 }, { c: [255, 80, 80], a: 0.42, w: 23, h: 28, ox: -54, oy: -2 }, { c: [255, 200, 60], a: 0.50, w: 24, h: 24, ox: 51, oy: -1 }, { c: [255, 120, 50], a: 0.45, w: 30, h: 20, ox: 21, oy: 0 }, { c: [255, 160, 80], a: 0.40, w: 25, h: 18, ox: -21, oy: -2 }, { c: [255, 90, 60], a: 0.45, w: 21, h: 24, ox: 66, oy: 0 }, { c: [255, 70, 70], a: 0.52, w: 18, h: 26, ox: -66, oy: -1 }]
    };

    // line bloom spike primaries + per-position spikes
    const lineSpikePrimaries = {
      colorful: { dark: [[255, 60, 80], [40, 190, 180, 0.98]], light: [[200, 30, 60], [20, 150, 140]] },
      mono: { dark: [[200, 200, 200], [170, 170, 170]], light: [[80, 80, 80], [120, 120, 120]] },
      ocean: { dark: [[100, 120, 255], [130, 100, 220, 0.98]], light: [[60, 60, 180], [80, 100, 200]] },
      sunset: { dark: [[255, 140, 80], [255, 100, 60, 0.98]], light: [[200, 80, 40], [220, 120, 30]] }
    };
    const lineBloomSpikes = {
      colorful: { dark: [[[100, 70, 255], [100, 70, 255, 1]], [[255, 170, 40, 0.59], [255, 170, 40, 0.29]], [[50, 200, 100], [50, 200, 100, 1]], [[200, 50, 240, 0.91], [200, 50, 240, 0.45]], [[40, 140, 255], [40, 140, 255, 1]]], light: [[[80, 50, 200], [80, 50, 200, 0.8]], [[210, 130, 0, 0.7], [210, 130, 0, 0.46]], [[30, 160, 70], [30, 160, 70, 0.82]], [[160, 30, 190], [160, 30, 190, 0.7]], [[30, 100, 200], [30, 100, 200, 0.78]]] },
      mono: { dark: [[[200, 200, 200], [200, 200, 200, 1]], [[180, 180, 180, 0.59], [180, 180, 180, 0.29]], [[190, 190, 190], [190, 190, 190, 1]], [[170, 170, 170, 0.91], [170, 170, 170, 0.45]], [[185, 185, 185], [185, 185, 185, 1]]], light: [[[80, 80, 80], [80, 80, 80, 0.8]], [[100, 100, 100, 0.7], [100, 100, 100, 0.46]], [[70, 70, 70], [70, 70, 70, 0.82]], [[90, 90, 90], [90, 90, 90, 0.7]], [[85, 85, 85], [85, 85, 85, 0.78]]] },
      ocean: { dark: [[[100, 80, 255], [100, 80, 255]], [[80, 130, 220, 0.59], [80, 130, 220, 0.29]], [[60, 100, 255], [60, 100, 255]], [[90, 120, 200, 0.91], [90, 120, 200, 0.45]], [[120, 90, 255], [120, 90, 255]]], light: [[[50, 40, 180], [50, 40, 180, 0.8]], [[40, 80, 200, 0.7], [40, 80, 200, 0.46]], [[30, 50, 190], [30, 50, 190, 0.82]], [[60, 90, 180], [60, 90, 180, 0.7]], [[70, 60, 200], [70, 60, 200, 0.78]]] },
      sunset: { dark: [[[255, 100, 80], [255, 100, 80]], [[255, 150, 80, 0.59], [255, 150, 80, 0.29]], [[255, 80, 60], [255, 80, 60]], [[255, 120, 50, 0.91], [255, 120, 50, 0.45]], [[255, 140, 70], [255, 140, 70]]], light: [[[200, 60, 30], [200, 60, 30, 0.8]], [[220, 100, 20, 0.7], [220, 100, 20, 0.46]], [[180, 40, 20], [180, 40, 20, 0.82]], [[210, 80, 10], [210, 80, 10, 0.7]], [[190, 70, 30], [190, 70, 30, 0.78]]] }
    };

    // sizeThemePresets — tuned per-preset layer opacities
    const themePresets = {
      md: { dark: { stroke: 0.26, inner: 0.42, bloom: 0.24, shadow: 'rgba(255, 255, 255, 0.27)', sat: 1.2 }, light: { stroke: 0.12, inner: 0.26, bloom: 0.34, shadow: 'rgba(0, 0, 0, 0.14)', sat: 1.5 } },
      sm: { dark: { stroke: 0.46, inner: 0.24, bloom: 0.38, shadow: 'rgba(255, 255, 255, 0.3)', sat: 1.2 }, light: { stroke: 0.12, inner: 0.3, bloom: 0.16, shadow: 'rgba(0, 0, 0, 0.14)', sat: 1.8 } },
      line: { dark: { stroke: 1.14, inner: 0.7, bloom: 0.8, shadow: 'rgba(255, 255, 255, 0.1)', sat: 1.2 }, light: { stroke: 0.16, inner: 0.32, bloom: 0.3, shadow: 'rgba(0, 0, 0, 0.14)', sat: 1.95 } }
    };

    // line animation keyframe tracks (verbatim from styles.ts)
    const TRACKS = {
      x: { stops: [[0, 0.06], [0.1, 0.15], [0.2, 0.25], [0.3, 0.35], [0.4, 0.44], [0.5, 0.5], [0.6, 0.56], [0.7, 0.65], [0.8, 0.75], [0.9, 0.85], [1, 0.94]], ease: 'linear', mult: 1 },
      w: { stops: [[0, 0.5], [0.1, 0.8], [0.2, 1.1], [0.3, 1.3], [0.4, 1.45], [0.5, 1.5], [0.6, 1.45], [0.7, 1.3], [0.8, 1.1], [0.9, 0.8], [1, 0.5]], ease: 'linear', mult: 1 },
      edge: { stops: [[0, 0], [0.125, 0], [0.325, 1], [0.675, 1], [0.875, 0], [1, 0]], ease: 'linear', mult: 1 },
      h: { stops: [[0, 0.8], [0.25, 1.25], [0.55, 0.85], [0.8, 1.3], [1, 0.8]], ease: 'inout', mult: 1.3 },
      spike: { stops: [[0, 0.8], [0.25, 1.3], [0.5, 0.9], [0.75, 1.4], [1, 0.8]], ease: 'inout', mult: 1.33 },
      spike2: { stops: [[0, 1.2], [0.25, 0.7], [0.5, 1.4], [0.75, 0.8], [1, 1.2]], ease: 'inout', mult: 1.7 }
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

    function hexToRgb(hex) { return [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16)); }
    function mixRgb(a, b, t) { return a.map((v, i) => Math.round(v + (b[i] - v) * t)); }
    function rgb(c) { return 'rgb(' + c[0] + ', ' + c[1] + ', ' + c[2] + ')'; }
    function rgba(c, a) { return 'rgba(' + c[0] + ', ' + c[1] + ', ' + c[2] + ', ' + a + ')'; }
    function colStr(c) { return c.length === 4 ? rgba(c, c[3]) : rgb(c); }

    // Custom variant: user's two colors distributed over each preset's
    // base positions with varied mix fractions for depth.
    const customMixes = [0, 0.3, 0.6, 0.45, 0.9, 0.2, 0.75, 1, 0.55];

    function customize(list, s, keepAlpha) {
      const a = hexToRgb(s.cc1), b = hexToRgb(s.cc2);
      return list.map((e, i) => {
        const out = Object.assign({}, e);
        out.c = mixRgb(a, b, customMixes[i % customMixes.length]);
        if (!keepAlpha) delete out.a;
        return out;
      });
    }

    function getMdPalette(s) {
      return s.variant === 'custom' ? customize(mdPalettes.colorful, s, false) : mdPalettes[s.variant];
    }
    function getSmPalette(s) {
      if (s.variant !== 'custom') return smPalettes[s.variant];
      const base = smPalettes.colorful;
      return { border: customize(base.border, s, false), innerA: base.innerA };
    }
    function getLineBorder(s, isDark) {
      const key = isDark ? 'dark' : 'light';
      return s.variant === 'custom' ? customize(lineBorder.colorful[key], s, false) : lineBorder[s.variant][key];
    }
    function getLineInner(s) {
      return s.variant === 'custom' ? customize(lineInner.colorful, s, true) : lineInner[s.variant];
    }
    function getSpikeData(s, isDark) {
      const key = isDark ? 'dark' : 'light';
      if (s.variant !== 'custom') {
        return { primaries: lineSpikePrimaries[s.variant][key], spikes: lineBloomSpikes[s.variant][key] };
      }
      const a = hexToRgb(s.cc1), b = hexToRgb(s.cc2);
      const base = lineBloomSpikes.colorful[key];
      const fr = [0.5, 0.15, 0.85, 0.3, 0.7];
      return {
        primaries: [a, b.concat(isDark ? [0.98] : [])],
        spikes: base.map((pair, i) => pair.map((col) => {
          const m = mixRgb(a, b, fr[i]);
          return col.length === 4 ? m.concat([col[3]]) : m;
        }))
      };
    }

    /* ================================================================
       Shared gradient fragments — mirror styles.ts, with the @property
       spin replaced by a rAF-driven --beam-angle (rotate presets) or
       --beam-* tracks (line preset) so it animates in every browser.
       ================================================================ */

    function whiteConic(isDark) {
      const c = isDark
        ? ['rgba(255, 255, 255, 0.1)', 'rgba(255, 255, 255, 0.3)', 'rgba(255, 255, 255, 0.6)', 'rgba(255, 255, 255, 0.75)']
        : ['rgba(0, 0, 0, 0.08)', 'rgba(0, 0, 0, 0.2)', 'rgba(0, 0, 0, 0.4)', 'rgba(0, 0, 0, 0.55)'];
      return 'conic-gradient(\n      from var(--beam-angle, 0deg),\n      transparent 0%, transparent 54%,\n      ' + c[0] + ' 57%, ' + c[1] + ' 60%, ' + c[2] + ' 63%, ' + c[3] + ' 66%,\n      ' + c[2] + ' 69%, ' + c[1] + ' 72%, ' + c[0] + ' 75%,\n      transparent 78%, transparent 100%\n    )';
    }

    function windowConic() {
      return 'conic-gradient(\n      from var(--beam-angle, 0deg),\n      transparent 0%, transparent 30%,\n      rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,\n      white 52%, white 80%,\n      rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,\n      transparent 95%, transparent 100%\n    )';
    }

    function smallWindowConic() {
      return 'conic-gradient(\n      from var(--beam-angle, 0deg),\n      transparent 0%, transparent 22%,\n      rgba(255, 255, 255, 0.12) 28%, rgba(255, 255, 255, 0.4) 36%,\n      white 46%, white 82%,\n      rgba(255, 255, 255, 0.4) 88%, rgba(255, 255, 255, 0.12) 94%,\n      transparent 97%, transparent 100%\n    )';
    }

    function bloomConic(isDark) {
      const p = isDark ? '255, 255, 255' : '0, 0, 0';
      const peak = isDark ? '0.85' : '0.6';
      return 'conic-gradient(\n      from var(--beam-angle, 0deg),\n      transparent 0%, transparent 58%,\n      rgba(' + p + ', 0.03) 62%, rgba(' + p + ', 0.08) 65%,\n      rgba(' + p + ', 0.2) 67%, rgba(' + p + ', 0.45) 69%,\n      rgba(' + p + ', ' + peak + ') 70%, rgba(' + p + ', ' + peak + ') 70.5%,\n      rgba(' + p + ', 0.45) 71.5%, rgba(' + p + ', 0.2) 73%,\n      rgba(' + p + ', 0.08) 75%, rgba(' + p + ', 0.03) 78%,\n      transparent 82%\n    )';
    }

    function ringMask(webkit) {
      const prop = webkit ? '-webkit-mask' : 'mask';
      const comp = webkit ? '-webkit-mask-composite: xor;' : 'mask-composite: exclude;';
      return prop + ': linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);\n  ' + comp;
    }

    function hueBlock(s) {
      const br = s.brightness.toFixed(2);
      const sat = s.saturation.toFixed(2);
      if (!s.hueShift) return { anim: '\n  filter: brightness(' + br + ') saturate(' + sat + ');', frames: '' };
      return {
        anim: '\n  animation: beam-hue-shift 12s ease-in-out infinite;',
        frames: '\n@keyframes beam-hue-shift {\n' +
          '  0%   { filter: hue-rotate(-' + s.hueRange + 'deg) brightness(' + br + ') saturate(' + sat + '); }\n' +
          '  50%  { filter: hue-rotate(' + s.hueRange + 'deg) brightness(' + br + ') saturate(' + sat + '); }\n' +
          '  100% { filter: hue-rotate(-' + s.hueRange + 'deg) brightness(' + br + ') saturate(' + sat + '); }\n' +
          '}\n'
      };
    }

    function generateRotateCSS(s, isSmall) {
      const isDark = s.theme === 'dark';
      const preset = themePresets[isSmall ? 'sm' : 'md'][s.theme];
      const monoMul = s.variant === 'mono' ? 0.5 : 1;
      const stroke = (preset.stroke * monoMul * s.strength).toFixed(3);
      const inner = (preset.inner * monoMul * s.strength).toFixed(3);
      const bloom = (preset.bloom * monoMul * s.strength).toFixed(3);
      const innerR = Math.max(0, s.radius - s.borderWidth);
      const hue = hueBlock(s);
      const br = s.brightness.toFixed(2), sat = s.saturation.toFixed(2);

      let colorField, innerField, innerMaskW, innerMaskS, shadowSize;
      if (isSmall) {
        const p = getSmPalette(s);
        colorField = p.border.map((e) =>
          'radial-gradient(ellipse ' + e.size[0] + 'px ' + e.size[1] + 'px at ' + e.pos + ', ' + rgb(e.c) + ', transparent)'
        ).join(',\n    ');
        innerField = p.border.map((e, i) =>
          'radial-gradient(ellipse ' + e.size[0] + 'px ' + e.size[1] + 'px at ' + e.pos + ', ' + rgba(e.c, p.innerA[i]) + ', transparent)'
        ).join(',\n    ');
        innerMaskW = '-webkit-mask-image: ' + smallWindowConic() + ';\n  -webkit-mask-composite: source-over;';
        innerMaskS = 'mask-image: ' + smallWindowConic() + ';\n  mask-composite: add;';
        shadowSize = '5px';
      } else {
        const p = getMdPalette(s);
        const isMono = s.variant === 'mono';
        colorField = p.map((e) =>
          'radial-gradient(ellipse ' + e.size[0] + 'px ' + e.size[1] + 'px at ' + e.pos + ', ' + rgb(e.c) + ', transparent)'
        ).join(',\n    ');
        innerField = p.map((e) =>
          'radial-gradient(ellipse ' + Math.round(e.size[0] * 0.9) + 'px ' + Math.round(e.size[1] * 0.9) + 'px at ' + e.pos +
          ', ' + rgba(e.c, isMono ? 0.225 : 0.45) + ', transparent)'
        ).join(',\n    ');
        const edgeFades = ',\n    linear-gradient(white, transparent 28px, transparent calc(100% - 28px), white),\n    linear-gradient(to right, white, transparent 28px, transparent calc(100% - 28px), white)';
        innerMaskW = '-webkit-mask-image:\n    ' + windowConic() + edgeFades + ';\n  -webkit-mask-composite: source-in, source-over;';
        innerMaskS = 'mask-image:\n    ' + windowConic() + edgeFades + ';\n  mask-composite: intersect, add;';
        shadowSize = '9px';
      }

      return '.beam-card {\n' +
        '  position: relative;\n' +
        '  border-radius: ' + s.radius + 'px;\n' +
        '  background: ' + s.cardbg + ';\n' +
        '  overflow: hidden;\n' +
        '}\n\n' +
        '/* Stroke — stationary color field revealed by a rotating conic window */\n' +
        '.beam-card::after {\n' +
        '  content: "";\n  position: absolute;\n  inset: 0;\n' +
        '  border-radius: ' + innerR + 'px;\n  padding: ' + s.borderWidth + 'px;\n' +
        '  clip-path: inset(0 round ' + s.radius + 'px);\n' +
        '  background:\n    ' + whiteConic(isDark) + ',\n    ' + colorField + ';\n' +
        '  -webkit-mask:\n    ' + windowConic() + ',\n    linear-gradient(#fff 0 0) content-box,\n    linear-gradient(#fff 0 0);\n' +
        '  -webkit-mask-composite: source-in, xor;\n' +
        '  mask:\n    ' + windowConic() + ',\n    linear-gradient(#fff 0 0) content-box,\n    linear-gradient(#fff 0 0);\n' +
        '  mask-composite: intersect, exclude;\n' +
        '  pointer-events: none;\n  z-index: 2;\n  opacity: ' + stroke + ';' + hue.anim + '\n}\n\n' +
        '/* Inner glow — soft color bleed inside the element */\n' +
        '.beam-card::before {\n' +
        '  content: "";\n  position: absolute;\n  inset: 0;\n' +
        '  border-radius: ' + s.radius + 'px;\n' +
        '  clip-path: inset(0 round ' + s.radius + 'px);\n' +
        '  background:\n    ' + innerField + ';\n' +
        '  box-shadow: inset 0 0 ' + shadowSize + ' 1px ' + preset.shadow + ';\n' +
        '  ' + innerMaskW + '\n  ' + innerMaskS + '\n' +
        '  pointer-events: none;\n  z-index: 1;\n  opacity: ' + inner + ';' + hue.anim + '\n}\n\n' +
        '/* Bloom — blurred highlight ring riding the beam head */\n' +
        '.beam-card .beam-bloom {\n' +
        '  position: absolute;\n  inset: 0;\n' +
        '  border-radius: ' + innerR + 'px;\n' +
        '  clip-path: inset(0 round ' + s.radius + 'px);\n' +
        '  background: ' + bloomConic(isDark) + ';\n' +
        '  ' + ringMask(true) + '\n  ' + ringMask(false) + '\n' +
        '  padding: ' + s.borderWidth + 'px;\n' +
        '  filter: blur(8px) brightness(' + br + ') saturate(' + sat + ');\n' +
        '  pointer-events: none;\n  z-index: 3;\n  opacity: ' + bloom + ';\n}\n' + hue.frames;
    }

    function generateLineCSS(s) {
      const isDark = s.theme === 'dark';
      const preset = themePresets.line[s.theme];
      const stroke = (preset.stroke * s.strength).toFixed(3);
      const inner = (preset.inner * s.strength).toFixed(3);
      const bloom = (preset.bloom * s.strength).toFixed(3);
      const innerR = Math.max(0, s.radius - s.borderWidth);
      const br = s.brightness.toFixed(2), sat = s.saturation.toFixed(2);
      const isMono = s.variant === 'mono';

      function off(v) {
        if (v === 0) return '';
        return v > 0 ? ' + ' + v + 'px' : ' - ' + Math.abs(v) + 'px';
      }

      // traveling colored blobs (::after background)
      const borderBlobs = getLineBorder(s, isDark).map((e) =>
        'radial-gradient(ellipse calc(' + e.w + 'px * var(--beam-w, 1)) calc(' + e.h + 'px * var(--beam-h, 1)) at calc(var(--beam-x, 0.5) * 100%' + off(e.ox) + ') calc(100%' + off(e.oy) + '), ' + rgb(e.c) + ', transparent)'
      ).join(',\n    ');

      const whiteHead = isDark
        ? 'radial-gradient(ellipse calc(24px * var(--beam-w, 1)) calc(28px * var(--beam-h, 1)) at calc(var(--beam-x, 0.5) * 100%) calc(100% + 2px), rgba(255, 255, 255, 0.38) 0%, rgba(255, 255, 255, 0.12) 30%, transparent 65%)'
        : 'radial-gradient(ellipse calc(35px * var(--beam-w, 1)) calc(28px * var(--beam-h, 1)) at calc(var(--beam-x, 0.5) * 100%) calc(100% + 2px), rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.25) 35%, transparent 70%)';

      // inner glow blobs (::before background)
      const innerBlobs = getLineInner(s).map((e) =>
        'radial-gradient(ellipse calc(' + e.w + 'px * var(--beam-w, 1)) calc(' + e.h + 'px * var(--beam-h, 1)) at calc(var(--beam-x, 0.5) * 100%' + off(e.ox) + ') calc(100%' + (e.oy === 0 ? '' : ' - ' + Math.abs(e.oy) + 'px') + '), ' + rgba(e.c, e.a) + ', transparent)'
      ).join(',\n    ');

      // traveling reveal mask, shared by stroke and inner
      const travelMask = 'radial-gradient(\n      ellipse calc(78px * var(--beam-w, 1)) calc(60px * var(--beam-h, 1)) at calc(var(--beam-x, 0.5) * 100%) 100%,\n      white 0%, rgba(255, 255, 255, 0.5) 45%, transparent 100%\n    )';

      // bloom: fixed-position vertical spikes + traveling head
      const sd = getSpikeData(s, isDark);
      const p1 = sd.primaries[0], p2 = sd.primaries[1];
      const sc1 = isMono ? rgba(p1, 0.14) : colStr(p1);
      const sc2 = isMono ? rgba(p2, 0.12) : colStr(p2);
      const spikes = sd.spikes.map((pair) => isMono
        ? [rgba(pair[0], (pair[0][3] || 1) * 0.14), rgba(pair[1], (pair[1][3] || 1) * 0.098)]
        : [colStr(pair[0]), colStr(pair[1])]);

      const tw = isMono
        ? { w1: '12px', w2: '14px', w3: '12px', w4: '10px', lw: '12px', h1: '42px', h2: '38px', h3: '40px', h4: '32px' }
        : { w1: '0.8px', w2: '2px', w3: '1.2px', w4: '0.6px', lw: '1px', h1: '92px', h2: '72px', h3: '85px', h4: '60px' };

      let bloomBg;
      if (isDark) {
        const sc1mid = isMono ? rgba(p1, 0.09) : colStr(p1);
        const sc2mid = isMono ? rgba(p2, 0.06) : rgba(p2, 0.49);
        const dC = isMono ? 'rgba(255, 255, 255, 0.5)' : 'rgba(255, 255, 255, 1)';
        const d20 = isMono ? 'rgba(255, 255, 255, 0.45)' : 'rgba(255, 255, 255, 0.9)';
        const d50 = isMono ? 'rgba(255, 255, 255, 0.25)' : 'rgba(255, 255, 255, 0.5)';
        const aC = isMono ? 'rgba(255, 255, 255, 0.15)' : 'rgba(255, 255, 255, 0.3)';
        const a25 = isMono ? 'rgba(255, 255, 255, 0.06)' : 'rgba(255, 255, 255, 0.12)';
        const a55 = isMono ? 'rgba(255, 255, 255, 0.015)' : 'rgba(255, 255, 255, 0.03)';
        bloomBg =
          'radial-gradient(ellipse calc(' + tw.w1 + ' * var(--beam-spike, 1)) calc(' + tw.h1 + ' * var(--beam-h, 1)) at 8% calc(100% - 2px), ' + sc1 + ', ' + sc1mid + ' 30%, transparent 88%),\n' +
          '    radial-gradient(ellipse calc(10px * var(--beam-spike2, 1)) calc(35px * var(--beam-h, 1)) at 22% calc(100% - 4px), ' + sc2 + ', ' + sc2mid + ' 50%, transparent 95%),\n' +
          '    radial-gradient(ellipse calc(' + tw.w2 + ' * (2 - var(--beam-spike, 1))) calc(' + tw.h2 + ' * var(--beam-h, 1)) at 36% calc(100% - 3px), ' + spikes[0][0] + ', ' + spikes[0][1] + ' 40%, transparent 90%),\n' +
          '    radial-gradient(ellipse calc(14px * var(--beam-spike2, 1)) calc(28px * var(--beam-h, 1)) at 50% calc(100% - 2px), ' + spikes[1][0] + ', ' + spikes[1][1] + ' 55%, transparent 96%),\n' +
          '    radial-gradient(ellipse calc(' + tw.w3 + ' * (2 - var(--beam-spike2, 1))) calc(' + tw.h3 + ' * var(--beam-h, 1)) at 64% calc(100% - 4px), ' + spikes[2][0] + ', ' + spikes[2][1] + ' 35%, transparent 89%),\n' +
          '    radial-gradient(ellipse calc(7px * var(--beam-spike, 1)) calc(45px * var(--beam-h, 1)) at 78% calc(100% - 2px), ' + spikes[3][0] + ', ' + spikes[3][1] + ' 48%, transparent 94%),\n' +
          '    radial-gradient(ellipse calc(' + tw.w4 + ' * (2 - var(--beam-spike, 1))) calc(' + tw.h4 + ' * var(--beam-h, 1)) at 92% calc(100% - 3px), ' + spikes[4][0] + ', ' + spikes[4][1] + ' 42%, transparent 91%),\n' +
          '    radial-gradient(ellipse calc(21px * var(--beam-spike, 1)) calc(15px * var(--beam-spike2, 1)) at calc(var(--beam-x, 0.5) * 100%) calc(100% + 1px), ' + dC + ' 0%, ' + d20 + ' 20%, ' + d50 + ' 50%, transparent 100%),\n' +
          '    radial-gradient(ellipse calc(42px * var(--beam-w, 1)) calc(40px * var(--beam-h, 1)) at calc(var(--beam-x, 0.5) * 100%) 100%, ' + aC + ' 0%, ' + a25 + ' 25%, ' + a55 + ' 55%, transparent 80%)';
      } else {
        const sc1lt = isMono ? rgba(p1, 0.11) : rgba(p1, 0.85);
        const sc2lt = isMono ? rgba(p2, 0.09) : rgba(p2, 0.7);
        bloomBg =
          'radial-gradient(ellipse calc(' + tw.w1 + ' * var(--beam-spike, 1)) calc(' + tw.h1 + ' * var(--beam-h, 1)) at 8% calc(100% - 2px), ' + sc1 + ', ' + sc1lt + ' 30%, transparent 88%),\n' +
          '    radial-gradient(ellipse calc(10px * var(--beam-spike2, 1)) calc(35px * var(--beam-h, 1)) at 22% calc(100% - 4px), ' + sc2 + ', ' + sc2lt + ' 50%, transparent 95%),\n' +
          '    radial-gradient(ellipse calc(' + tw.w2 + ' * (2 - var(--beam-spike, 1))) calc(' + tw.h2 + ' * var(--beam-h, 1)) at 36% calc(100% - 3px), ' + spikes[0][0] + ', ' + spikes[0][1] + ' 40%, transparent 90%),\n' +
          '    radial-gradient(ellipse calc(14px * var(--beam-spike2, 1)) calc(28px * var(--beam-h, 1)) at 50% calc(100% - 2px), ' + spikes[1][0] + ', ' + spikes[1][1] + ' 55%, transparent 96%),\n' +
          '    radial-gradient(ellipse calc(' + tw.w3 + ' * (2 - var(--beam-spike2, 1))) calc(' + tw.h3 + ' * var(--beam-h, 1)) at 64% calc(100% - 4px), ' + spikes[2][0] + ', ' + spikes[2][1] + ' 35%, transparent 89%),\n' +
          '    radial-gradient(ellipse calc(7px * var(--beam-spike, 1)) calc(45px * var(--beam-h, 1)) at 78% calc(100% - 2px), ' + spikes[3][0] + ', ' + spikes[3][1] + ' 48%, transparent 94%),\n' +
          '    radial-gradient(ellipse calc(' + tw.lw + ' * (2 - var(--beam-spike, 1))) calc(' + tw.h4 + ' * var(--beam-h, 1)) at 92% calc(100% - 3px), ' + spikes[4][0] + ', ' + spikes[4][1] + ' 42%, transparent 91%),\n' +
          '    radial-gradient(ellipse calc(50px * var(--beam-w, 1)) calc(32px * var(--beam-h, 1)) at calc(var(--beam-x, 0.5) * 100%) calc(100%), rgba(0, 0, 0, 0.5) 0%, rgba(0, 0, 0, 0.18) 30%, rgba(0, 0, 0, 0.03) 60%, transparent 85%)';
      }

      const hue = hueBlock(s);
      const bloomFilter = isMono ? 'blur(6px)' : 'blur(8px)';
      const bloomHue = s.hueShift
        ? '\n  animation: beam-hue-shift-bloom 8s ease-in-out infinite;'
        : '\n  filter: ' + bloomFilter + ' brightness(' + br + ') saturate(' + sat + ');';
      const bloomFrames = s.hueShift
        ? '\n@keyframes beam-hue-shift-bloom {\n' +
        '  0%   { filter: ' + bloomFilter + ' hue-rotate(-' + (s.hueRange + 10) + 'deg) brightness(' + br + ') saturate(' + sat + '); }\n' +
        '  50%  { filter: ' + bloomFilter + ' hue-rotate(' + (s.hueRange + 10) + 'deg) brightness(' + br + ') saturate(' + sat + '); }\n' +
        '  100% { filter: ' + bloomFilter + ' hue-rotate(-' + (s.hueRange + 10) + 'deg) brightness(' + br + ') saturate(' + sat + '); }\n' +
        '}\n'
        : '';

      const edgeFades = ',\n    linear-gradient(white, transparent 28px, transparent calc(100% - 28px), white),\n    linear-gradient(to right, white, transparent 28px, transparent calc(100% - 28px), white)';

      return '.beam-card {\n' +
        '  position: relative;\n' +
        '  border-radius: ' + s.radius + 'px;\n' +
        '  background: ' + s.cardbg + ';\n' +
        '  overflow: hidden;\n' +
        '}\n\n' +
        '/* Stroke — traveling color blobs on the bottom border */\n' +
        '.beam-card::after {\n' +
        '  content: "";\n  position: absolute;\n  inset: 0;\n' +
        '  border-radius: ' + innerR + 'px;\n  padding: ' + s.borderWidth + 'px;\n' +
        '  clip-path: inset(0 round ' + s.radius + 'px);\n' +
        '  background:\n    ' + whiteHead + ',\n    ' + borderBlobs + ';\n' +
        '  -webkit-mask:\n    ' + travelMask + ',\n    linear-gradient(#fff 0 0) content-box,\n    linear-gradient(#fff 0 0);\n' +
        '  -webkit-mask-composite: source-in, xor;\n' +
        '  mask:\n    ' + travelMask + ',\n    linear-gradient(#fff 0 0) content-box,\n    linear-gradient(#fff 0 0);\n' +
        '  mask-composite: intersect, exclude;\n' +
        '  pointer-events: none;\n  z-index: 2;\n' +
        '  opacity: calc(var(--beam-edge, 1) * ' + stroke + ');' + hue.anim + '\n}\n\n' +
        '/* Inner glow — color bleed rising from the bottom edge */\n' +
        '.beam-card::before {\n' +
        '  content: "";\n  position: absolute;\n  inset: 0;\n' +
        '  border-radius: ' + s.radius + 'px;\n' +
        '  clip-path: inset(0 round ' + s.radius + 'px);\n' +
        '  background:\n    ' + innerBlobs + ';\n' +
        '  box-shadow: inset 0 0 9px 1px ' + preset.shadow + ';\n' +
        '  -webkit-mask-image:\n    ' + travelMask + edgeFades + ';\n' +
        '  -webkit-mask-composite: source-in, source-over;\n' +
        '  mask-image:\n    ' + travelMask + edgeFades + ';\n' +
        '  mask-composite: intersect, add;\n' +
        '  pointer-events: none;\n  z-index: 1;\n' +
        '  opacity: calc(var(--beam-edge, 1) * ' + inner + ');' + hue.anim + '\n}\n\n' +
        '/* Bloom — uneven vertical spikes + traveling head glow.\n' +
        '   Spike widths pulse on three desynchronized tracks, which is\n' +
        '   what makes the glow read as organic rather than uniform. */\n' +
        '.beam-card .beam-bloom {\n' +
        '  position: absolute;\n  inset: 0;\n' +
        '  border-radius: ' + innerR + 'px;\n' +
        '  clip-path: inset(0 round ' + s.radius + 'px);\n' +
        '  background:\n    ' + bloomBg + ';\n' +
        '  -webkit-mask: radial-gradient(\n    ellipse calc(84px * var(--beam-w, 1)) calc(110px * var(--beam-h, 1)) at calc(var(--beam-x, 0.5) * 100%) 100%,\n    white 0%, rgba(255, 255, 255, 0.5) 35%, transparent 100%\n  );\n' +
        '  mask: radial-gradient(\n    ellipse calc(84px * var(--beam-w, 1)) calc(110px * var(--beam-h, 1)) at calc(var(--beam-x, 0.5) * 100%) 100%,\n    white 0%, rgba(255, 255, 255, 0.5) 35%, transparent 100%\n  );\n' +
        '  pointer-events: none;\n  z-index: 3;\n' +
        '  opacity: calc(var(--beam-edge, 1) * ' + bloom + ');' + bloomHue + '\n}\n' + hue.frames + bloomFrames;
    }

    /* ================================================================
       Three previews at once — scope each generator's `.beam-card`
       rules to a per-type class so all three render simultaneously
       and update from the same controls.
       ================================================================ */

    function scopeCSS(css, cls) { return css.split('.beam-card').join('.' + cls); }

    const TYPES = [
      { key: 'md', cls: 'beam-card--md', label: 'Card', gen: (s) => generateRotateCSS(s, false) },
      { key: 'sm', cls: 'beam-card--sm', label: 'Button', gen: (s) => generateRotateCSS(s, true) },
      { key: 'line', cls: 'beam-card--line', label: 'Search line', gen: (s) => generateLineCSS(s) }
    ];

    // Which variations the exported code includes (all on by default).
    // Preview always shows all three; this only filters the export.
    const exportPick = { md: true, sm: true, line: true };

    function driverAll(s, sel) {
      const durMs = Math.round(s.duration * 1000);
      const rotSel = [sel.md ? '.beam-card--md' : null, sel.sm ? '.beam-card--sm' : null].filter(Boolean).join(', ');
      const wantLine = !!sel.line;
      if (!rotSel && !wantLine) return '';

      const note = rotSel && wantLine
        ? '// One rAF loop drives everything: the rotate presets read --beam-angle,\n// the search line runs six desynchronized tracks (travel, edge fade,\n// breathe 1.3x, spike 1.33x, spike2 1.7x).\n'
        : rotSel
          ? '// Drives --beam-angle with rAF — works in every browser, unlike the\n// @property custom-property animation the original relies on.\n'
          : '// Drives six desynchronized tracks (travel, edge fade, breathe 1.3x,\n// spike 1.33x, spike2 1.7x) — the mismatched periods make the glow organic.\n';

      let js = '<' + 'script>\n' + note +
        '(function () {\n' +
        '  if (matchMedia("(prefers-reduced-motion: reduce)").matches) return;\n' +
        '  var dur = ' + durMs + ';\n';
      if (rotSel) js += '  var rot = document.querySelectorAll("' + rotSel + '");\n';
      if (wantLine) {
        js += '  var lines = document.querySelectorAll(".beam-card--line");\n' +
          '  var T = {\n' +
          '    x:      { s: [[0,.06],[.1,.15],[.2,.25],[.3,.35],[.4,.44],[.5,.5],[.6,.56],[.7,.65],[.8,.75],[.9,.85],[1,.94]], e: 0, m: 1 },\n' +
          '    w:      { s: [[0,.5],[.1,.8],[.2,1.1],[.3,1.3],[.4,1.45],[.5,1.5],[.6,1.45],[.7,1.3],[.8,1.1],[.9,.8],[1,.5]], e: 0, m: 1 },\n' +
          '    edge:   { s: [[0,0],[.125,0],[.325,1],[.675,1],[.875,0],[1,0]], e: 0, m: 1 },\n' +
          '    h:      { s: [[0,.8],[.25,1.25],[.55,.85],[.8,1.3],[1,.8]], e: 1, m: 1.3 },\n' +
          '    spike:  { s: [[0,.8],[.25,1.3],[.5,.9],[.75,1.4],[1,.8]], e: 1, m: 1.33 },\n' +
          '    spike2: { s: [[0,1.2],[.25,.7],[.5,1.4],[.75,.8],[1,1.2]], e: 1, m: 1.7 }\n' +
          '  };\n' +
          '  function sample(tr, p) {\n' +
          '    var s = tr.s;\n' +
          '    for (var i = 1; i < s.length; i++) {\n' +
          '      if (p <= s[i][0]) {\n' +
          '        var u = (p - s[i - 1][0]) / (s[i][0] - s[i - 1][0]);\n' +
          '        if (tr.e) u = u * u * (3 - 2 * u);\n' +
          '        return s[i - 1][1] + (s[i][1] - s[i - 1][1]) * u;\n' +
          '      }\n' +
          '    }\n' +
          '    return s[s.length - 1][1];\n' +
          '  }\n';
      }
      js += '  function tick(t) {\n';
      if (rotSel) {
        js += '    var deg = ((t / dur) % 1) * 360 + "deg";\n' +
          '    for (var i = 0; i < rot.length; i++) rot[i].style.setProperty("--beam-angle", deg);\n';
      }
      if (wantLine) {
        js += '    for (var j = 0; j < lines.length; j++) {\n' +
          '      var st = lines[j].style;\n' +
          '      for (var k in T) { var p = (t / (dur * T[k].m)) % 1; st.setProperty("--beam-" + k, sample(T[k], p).toFixed(4)); }\n' +
          '    }\n';
      }
      js += '    requestAnimationFrame(tick);\n' +
        '  }\n' +
        '  requestAnimationFrame(tick);\n' +
        '})();\n' +
        '<' + '/script>';
      return js;
    }

    function generateOutput(s, sel) {
      const picked = TYPES.filter((t) => sel[t.key]);
      if (!picked.length) return '/* Tick a variation above to generate its CSS. */';
      const names = picked.map((t) => t.label.toLowerCase()).join(', ');
      let o = '/* Border beam — ' + names + ',\n' +
        '   ported from github.com/Jakubantalik/border-beam (MIT) */\n';
      picked.forEach((t) => {
        o += '\n/* ===== ' + t.label + '  ·  .' + t.cls + ' ===== */\n' +
          scopeCSS(t.gen(s), t.cls) + '\n' +
          '/* Markup: <div class="' + t.cls + '"><div class="beam-bloom"></div>' +
          '<div class="content">…</div></div>   (.content { position: relative; z-index: 0 }) */\n';
      });
      o += '\n/* Driver — place once before </body>: */\n' + driverAll(s, sel);
      return o;
    }

    function refreshExport() {
      out.textContent = generateOutput(state, exportPick);
    }

    /* ================================================================
       Live preview wiring
       ================================================================ */

    const beamStyle = $('beam-style');
    const stage = $('stage');
    const slot = $('preview-slot');
    const out = $('css');
    let els = {};   // { md, sm, line } — the three live preview elements

    function textColors(isDark) {
      return { fg: isDark ? '#fafaf9' : '#1c1c1e', dim: isDark ? '#a1a1aa' : '#5f5f66' };
    }

    // Build the three preview elements once (and on theme change, since
    // their content colors are theme-dependent). Slider drags only
    // rewrite #beam-style, so the DOM and driver refs stay stable.
    function buildPreview() {
      const t = textColors(state.theme === 'dark');
      slot.innerHTML =
        '<div class="beam-card--md bg-el bg-el--card">' +
        '<div class="beam-bloom"></div>' +
        '<div class="content">' +
        '<h3 style="color: ' + t.fg + ';">Your card</h3>' +
        '<p style="color: ' + t.dim + ';">Any content sits inside the glowing border — the beam is pure CSS plus a tiny rAF driver.</p>' +
        '</div>' +
        '</div>' +
        '<div class="beam-card--sm bg-el bg-el--btn">' +
        '<div class="beam-bloom"></div>' +
        '<div class="content" style="color: ' + t.fg + ';">Button</div>' +
        '</div>' +
        '<div class="beam-card--line bg-el bg-el--search">' +
        '<div class="beam-bloom"></div>' +
        '<div class="content">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="' + t.dim + '" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>' +
        '<span style="color: ' + t.dim + ';">Search</span>' +
        '</div>' +
        '</div>';
      els = {
        md: slot.querySelector('.beam-card--md'),
        sm: slot.querySelector('.beam-card--sm'),
        line: slot.querySelector('.beam-card--line')
      };
    }

    function render() {
      const s = state;
      beamStyle.textContent = TYPES.map((t) => scopeCSS(t.gen(s), t.cls)).join('\n\n');
      stage.style.background = s.pagebg;

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

      refreshExport();
    }

    /* rAF driver — same math the exported driver ships with. Rotate
       presets (md, sm) read --beam-angle; the line reads six tracks. */
    function sample(track, p) {
      const st = track.stops;
      for (let i = 1; i < st.length; i++) {
        if (p <= st[i][0]) {
          let u = (p - st[i - 1][0]) / (st[i][0] - st[i - 1][0]);
          if (track.ease === 'inout') u = u * u * (3 - 2 * u);
          return st[i - 1][1] + (st[i][1] - st[i - 1][1]) * u;
        }
      }
      return st[st.length - 1][1];
    }

    let rafId = null;
    function loop(t) {
      const durMs = state.duration * 1000;
      const deg = (((t / durMs) % 1) * 360).toFixed(2) + 'deg';
      if (els.md) els.md.style.setProperty('--beam-angle', deg);
      if (els.sm) els.sm.style.setProperty('--beam-angle', deg);
      if (els.line) {
        for (const k in TRACKS) {
          const p = (t / (durMs * TRACKS[k].mult)) % 1;
          els.line.style.setProperty('--beam-' + k, sample(TRACKS[k], p).toFixed(4));
        }
      }
      rafId = requestAnimationFrame(loop);
    }
    function startDriver() { if (!rafId) rafId = requestAnimationFrame(loop); }
    function stopDriver() { if (rafId) { cancelAnimationFrame(rafId); rafId = null; } }

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
        state.saturation = themePresets.md[theme].sat;
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
      buildPreview();
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

    /* Export modal (topstrip button) — the <pre> is kept current by render() */
    const exportModal = $('export-modal');
    $('bg-export-btn').addEventListener('click', () => exportModal.classList.add('open'));
    exportModal.querySelectorAll('[data-close]').forEach((el) =>
      el.addEventListener('click', () => exportModal.classList.remove('open')));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') exportModal.classList.remove('open'); });

    // Include checkboxes — filter which variations the export code contains.
    ['md', 'sm', 'line'].forEach((k) => {
      $('pick-' + k).addEventListener('change', (e) => {
        exportPick[k] = e.target.checked;
        refreshExport();
      });
    });

    buildPreview();
    render();
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) startDriver();
  })();
</script>

<?php require '../includes/footer.php'; ?>