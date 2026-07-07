<?php
$pageTitle = 'Button Maker — Generate Reusable CSS Buttons | ONE design';
$pageDescription = 'Design button styles for real interfaces: states, spacing, radius, and type. Preview instantly and export reusable CSS.';
$activePage = 'button-maker';
$shellClass = 'full-height';
require '../includes/header.php';
?>

<main class="panel">

  <div class="topstrip">
    <div class="topstrip-head">
      <h1 class="topstrip-title">Button <em>maker</em></h1>
      <p class="topstrip-intro">Design primary and secondary button styles with adjustable radius, spacing, type, and
        color. Preview states side by side and export reusable CSS for product UIs and design systems.</p>
    </div>
    <div class="topstrip-actions">
      <button class="btn" onclick="openExportModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
          <rect x="9" y="9" width="13" height="13" rx="2" />
          <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
        </svg>
        Export CSS
      </button>
      <button class="btn btn-primary" onclick="saveButton()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
          <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" />
        </svg>
        Save
      </button>
    </div>
  </div>

  <div class="workspace">

    <!-- ── LEFT PANEL ──────────────────────────────────── -->
    <div class="grad-panel">

      <div class="grad-section">
        <div class="field-label">Typeface</div>
        <div class="font-picker-row">
          <button class="font-picker-trigger" id="font-picker-trigger" onclick="openFontPicker()" style="flex:1">
            <span class="font-picker-trigger-name" id="font-picker-name">System UI</span>
            <svg viewBox="0 0 24 24" class="font-picker-chevron">
              <polyline points="6 9 12 15 18 9" />
            </svg>
          </button>
        </div>

        <div class="bm-type-row">
          <select class="bm-select" id="fw-select" onchange="setFontWeight(+this.value)" aria-label="Font weight">
            <option value="300">Light</option>
            <option value="400">Regular</option>
            <option value="500" selected>Medium</option>
            <option value="600">Semibold</option>
          </select>
          <select class="bm-select" id="fs-select" onchange="setFontSize(+this.value)" aria-label="Font size">
            <option value="11">11px</option>
            <option value="12">12px</option>
            <option value="13">13px</option>
            <option value="14" selected>14px</option>
            <option value="15">15px</option>
            <option value="16">16px</option>
            <option value="17">17px</option>
            <option value="18">18px</option>
            <option value="19">19px</option>
            <option value="20">20px</option>
          </select>
        </div>
      </div>

      <div class="grad-section">
        <div class="field-label">Shape &amp; size</div>

        <div class="bm-slider-row">
          <div class="bm-slider-head">
            <span class="bm-label">Border radius</span>
            <span class="bm-val" id="val-radius">8px</span>
          </div>
          <input type="range" class="slider" id="sl-radius" min="0" max="32" step="1" value="8">
        </div>

        <div class="bm-slider-row">
          <div class="bm-slider-head">
            <span class="bm-label">Padding — vertical</span>
            <span class="bm-val" id="val-pv">10px</span>
          </div>
          <input type="range" class="slider" id="sl-pv" min="4" max="24" step="1" value="10">
        </div>

        <div class="bm-slider-row">
          <div class="bm-slider-head">
            <span class="bm-label">Padding — horizontal</span>
            <span class="bm-val" id="val-ph">20px</span>
          </div>
          <input type="range" class="slider" id="sl-ph" min="8" max="56" step="2" value="20">
        </div>
      </div>

      <div class="grad-section">
        <div class="field-label">Primary</div>

        <div class="bm-color-row">
          <div class="bm-color-left">
            <label class="bm-switch">
              <input type="checkbox" class="bm-toggle" data-key="pBgOn" checked>
              <span class="bm-switch-track"></span>
            </label>
            <span class="bm-label">Background</span>
          </div>
          <div class="bm-color-control">
            <button class="bm-palette-btn" data-target="pBg" title="Pull from saved palette"
              aria-label="Pull from saved palette">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="5" width="5" height="14" rx="1.2" />
                <rect x="9.5" y="5" width="5" height="14" rx="1.2" opacity=".6" />
                <rect x="16" y="5" width="5" height="14" rx="1.2" opacity=".35" />
              </svg>
            </button>
            <label class="bm-swatch-wrap">
              <div class="bm-swatch" id="sw-p-bg"></div>
              <input type="color" class="bm-color-input" id="in-p-bg" value="#2563eb">
            </label>
          </div>
        </div>

        <div class="bm-color-row">
          <div class="bm-color-left">
            <label class="bm-switch">
              <input type="checkbox" class="bm-toggle" data-key="pBorderOn">
              <span class="bm-switch-track"></span>
            </label>
            <span class="bm-label">Stroke</span>
          </div>
          <div class="bm-color-control">
            <button class="bm-palette-btn" data-target="pBorder" title="Pull from saved palette"
              aria-label="Pull from saved palette">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="5" width="5" height="14" rx="1.2" />
                <rect x="9.5" y="5" width="5" height="14" rx="1.2" opacity=".6" />
                <rect x="16" y="5" width="5" height="14" rx="1.2" opacity=".35" />
              </svg>
            </button>
            <label class="bm-swatch-wrap">
              <div class="bm-swatch" id="sw-p-border"></div>
              <input type="color" class="bm-color-input" id="in-p-border" value="#2563eb">
            </label>
          </div>
        </div>

        <div class="bm-color-row">
          <div class="bm-color-left">
            <span class="bm-switch-spacer"></span>
            <span class="bm-label">Text</span>
          </div>
          <div class="bm-color-control">
            <button class="bm-palette-btn" data-target="pText" title="Pull from saved palette"
              aria-label="Pull from saved palette">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="5" width="5" height="14" rx="1.2" />
                <rect x="9.5" y="5" width="5" height="14" rx="1.2" opacity=".6" />
                <rect x="16" y="5" width="5" height="14" rx="1.2" opacity=".35" />
              </svg>
            </button>
            <label class="bm-swatch-wrap">
              <div class="bm-swatch" id="sw-p-text"></div>
              <input type="color" class="bm-color-input" id="in-p-text" value="#ffffff">
            </label>
          </div>
        </div>

        <div class="bm-slider-row">
          <div class="bm-slider-head">
            <span class="bm-label">Opacity</span>
            <span class="bm-val" id="val-p-opacity">100%</span>
          </div>
          <input type="range" class="slider" id="sl-p-opacity" min="10" max="100" step="1" value="100">
        </div>
      </div>

      <div class="grad-section">
        <div class="field-label">Secondary</div>

        <div class="bm-color-row">
          <div class="bm-color-left">
            <label class="bm-switch">
              <input type="checkbox" class="bm-toggle" data-key="sBgOn">
              <span class="bm-switch-track"></span>
            </label>
            <span class="bm-label">Background</span>
          </div>
          <div class="bm-color-control">
            <button class="bm-palette-btn" data-target="sBg" title="Pull from saved palette"
              aria-label="Pull from saved palette">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="5" width="5" height="14" rx="1.2" />
                <rect x="9.5" y="5" width="5" height="14" rx="1.2" opacity=".6" />
                <rect x="16" y="5" width="5" height="14" rx="1.2" opacity=".35" />
              </svg>
            </button>
            <label class="bm-swatch-wrap">
              <div class="bm-swatch" id="sw-s-bg"></div>
              <input type="color" class="bm-color-input" id="in-s-bg" value="#dbeafe">
            </label>
          </div>
        </div>

        <div class="bm-color-row">
          <div class="bm-color-left">
            <label class="bm-switch">
              <input type="checkbox" class="bm-toggle" data-key="sBorderOn" checked>
              <span class="bm-switch-track"></span>
            </label>
            <span class="bm-label">Stroke</span>
          </div>
          <div class="bm-color-control">
            <button class="bm-palette-btn" data-target="sBorder" title="Pull from saved palette"
              aria-label="Pull from saved palette">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="5" width="5" height="14" rx="1.2" />
                <rect x="9.5" y="5" width="5" height="14" rx="1.2" opacity=".6" />
                <rect x="16" y="5" width="5" height="14" rx="1.2" opacity=".35" />
              </svg>
            </button>
            <label class="bm-swatch-wrap">
              <div class="bm-swatch" id="sw-s-border"></div>
              <input type="color" class="bm-color-input" id="in-s-border" value="#2563eb">
            </label>
          </div>
        </div>

        <div class="bm-color-row">
          <div class="bm-color-left">
            <span class="bm-switch-spacer"></span>
            <span class="bm-label">Text</span>
          </div>
          <div class="bm-color-control">
            <button class="bm-palette-btn" data-target="sText" title="Pull from saved palette"
              aria-label="Pull from saved palette">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="5" width="5" height="14" rx="1.2" />
                <rect x="9.5" y="5" width="5" height="14" rx="1.2" opacity=".6" />
                <rect x="16" y="5" width="5" height="14" rx="1.2" opacity=".35" />
              </svg>
            </button>
            <label class="bm-swatch-wrap">
              <div class="bm-swatch" id="sw-s-text"></div>
              <input type="color" class="bm-color-input" id="in-s-text" value="#2563eb">
            </label>
          </div>
        </div>

        <div class="bm-slider-row">
          <div class="bm-slider-head">
            <span class="bm-label">Opacity</span>
            <span class="bm-val" id="val-s-opacity">100%</span>
          </div>
          <input type="range" class="slider" id="sl-s-opacity" min="10" max="100" step="1" value="100">
        </div>
      </div>

      <div class="grad-section">
        <div class="field-label">Tertiary</div>

        <div class="bm-color-row">
          <div class="bm-color-left">
            <label class="bm-switch">
              <input type="checkbox" class="bm-toggle" data-key="tBgOn">
              <span class="bm-switch-track"></span>
            </label>
            <span class="bm-label">Background</span>
          </div>
          <div class="bm-color-control">
            <button class="bm-palette-btn" data-target="tBg" title="Pull from saved palette"
              aria-label="Pull from saved palette">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="5" width="5" height="14" rx="1.2" />
                <rect x="9.5" y="5" width="5" height="14" rx="1.2" opacity=".6" />
                <rect x="16" y="5" width="5" height="14" rx="1.2" opacity=".35" />
              </svg>
            </button>
            <label class="bm-swatch-wrap">
              <div class="bm-swatch" id="sw-t-bg"></div>
              <input type="color" class="bm-color-input" id="in-t-bg" value="#2563eb">
            </label>
          </div>
        </div>

        <div class="bm-color-row">
          <div class="bm-color-left">
            <label class="bm-switch">
              <input type="checkbox" class="bm-toggle" data-key="tBorderOn">
              <span class="bm-switch-track"></span>
            </label>
            <span class="bm-label">Stroke</span>
          </div>
          <div class="bm-color-control">
            <button class="bm-palette-btn" data-target="tBorder" title="Pull from saved palette"
              aria-label="Pull from saved palette">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="5" width="5" height="14" rx="1.2" />
                <rect x="9.5" y="5" width="5" height="14" rx="1.2" opacity=".6" />
                <rect x="16" y="5" width="5" height="14" rx="1.2" opacity=".35" />
              </svg>
            </button>
            <label class="bm-swatch-wrap">
              <div class="bm-swatch" id="sw-t-border"></div>
              <input type="color" class="bm-color-input" id="in-t-border" value="#2563eb">
            </label>
          </div>
        </div>

        <div class="bm-color-row">
          <div class="bm-color-left">
            <span class="bm-switch-spacer"></span>
            <span class="bm-label">Text</span>
          </div>
          <div class="bm-color-control">
            <button class="bm-palette-btn" data-target="tText" title="Pull from saved palette"
              aria-label="Pull from saved palette">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="5" width="5" height="14" rx="1.2" />
                <rect x="9.5" y="5" width="5" height="14" rx="1.2" opacity=".6" />
                <rect x="16" y="5" width="5" height="14" rx="1.2" opacity=".35" />
              </svg>
            </button>
            <label class="bm-swatch-wrap">
              <div class="bm-swatch" id="sw-t-text"></div>
              <input type="color" class="bm-color-input" id="in-t-text" value="#2563eb">
            </label>
          </div>
        </div>

        <div class="bm-slider-row">
          <div class="bm-slider-head">
            <span class="bm-label">Opacity</span>
            <span class="bm-val" id="val-t-opacity">100%</span>
          </div>
          <input type="range" class="slider" id="sl-t-opacity" min="10" max="100" step="1" value="100">
        </div>
      </div>

    </div>

    <!-- ── RIGHT: PREVIEW ──────────────────────────────── -->
    <div class="bm-preview-area">
      <div class="bm-preview-inner">

        <div class="bm-preview-header">
          <div></div>
          <div class="bm-col-label">Primary</div>
          <div class="bm-col-label">Secondary</div>
          <div class="bm-col-label">Tertiary</div>
        </div>

        <div class="bm-size-row">
          <span class="bm-size-tag">Large</span>
          <div class="bm-btn-cell"><button class="bm-btn" id="btn-p-lg">Button</button></div>
          <div class="bm-btn-cell"><button class="bm-btn" id="btn-s-lg">Button</button></div>
          <div class="bm-btn-cell"><button class="bm-btn" id="btn-t-lg">Button</button></div>
        </div>

        <div class="bm-size-row">
          <span class="bm-size-tag">Default</span>
          <div class="bm-btn-cell"><button class="bm-btn" id="btn-p-md">Button</button></div>
          <div class="bm-btn-cell"><button class="bm-btn" id="btn-s-md">Button</button></div>
          <div class="bm-btn-cell"><button class="bm-btn" id="btn-t-md">Button</button></div>
        </div>

        <div class="bm-size-row">
          <span class="bm-size-tag">Small</span>
          <div class="bm-btn-cell"><button class="bm-btn" id="btn-p-sm">Button</button></div>
          <div class="bm-btn-cell"><button class="bm-btn" id="btn-s-sm">Button</button></div>
          <div class="bm-btn-cell"><button class="bm-btn" id="btn-t-sm">Button</button></div>
        </div>

        <section class="tool-seo-section" aria-labelledby="button-seo-title">
          <h2 id="button-seo-title">Design Reusable Button Styles Faster</h2>
          <p>This button generator is built for shaping consistent primary, secondary, and tertiary button patterns
            without writing each variation from scratch. You can control radius, spacing, typography, fill, stroke, and
            opacity while previewing how the set behaves across multiple sizes.</p>
          <p>It works well for design systems, landing pages, app interfaces, and quick UI exploration. If you need a
            CSS button maker that helps translate visual decisions into reusable production code, this page gives you a
            more direct workflow from styling decisions to export.</p>
        </section>

      </div>
    </div>

  </div>
</main>
</div>

<!-- ── FONT PICKER DROPDOWN ─────────────────────────────── -->
<div class="font-picker-dropdown" id="font-picker-dropdown">
  <div class="font-picker-search-wrap">
    <svg viewBox="0 0 24 24" class="font-picker-search-icon" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8" />
      <line x1="21" y1="21" x2="16.65" y2="16.65" />
    </svg>
    <input type="text" class="font-picker-search" id="font-picker-search" placeholder="Search fonts…" autocomplete="off"
      spellcheck="false" oninput="renderFontList(this.value)">
  </div>
  <div class="font-picker-list" id="font-picker-list"></div>
</div>

<!-- ── EXPORT MODAL ─────────────────────────────────────── -->
<div class="export-modal" id="export-modal">
  <div class="export-modal-backdrop" onclick="closeExportModal()"></div>
  <div class="export-modal-box">
    <div class="export-modal-header">
      <span style="font-family:var(--mono);font-size:13px;font-weight:500">CSS export</span>
      <div class="export-modal-actions">
        <button class="btn" id="export-copy-btn" onclick="copyCSS()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
            <rect x="9" y="9" width="13" height="13" rx="2" />
            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
          </svg>
          Copy
        </button>
        <button class="export-modal-close" onclick="closeExportModal()">×</button>
      </div>
    </div>
    <div class="export-modal-body">
      <pre class="export-modal-code" id="export-code"></pre>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>
<script src="/assets/color-math.js?v=<?= APP_VERSION ?>"></script>
<script>
  // ── STATE ──────────────────────────────────────────────
  const DRAFT_KEY = 'oklch-btn-draft';

  let s = {
    radius: 8,
    fontSize: 14,
    padV: 10,
    padH: 20,
    fontWeight: 500,
    fontFamily: 'System UI',
    pBg: '#2563eb', pBgOn: true,
    pBorder: '#2563eb', pBorderOn: false,
    pText: '#ffffff',
    pOpacity: 100,
    sBg: '#dbeafe', sBgOn: false,
    sBorder: '#2563eb', sBorderOn: true,
    sText: '#2563eb',
    sOpacity: 100,
    tBg: '#2563eb', tBgOn: false,
    tBorder: '#2563eb', tBorderOn: false,
    tText: '#2563eb',
    tOpacity: 100,
  };

  const SIZES = {
    lg: { fScale: 1.2, pScale: 1.3 },
    md: { fScale: 1.0, pScale: 1.0 },
    sm: { fScale: 0.82, pScale: 0.72 },
  };

  // ── RENDER ─────────────────────────────────────────────
  function applyBtn(id, variant, size) {
    const el = document.getElementById(id);
    const { fScale, pScale } = SIZES[size];
    const pv = Math.round(s.padV * pScale);
    const ph = Math.round(s.padH * pScale);

    el.style.fontFamily = fontStackFor(s.fontFamily);
    el.style.fontSize = (s.fontSize * fScale).toFixed(1) + 'px';
    el.style.fontWeight = s.fontWeight;
    el.style.padding = pv + 'px ' + ph + 'px';
    el.style.borderRadius = s.radius + 'px';
    el.style.border = '1.5px solid transparent';

    const p = variant === 'primary' ? 'p' : variant === 'secondary' ? 's' : 't';
    el.style.background = s[p + 'BgOn'] ? s[p + 'Bg'] : 'transparent';
    el.style.color = s[p + 'Text'];
    el.style.borderColor = s[p + 'BorderOn'] ? s[p + 'Border'] : 'transparent';
    el.style.opacity = s[p + 'Opacity'] / 100;
  }

  function render() {
    ['lg', 'md', 'sm'].forEach(sz => {
      applyBtn('btn-p-' + sz, 'primary', sz);
      applyBtn('btn-s-' + sz, 'secondary', sz);
      applyBtn('btn-t-' + sz, 'tertiary', sz);
    });

    // Typeface controls
    const fpName = document.getElementById('font-picker-name');
    fpName.textContent = s.fontFamily;
    fpName.style.fontFamily = fontStackFor(s.fontFamily);
    document.getElementById('fw-select').value = s.fontWeight;
    document.getElementById('fs-select').value = s.fontSize;

    // Value labels
    document.getElementById('val-radius').textContent = s.radius + 'px';
    document.getElementById('val-pv').textContent = s.padV + 'px';
    document.getElementById('val-ph').textContent = s.padH + 'px';
    document.getElementById('val-p-opacity').textContent = s.pOpacity + '%';
    document.getElementById('val-s-opacity').textContent = s.sOpacity + '%';
    document.getElementById('val-t-opacity').textContent = s.tOpacity + '%';

    // Color swatches + native input sync
    [['p-bg', 'pBg'], ['p-border', 'pBorder'], ['p-text', 'pText'],
    ['s-bg', 'sBg'], ['s-border', 'sBorder'], ['s-text', 'sText'],
    ['t-bg', 'tBg'], ['t-border', 'tBorder'], ['t-text', 'tText']].forEach(([id, key]) => {
      document.getElementById('sw-' + id).style.background = s[key];
      document.getElementById('in-' + id).value = s[key];
    });

    // Sync slider positions
    document.getElementById('sl-radius').value = s.radius;
    document.getElementById('sl-pv').value = s.padV;
    document.getElementById('sl-ph').value = s.padH;
    document.getElementById('sl-p-opacity').value = s.pOpacity;
    document.getElementById('sl-s-opacity').value = s.sOpacity;
    document.getElementById('sl-t-opacity').value = s.tOpacity;

    // Toggle states — sync checkbox + dim the row's color control when off
    document.querySelectorAll('.bm-toggle').forEach(cb => {
      const on = !!s[cb.dataset.key];
      cb.checked = on;
      cb.closest('.bm-color-row').classList.toggle('bm-row-off', !on);
    });

    persistDraft();
  }

  // ── MUTATORS ───────────────────────────────────────────
  function setFontWeight(w) { s.fontWeight = w; render(); }
  function setFontSize(v) { s.fontSize = v; render(); }

  // ── SLIDER WIRING ──────────────────────────────────────
  document.getElementById('sl-radius').addEventListener('input', e => { s.radius = +e.target.value; render(); });
  document.getElementById('sl-pv').addEventListener('input', e => { s.padV = +e.target.value; render(); });
  document.getElementById('sl-ph').addEventListener('input', e => { s.padH = +e.target.value; render(); });
  document.getElementById('sl-p-opacity').addEventListener('input', e => { s.pOpacity = +e.target.value; render(); });
  document.getElementById('sl-s-opacity').addEventListener('input', e => { s.sOpacity = +e.target.value; render(); });
  document.getElementById('sl-t-opacity').addEventListener('input', e => { s.tOpacity = +e.target.value; render(); });

  // ── TOGGLE WIRING ──────────────────────────────────────
  document.querySelectorAll('.bm-toggle').forEach(cb => {
    cb.addEventListener('change', e => { s[cb.dataset.key] = e.target.checked; render(); });
  });

  // ── COLOR WIRING ───────────────────────────────────────
  function wireColor(inputId, key) {
    document.getElementById(inputId).addEventListener('input', e => { s[key] = e.target.value; render(); });
  }
  wireColor('in-p-bg', 'pBg');
  wireColor('in-p-border', 'pBorder');
  wireColor('in-p-text', 'pText');
  wireColor('in-s-bg', 'sBg');
  wireColor('in-s-border', 'sBorder');
  wireColor('in-s-text', 'sText');
  wireColor('in-t-bg', 'tBg');
  wireColor('in-t-border', 'tBorder');
  wireColor('in-t-text', 'tText');

  // ── PULL FROM SAVED PALETTE ────────────────────────────
  const PAL_KEY = 'oklch-palettes';
  function loadSavedPalettes() {
    try { return JSON.parse(localStorage.getItem(PAL_KEY) || '[]'); }
    catch (_) { return []; }
  }
  function escapeHtml(str) {
    return String(str).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  }

  const pop = document.createElement('div');
  pop.className = 'bm-pop';
  document.body.appendChild(pop);
  let popTargetKey = null;

  function buildPop() {
    const palettes = loadSavedPalettes();
    if (!palettes.length) {
      pop.innerHTML = `<div class="bm-pop-empty">No saved palettes yet.<br><a href="/palette/">Open palette generator →</a></div>`;
      return;
    }
    let html = '';
    [...palettes].reverse().forEach(p => {
      html += `<div class="bm-pop-pal"><div class="bm-pop-pal-name">${escapeHtml(p.name)}</div>`;
      p.colors.forEach(c => {
        html += `<div class="bm-pop-color"><span class="bm-pop-color-label">${escapeHtml(c.name)}</span><div class="bm-pop-scale">`;
        genScale(c.hex).forEach(hex => {
          html += `<button class="bm-pop-sw" style="background:${hex}" data-hex="${hex}" title="${hex}" aria-label="${hex}"></button>`;
        });
        html += `</div></div>`;
      });
      html += `</div>`;
    });
    pop.innerHTML = html;
  }

  function openPop(btn) {
    popTargetKey = btn.dataset.target;
    buildPop();
    const r = btn.getBoundingClientRect();
    const pw = 248, ph = pop.offsetHeight;
    let left = r.right + 8;
    if (left + pw > window.innerWidth - 8) left = r.left - pw - 8;
    let top = r.top - 4;
    if (top + ph > window.innerHeight - 8) top = Math.max(8, window.innerHeight - ph - 8);
    pop.style.left = left + 'px';
    pop.style.top = top + 'px';
    pop.classList.add('open');
  }
  function closePop() { pop.classList.remove('open'); popTargetKey = null; }

  document.querySelectorAll('.bm-palette-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      if (pop.classList.contains('open') && popTargetKey === btn.dataset.target) { closePop(); return; }
      openPop(btn);
    });
  });
  pop.addEventListener('click', e => {
    const sw = e.target.closest('.bm-pop-sw');
    if (!sw || !popTargetKey) return;
    s[popTargetKey] = sw.dataset.hex;
    // Pulling a color into a background/stroke layer implies turning that layer on
    if (/(Bg|Border)$/.test(popTargetKey)) s[popTargetKey + 'On'] = true;
    render();
    closePop();
  });
  document.addEventListener('click', e => {
    if (pop.classList.contains('open') && !pop.contains(e.target) && !e.target.closest('.bm-palette-btn')) closePop();
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closePop(); });
  document.querySelector('.grad-panel')?.addEventListener('scroll', closePop);
  window.addEventListener('resize', closePop);

  // ── CSS EXPORT ─────────────────────────────────────────
  function genCSS() {
    const px = (v, scale) => Math.round(v * scale);
    const isGoogle = s.fontFamily && s.fontFamily !== 'System UI';
    return [
      ...(isGoogle ? [`@import url('${fontImportURL(s.fontFamily)}');`, ''] : []),
      '/* ── Button base ──────────────────────────── */',
      '.btn {',
      '  display: inline-flex;',
      '  align-items: center;',
      '  justify-content: center;',
      '  gap: 6px;',
      `  font-family: ${fontStackFor(s.fontFamily)};`,
      `  font-size: ${s.fontSize}px;`,
      `  font-weight: ${s.fontWeight};`,
      `  padding: ${s.padV}px ${s.padH}px;`,
      `  border-radius: ${s.radius}px;`,
      '  border: 1.5px solid transparent;',
      '  line-height: 1;',
      '  cursor: pointer;',
      '  white-space: nowrap;',
      '  transition: opacity 0.15s, transform 0.15s;',
      '}',
      '.btn:hover  { opacity: 0.85; }',
      '.btn:active { transform: scale(0.98); }',
      '',
      '/* Sizes */',
      '.btn-lg {',
      `  font-size: ${px(s.fontSize, 1.2)}px;`,
      `  padding: ${px(s.padV, 1.3)}px ${px(s.padH, 1.3)}px;`,
      '}',
      '.btn-sm {',
      `  font-size: ${px(s.fontSize, 0.82)}px;`,
      `  padding: ${px(s.padV, 0.72)}px ${px(s.padH, 0.72)}px;`,
      '}',
      '',
      '/* Primary */',
      '.btn-primary {',
      `  background: ${s.pBgOn ? s.pBg : 'transparent'};`,
      `  color: ${s.pText};`,
      ...(s.pBorderOn ? [`  border-color: ${s.pBorder};`] : []),
      ...(s.pOpacity < 100 ? [`  opacity: ${(s.pOpacity / 100).toFixed(2)};`] : []),
      '}',
      '',
      '/* Secondary */',
      '.btn-secondary {',
      `  background: ${s.sBgOn ? s.sBg : 'transparent'};`,
      `  color: ${s.sText};`,
      ...(s.sBorderOn ? [`  border-color: ${s.sBorder};`] : []),
      ...(s.sOpacity < 100 ? [`  opacity: ${(s.sOpacity / 100).toFixed(2)};`] : []),
      '}',
      '',
      '/* Tertiary */',
      '.btn-tertiary {',
      `  background: ${s.tBgOn ? s.tBg : 'transparent'};`,
      `  color: ${s.tText};`,
      ...(s.tBorderOn ? [`  border-color: ${s.tBorder};`] : []),
      ...(s.tOpacity < 100 ? [`  opacity: ${(s.tOpacity / 100).toFixed(2)};`] : []),
      '}',
    ].join('\n');
  }

  function openExportModal() {
    document.getElementById('export-code').textContent = genCSS();
    document.getElementById('export-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeExportModal() {
    document.getElementById('export-modal').classList.remove('open');
    document.body.style.overflow = '';
  }
  function copyCSS() {
    const code = document.getElementById('export-code').textContent;
    navigator.clipboard.writeText(code).then(() => {
      const btn = document.getElementById('export-copy-btn');
      const orig = btn.innerHTML;
      btn.textContent = 'Copied!';
      setTimeout(() => btn.innerHTML = orig, 1600);
    });
  }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') { if (pickerOpen) closeFontPicker(); else closeExportModal(); } });

  // ── FONT PICKER (Google Fonts) ─────────────────────────
  const SYSTEM_FONT = 'System UI';
  const SYSTEM_STACK = "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif";
  const FALLBACK = { Sans: 'sans-serif', Serif: 'serif', Mono: 'monospace', Display: 'cursive' };
  const loadedFonts = new Set();
  const loadedPreviewFonts = new Set();
  let pickerOpen = false;
  let fontObserver = null;

  const GOOGLE_FONTS = [
    { name: 'Inter', cat: 'Sans' }, { name: 'Roboto', cat: 'Sans' }, { name: 'Open Sans', cat: 'Sans' },
    { name: 'Lato', cat: 'Sans' }, { name: 'Montserrat', cat: 'Sans' }, { name: 'Poppins', cat: 'Sans' },
    { name: 'Nunito', cat: 'Sans' }, { name: 'Raleway', cat: 'Sans' }, { name: 'Oswald', cat: 'Sans' },
    { name: 'Ubuntu', cat: 'Sans' }, { name: 'Work Sans', cat: 'Sans' }, { name: 'Rubik', cat: 'Sans' },
    { name: 'Noto Sans', cat: 'Sans' }, { name: 'DM Sans', cat: 'Sans' }, { name: 'Outfit', cat: 'Sans' },
    { name: 'Plus Jakarta Sans', cat: 'Sans' }, { name: 'Figtree', cat: 'Sans' }, { name: 'Manrope', cat: 'Sans' },
    { name: 'Mulish', cat: 'Sans' }, { name: 'Karla', cat: 'Sans' }, { name: 'Barlow', cat: 'Sans' },
    { name: 'Cabin', cat: 'Sans' }, { name: 'Jost', cat: 'Sans' }, { name: 'Quicksand', cat: 'Sans' },
    { name: 'Source Sans 3', cat: 'Sans' }, { name: 'Nunito Sans', cat: 'Sans' }, { name: 'IBM Plex Sans', cat: 'Sans' },
    { name: 'Sora', cat: 'Sans' }, { name: 'Lexend', cat: 'Sans' },
    { name: 'Playfair Display', cat: 'Serif' }, { name: 'Merriweather', cat: 'Serif' }, { name: 'Lora', cat: 'Serif' },
    { name: 'EB Garamond', cat: 'Serif' }, { name: 'Cormorant Garamond', cat: 'Serif' }, { name: 'Libre Baskerville', cat: 'Serif' },
    { name: 'Bitter', cat: 'Serif' }, { name: 'Crimson Text', cat: 'Serif' }, { name: 'PT Serif', cat: 'Serif' },
    { name: 'Fraunces', cat: 'Serif' }, { name: 'DM Serif Display', cat: 'Serif' }, { name: 'Young Serif', cat: 'Serif' },
    { name: 'Bodoni Moda', cat: 'Serif' }, { name: 'Cardo', cat: 'Serif' }, { name: 'Spectral', cat: 'Serif' },
    { name: 'IBM Plex Serif', cat: 'Serif' }, { name: 'Source Serif 4', cat: 'Serif' }, { name: 'Cormorant', cat: 'Serif' },
    { name: 'Roboto Mono', cat: 'Mono' }, { name: 'Source Code Pro', cat: 'Mono' }, { name: 'JetBrains Mono', cat: 'Mono' },
    { name: 'Fira Code', cat: 'Mono' }, { name: 'Space Mono', cat: 'Mono' }, { name: 'DM Mono', cat: 'Mono' },
    { name: 'IBM Plex Mono', cat: 'Mono' }, { name: 'Inconsolata', cat: 'Mono' }, { name: 'Courier Prime', cat: 'Mono' },
    { name: 'Fira Mono', cat: 'Mono' },
    { name: 'Abril Fatface', cat: 'Display' }, { name: 'Bebas Neue', cat: 'Display' }, { name: 'Righteous', cat: 'Display' },
    { name: 'Lobster', cat: 'Display' }, { name: 'Pacifico', cat: 'Display' }, { name: 'Permanent Marker', cat: 'Display' },
    { name: 'Dancing Script', cat: 'Display' }, { name: 'Caveat', cat: 'Display' }, { name: 'Satisfy', cat: 'Display' },
    { name: 'Sacramento', cat: 'Display' }, { name: 'Russo One', cat: 'Display' }, { name: 'Comfortaa', cat: 'Display' },
    { name: 'Alfa Slab One', cat: 'Display' },
  ];

  function fontStackFor(name) {
    if (!name || name === SYSTEM_FONT) return SYSTEM_STACK;
    const f = GOOGLE_FONTS.find(x => x.name === name);
    return `'${name}', ${f ? (FALLBACK[f.cat] || 'sans-serif') : 'sans-serif'}`;
  }
  function fontImportURL(name) {
    return `https://fonts.googleapis.com/css2?family=${encodeURIComponent(name)}:wght@300;400;500;600;700&display=swap`;
  }
  function ensureFontLoaded(name) {
    if (name === SYSTEM_FONT || loadedFonts.has(name)) return;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = fontImportURL(name);
    document.head.appendChild(link);
    loadedFonts.add(name);
  }

  function openFontPicker() {
    if (pickerOpen) { closeFontPicker(); return; }
    pickerOpen = true;
    const trigger = document.getElementById('font-picker-trigger');
    trigger.classList.add('open');
    const rect = trigger.getBoundingClientRect();
    const W = 264;
    let left = rect.left;
    if (left + W > window.innerWidth - 8) left = window.innerWidth - W - 8;
    const dd = document.getElementById('font-picker-dropdown');
    dd.style.top = (rect.bottom + 4) + 'px';
    dd.style.left = left + 'px';
    dd.style.width = W + 'px';
    dd.classList.add('open');
    const search = document.getElementById('font-picker-search');
    search.value = '';
    renderFontList('');
    search.focus();
  }
  function closeFontPicker() {
    if (!pickerOpen) return;
    pickerOpen = false;
    document.getElementById('font-picker-trigger').classList.remove('open');
    document.getElementById('font-picker-dropdown').classList.remove('open');
    if (fontObserver) { fontObserver.disconnect(); fontObserver = null; }
  }

  function renderFontList(query) {
    const list = document.getElementById('font-picker-list');
    const q = query.toLowerCase().trim();
    let html = '';
    if (q) {
      const results = GOOGLE_FONTS.filter(f => f.name.toLowerCase().includes(q));
      html = results.length
        ? results.map(f => fontItemHTML(f.name, f.name === s.fontFamily)).join('')
        : '<div class="font-picker-empty">No fonts found</div>';
    } else {
      html += `<div class="font-picker-cat">Default</div>`;
      html += fontItemHTML(SYSTEM_FONT, s.fontFamily === SYSTEM_FONT);
      ['Sans', 'Serif', 'Mono', 'Display'].forEach(cat => {
        html += `<div class="font-picker-cat">${cat}</div>`;
        GOOGLE_FONTS.filter(f => f.cat === cat).forEach(f => {
          html += fontItemHTML(f.name, f.name === s.fontFamily);
        });
      });
    }
    list.innerHTML = html;
    setupFontObserver();
    const active = list.querySelector('.font-picker-item.active');
    if (active) setTimeout(() => active.scrollIntoView({ block: 'center' }), 0);
  }

  function fontItemHTML(name, isActive) {
    const isSystem = name === SYSTEM_FONT;
    const ff = isSystem ? SYSTEM_STACK
      : (loadedPreviewFonts.has(name) || loadedFonts.has(name)) ? `'${name}',sans-serif` : 'inherit';
    const check = isActive
      ? `<svg class="font-picker-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`
      : '';
    return `<button class="font-picker-item${isActive ? ' active' : ''}" data-font="${name}"${isSystem ? '' : ` data-load="1"`} onclick="applyFont('${name.replace(/'/g, "\\'")}')">
      <span class="font-picker-item-name" style="font-family:${ff}">${name}</span>${check}
    </button>`;
  }

  function setupFontObserver() {
    if (fontObserver) fontObserver.disconnect();
    const list = document.getElementById('font-picker-list');
    fontObserver = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (!e.isIntersecting) return;
        fontObserver.unobserve(e.target);
        const name = e.target.dataset.font;
        if (!name || !e.target.dataset.load) return;
        const nameEl = e.target.querySelector('.font-picker-item-name');
        if (!nameEl) return;
        if (!loadedPreviewFonts.has(name) && !loadedFonts.has(name)) {
          const link = document.createElement('link');
          link.rel = 'stylesheet';
          link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(name)}:wght@400&display=swap`;
          document.head.appendChild(link);
          loadedPreviewFonts.add(name);
        }
        nameEl.style.fontFamily = `'${name}',sans-serif`;
      });
    }, { root: list, rootMargin: '80px 0px' });
    list.querySelectorAll('.font-picker-item[data-load]').forEach(item => fontObserver.observe(item));
  }

  function applyFont(name) {
    s.fontFamily = name;
    ensureFontLoaded(name);
    closeFontPicker();
    render();
  }

  document.addEventListener('click', e => {
    if (!pickerOpen) return;
    const dd = document.getElementById('font-picker-dropdown');
    const trigger = document.getElementById('font-picker-trigger');
    if (!dd.contains(e.target) && !trigger.contains(e.target)) closeFontPicker();
  });

  // ── DRAFT PERSISTENCE ──────────────────────────────────
  let _dt;
  function persistDraft() {
    clearTimeout(_dt);
    _dt = setTimeout(() => localStorage.setItem(DRAFT_KEY, JSON.stringify(s)), 400);
  }

  // ── SAVE TO SAVED WORK ─────────────────────────────────
  const BTN_KEY = 'oklch-buttons';
  function loadButtons() {
    try { return JSON.parse(localStorage.getItem(BTN_KEY) || '[]'); }
    catch (_) { return []; }
  }
  function saveButton() {
    const all = loadButtons();
    const snap = JSON.parse(JSON.stringify(s));
    snap.fontStack = fontStackFor(s.fontFamily);  // resolved stack so the Saved page needs no font list
    all.push({
      id: 'btn-' + Date.now(),
      name: 'Button set',
      savedAt: Date.now(),
      s: snap,
    });
    localStorage.setItem(BTN_KEY, JSON.stringify(all));
    showToast('Button saved');
  }

  // ── INIT ───────────────────────────────────────────────
  let _loadedFromSave = false;
  const _loadId = new URLSearchParams(location.search).get('load');
  if (_loadId) {
    try {
      const saved = loadButtons().find(b => b.id === _loadId);
      if (saved && saved.s) { Object.assign(s, saved.s); delete s.secStyle; delete s.fontStack; _loadedFromSave = true; }
    } catch (_) { }
  }

  if (!_loadedFromSave) try {
    const d = JSON.parse(localStorage.getItem(DRAFT_KEY));
    if (d) {
      Object.assign(s, d);
      // ── Back-compat with pre-toggle drafts ──
      if (d.pBorder == null) s.pBorder = s.pBg;
      if (d.pBgOn == null) s.pBgOn = true;                     // primary was always filled
      if (d.pBorderOn == null) s.pBorderOn = false;            // primary had no visible stroke
      // Migrate the old secondary preset (outlined/filled/ghost) → fill/stroke toggles
      if (d.secStyle != null) {
        s.sBgOn = d.secStyle === 'filled';
        s.sBorderOn = d.secStyle === 'outlined';
      } else {
        if (d.sBgOn == null) s.sBgOn = false;
        if (d.sBorderOn == null) s.sBorderOn = true;
      }
      delete s.secStyle;
    }
  } catch (_) { }

  // Load the active typeface (if it's a Google font)
  ensureFontLoaded(s.fontFamily);

  render();
</script>

<?php require '../includes/footer.php'; ?>