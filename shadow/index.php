<?php
$pageTitle = 'Shadow Generator — Elevation Tokens for UI | ONE design';
$pageDescription = 'Build semantic shadow scales for light and dark themes, tint with your palette, and export production-ready CSS tokens.';
$activePage = 'shadow';
$shellClass = 'full-height';
require '../includes/header.php';
?>

<style>
  /* Left controls inherit the shared .grad-panel width; only internal styling here. */
  .shadow-page .grad-panel .picker-card {
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
  }

  .shadow-page .grad-panel .picker-card:last-child {
    border-bottom: none;
  }

  /* Narrow-panel segmented controls fill the width instead of overflowing */
  .shadow-page .grad-panel .tabs {
    width: 100%;
  }

  .shadow-page .grad-panel .tab-btn {
    flex: 1;
    min-width: 0;
    text-align: center;
    padding: 5px 4px;
    font-size: 10.5px;
    letter-spacing: 0.01em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Right side: preview + tokens scroll area */
  .shadow-page .shadow-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 24px 28px 40px;
  }
</style>

<main class="panel shadow-page">

  <div class="topstrip">
    <div class="topstrip-head">
      <h1 class="topstrip-title">Shadow <em>& elevation</em></h1>
      <p class="topstrip-intro">Build semantic shadow and elevation tokens for light and dark surfaces. Tint shadows
        from your palette, compare depth levels, and export clean CSS values or Figma-ready tokens.</p>
    </div>
    <div class="topstrip-actions">
      <button class="btn" onclick="openExportModal()">
        <svg viewBox="0 0 24 24">
          <rect x="9" y="9" width="13" height="13" rx="2" />
          <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
        </svg>
        Export
      </button>
      <button class="btn btn-primary" onclick="saveShadowScale()">
        <svg viewBox="0 0 24 24">
          <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" />
        </svg>
        <span id="save-label">Save scale</span>
      </button>
    </div>
  </div>

  <div class="workspace">

    <!-- ── LEFT COLUMN: controls (flush to sidebar) ───── -->
    <div class="grad-panel" id="controls-grid">

      <!-- Palette handoff card -->
      <div class="picker-card" id="handoff-card">
        <div class="picker-card-header">
          <span class="picker-card-title">Color source</span>
        </div>

        <div class="tabs" style="margin-bottom:12px">
          <button class="tab-btn active" id="mode-tinted" onclick="setShadowMode('tinted')">Palette tint</button>
          <button class="tab-btn" id="mode-custom" onclick="setShadowMode('custom')">Custom color</button>
          <button class="tab-btn" id="mode-black" onclick="setShadowMode('black')">Pure black</button>
        </div>

        <div id="handoff-active" style="display:none">
          <p class="shadow-hint">Palette loaded from Palette Generator</p>
          <div class="palette-chip-row" id="palette-chips"></div>
          <div class="palette-swatch-strip" id="palette-strip"></div>
          <p class="shadow-hint" style="margin-top:8px">Click a color to use it as your shadow tint source.</p>
        </div>

        <div id="handoff-empty">
          <p class="shadow-hint">No palette loaded yet.</p>
          <a href="/palette/" class="btn" style="margin-top:10px;display:inline-flex">
            <svg viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" />
              <path d="M12 8v8M8 12h8" />
            </svg>
            Go to Palette Generator
          </a>
        </div>

        <div id="custom-color-row" style="display:none; margin-top:12px">
          <label class="shadow-control-label">Shadow color</label>
          <div class="picker-controls">
            <div class="color-swatch-btn">
              <div class="color-swatch-preview" id="custom-preview" style="background:#1e1b4b"></div>
              <input type="color" value="#1e1b4b" id="custom-picker" aria-label="Shadow base color">
            </div>
            <input type="text" class="hex-input" value="#1e1b4b" id="custom-hex" maxlength="7" spellcheck="false"
              aria-label="Shadow hex">
          </div>
        </div>
      </div>

      <!-- Controls card -->
      <div class="picker-card">
        <div class="picker-card-header">
          <span class="picker-card-title">Shadow controls</span>
        </div>

        <label class="shadow-control-label">Tint intensity</label>
        <div class="shadow-slider-row">
          <input type="range" class="slider" min="0" max="100" value="40" id="tint-slider" oninput="updateAll()">
          <span class="shadow-slider-val" id="tint-val">40%</span>
        </div>

        <label class="shadow-control-label">Base opacity</label>
        <div class="shadow-slider-row">
          <input type="range" class="slider" min="3" max="55" value="16" id="opacity-slider" oninput="updateAll()">
          <span class="shadow-slider-val" id="opacity-val">16%</span>
        </div>

        <label class="shadow-control-label" style="margin-top:16px">Light direction</label>
        <div class="tabs" style="margin-bottom:12px">
          <button class="tab-btn active" id="light-above" onclick="setLight('above',this)">Above</button>
          <button class="tab-btn" id="light-angled" onclick="setLight('angled',this)">Angled</button>
          <button class="tab-btn" id="light-flat" onclick="setLight('flat',this)">Flat</button>
        </div>

        <label class="shadow-control-label">Scale stops</label>
        <div class="swatch-count-control">
          <button class="swatch-count-btn" onclick="setStopCount(stopCount - 1)" aria-label="Fewer stops">−</button>
          <input type="number" id="stop-count" min="3" max="6" value="5" aria-label="Number of shadow stops"
            onchange="setStopCount(this.value)" oninput="setStopCount(this.value)">
          <button class="swatch-count-btn" onclick="setStopCount(stopCount + 1)" aria-label="More stops">+</button>
        </div>
      </div>

    </div><!-- /#controls-grid -->

    <!-- ── RIGHT COLUMN: preview + tokens ─────────────── -->
    <div class="grad-main">
      <div class="shadow-scroll">
        <div class="scales-header">
          <span class="scales-header-label">Preview &amp; tokens</span>
          <div class="shadow-surface-toggle tabs">
            <button class="tab-btn active" id="surface-both" onclick="setSurface('both')">Both</button>
            <button class="tab-btn" id="surface-light" onclick="setSurface('light')">Light</button>
            <button class="tab-btn" id="surface-dark" onclick="setSurface('dark')">Dark</button>
          </div>
        </div>

        <!-- Dual surface preview -->
        <div class="shadow-surfaces" id="shadow-surfaces">
          <div class="shadow-surface shadow-surface--light" id="surface-light-panel">
            <span class="shadow-surface-label">Light surface</span>
            <div class="shadow-preview-cards" id="light-cards"></div>
          </div>
          <div class="shadow-surface shadow-surface--dark" id="surface-dark-panel">
            <span class="shadow-surface-label">Dark surface</span>
            <div class="shadow-preview-cards" id="dark-cards"></div>
          </div>
        </div>

        <!-- Token rows -->
        <div class="scales-header" style="margin-top:24px">
          <span class="scales-header-label">CSS tokens</span>
        </div>
        <div class="shadow-tokens" id="token-rows"></div>

        <section class="tool-seo-section" aria-labelledby="shadow-seo-title">
          <h2 id="shadow-seo-title">Create A More Useful Elevation System</h2>
          <p>This shadow and elevation tool helps you move beyond random box-shadow values by building a semantic depth
            scale. You can compare shadows on light and dark surfaces, tint them from your palette, and create a set
            that feels more intentional across cards, overlays, dropdowns, and modal layers.</p>
          <p>It is a practical fit for teams building design systems or product UI where consistency matters. If you
            need an elevation token generator for CSS or Figma, this page gives you a structured way to define depth and
            export it into a reusable system.</p>
        </section>
      </div><!-- /.shadow-scroll -->
    </div><!-- /.grad-main -->

  </div><!-- /.workspace -->

</main>
</div>

<!-- ── Export modal (mirrors palette tool) ───────────────── -->
<div class="export-modal" id="export-modal">
  <div class="export-modal-backdrop" onclick="closeExportModal()"></div>
  <div class="export-modal-box">
    <div class="export-modal-header">
      <div class="tabs">
        <button class="tab-btn active" id="tab-css" onclick="switchTab('css')">CSS variables</button>
        <button class="tab-btn" id="tab-json" onclick="switchTab('json')">Figma JSON</button>
      </div>
      <div class="export-modal-actions">
        <button class="btn" onclick="copyOutput()">
          <svg viewBox="0 0 24 24">
            <rect x="9" y="9" width="13" height="13" rx="2" />
            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
          </svg>
          <span id="copy-label">Copy</span>
        </button>
        <button class="export-modal-close" onclick="closeExportModal()">×</button>
      </div>
    </div>
    <div class="export-modal-body">
      <pre class="export-modal-code" id="output"></pre>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="/assets/color-math.js?v=<?= APP_VERSION ?>"></script>
<script>
  // ─────────────────────────────────────────────────────────────
  //  SHADOW & ELEVATION TOOL
  //  Reads saved palettes from localStorage (oklch-palettes) just
  //  like the picker-palette-handoff pattern in the palette tool.
  // ─────────────────────────────────────────────────────────────

  const SHADOW_DRAFT_KEY = 'oklch-shadow-draft';
  const SHADOW_STORAGE_KEY = 'oklch-shadows';
  const PALETTE_KEY = 'oklch-palettes';

  // ── Shadow level definitions ──────────────────────────────────
  const ALL_LEVELS = [
    { name: 'shadow-xs', label: 'XS', use: 'Chips, subtle lift', blur: 2, spread: 0, yBase: 1 },
    { name: 'shadow-sm', label: 'SM', use: 'Buttons, inputs', blur: 6, spread: 0, yBase: 2 },
    { name: 'shadow-md', label: 'MD', use: 'Dropdowns, popovers', blur: 16, spread: -1, yBase: 4 },
    { name: 'shadow-lg', label: 'LG', use: 'Modals, drawers', blur: 32, spread: -2, yBase: 8 },
    { name: 'shadow-xl', label: 'XL', use: 'Full overlays', blur: 64, spread: -4, yBase: 16 },
    { name: 'shadow-2xl', label: '2XL', use: 'Bottom sheets', blur: 96, spread: -6, yBase: 24 },
  ];

  // ── State ─────────────────────────────────────────────────────
  let shadowMode = 'tinted';   // 'tinted' | 'custom' | 'black'
  let lightDir = 'above';    // 'above' | 'angled' | 'flat'
  let stopCount = 5;
  let currentTab = 'css';
  let surfaceMode = 'both';     // 'both' | 'light' | 'dark'
  let customHex = '#1e1b4b';

  // Active palette colours (loaded from localStorage)
  let paletteColors = [];  // [{ name, hex, scale:[] }]
  let activePalIndex = 0;   // which colour in palette is the shadow tint source

  let _draftTimer;

  // ─────────────────────────────────────────────────────────────
  //  PALETTE HANDOFF — load from oklch-palettes (saved palettes)
  // ─────────────────────────────────────────────────────────────
  function loadPaletteFromStorage() {
    try {
      const all = JSON.parse(localStorage.getItem(PALETTE_KEY) || '[]');
      if (!all.length) return false;
      // Use the most recently saved palette
      const latest = all[all.length - 1];
      if (!latest.colors || !latest.colors.length) return false;
      paletteColors = latest.colors.map(c => ({
        name: c.name,
        hex: c.hex,
        scale: genPaletteStrip(c.hex),
      }));
      return true;
    } catch (_) { return false; }
  }

  // Generate a 9-stop strip for display (50→900)
  function genPaletteStrip(hex) {
    const stops = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
    return genScaleWithStops(hex, stops);
  }

  // ─────────────────────────────────────────────────────────────
  //  SHADOW COLOUR MATH
  // ─────────────────────────────────────────────────────────────
  function getShadowRgb() {
    if (shadowMode === 'black') return [0, 0, 0];
    if (shadowMode === 'custom') return hexToRgb(customHex);
    // tinted — use the 800-stop of the active palette colour
    if (!paletteColors.length) return [0, 0, 0];
    const strip = paletteColors[activePalIndex].scale;
    const dark = strip[strip.length - 2] || strip[strip.length - 1]; // ~800 stop
    const tint = parseInt(document.getElementById('tint-slider').value) / 100;
    const base = hexToRgb(dark);
    return [
      Math.round(base[0] * tint),
      Math.round(base[1] * tint),
      Math.round(base[2] * tint),
    ];
  }

  function buildShadowValue(level, index) {
    const opacityBase = parseInt(document.getElementById('opacity-slider').value) / 100;
    // Gently ramp opacity up with level depth
    const opacity = Math.min(opacityBase + index * 0.025, 0.55).toFixed(3);
    const rgb = getShadowRgb();
    const color = `rgba(${rgb[0]},${rgb[1]},${rgb[2]},${opacity})`;

    let x = 0;
    let y = level.yBase;
    if (lightDir === 'angled') x = Math.round(level.yBase * 0.4);
    if (lightDir === 'flat') y = 0;

    return `${x}px ${y}px ${level.blur}px ${level.spread}px ${color}`;
  }

  // ─────────────────────────────────────────────────────────────
  //  RENDER
  // ─────────────────────────────────────────────────────────────
  function renderHandoff() {
    const hasPalette = paletteColors.length > 0;
    document.getElementById('handoff-active').style.display = hasPalette ? '' : 'none';
    document.getElementById('handoff-empty').style.display = hasPalette ? 'none' : '';

    if (!hasPalette) return;

    // Chips
    const chipRow = document.getElementById('palette-chips');
    chipRow.innerHTML = '';
    paletteColors.forEach((col, i) => {
      const chip = document.createElement('button');
      chip.className = 'shadow-palette-chip' + (i === activePalIndex ? ' active' : '');
      chip.style.background = col.hex;
      chip.title = col.name;
      chip.setAttribute('aria-label', col.name);
      chip.onclick = () => { activePalIndex = i; renderHandoff(); updateAll(); };
      chipRow.appendChild(chip);
    });

    // Swatch strip for active colour
    const strip = document.getElementById('palette-strip');
    strip.innerHTML = '';
    const activePal = paletteColors[activePalIndex];
    activePal.scale.forEach(hex => {
      const s = document.createElement('div');
      s.className = 'shadow-swatch-stop';
      s.style.background = hex;
      strip.appendChild(s);
    });

    // Show/hide custom colour row
    document.getElementById('custom-color-row').style.display =
      shadowMode === 'custom' ? '' : 'none';
  }

  function renderPreviews() {
    const levels = ALL_LEVELS.slice(0, stopCount);

    ['light', 'dark'].forEach(mode => {
      const container = document.getElementById(mode + '-cards');
      container.innerHTML = '';
      levels.forEach((level, i) => {
        const shadow = buildShadowValue(level, i);
        const card = document.createElement('div');
        card.className = 'shadow-preview-card shadow-preview-card--' + mode;
        card.style.boxShadow = shadow;
        card.style.marginBottom = i < levels.length - 1 ? Math.round(10 * Math.pow(1.6, i)) + 'px' : '0';
        card.innerHTML = `
        <div class="shadow-card-meta">
          <span class="shadow-card-label">${level.label}</span>
          <span class="shadow-card-use">${level.use}</span>
        </div>
        <span class="shadow-card-blur">${level.blur}px blur</span>`;
        container.appendChild(card);
      });
    });
  }

  function renderTokens() {
    const levels = ALL_LEVELS.slice(0, stopCount);
    const container = document.getElementById('token-rows');
    container.innerHTML = '';

    levels.forEach((level, i) => {
      const shadow = buildShadowValue(level, i);
      const row = document.createElement('div');
      row.className = 'shadow-token-row';

      // Mini shadow swatch
      const swatch = document.createElement('div');
      swatch.className = 'shadow-token-swatch';
      swatch.style.boxShadow = shadow;

      row.innerHTML = `
      <span class="shadow-token-name">--${level.name}</span>
      <span class="shadow-token-value">${shadow}</span>`;
      row.appendChild(swatch);

      // Copy on click
      row.style.cursor = 'pointer';
      row.onclick = () => copyText(`--${level.name}: ${shadow};`, `--${level.name} copied`);

      container.appendChild(row);
    });
  }

  function updateAll() {
    // Sync slider readouts
    document.getElementById('tint-val').textContent = document.getElementById('tint-slider').value + '%';
    document.getElementById('opacity-val').textContent = document.getElementById('opacity-slider').value + '%';

    renderPreviews();
    renderTokens();
    updateOutput();
    persistDraft();
  }

  // ─────────────────────────────────────────────────────────────
  //  SURFACE TOGGLE
  // ─────────────────────────────────────────────────────────────
  function setSurface(mode) {
    surfaceMode = mode;
    ['both', 'light', 'dark'].forEach(m => {
      document.getElementById('surface-' + m).classList.toggle('active', m === mode);
    });
    const lightPanel = document.getElementById('surface-light-panel');
    const darkPanel = document.getElementById('surface-dark-panel');
    const surfaces = document.getElementById('shadow-surfaces');

    lightPanel.style.display = (mode === 'dark') ? 'none' : '';
    darkPanel.style.display = (mode === 'light') ? 'none' : '';
    surfaces.classList.toggle('shadow-surfaces--single', mode !== 'both');
  }

  // ─────────────────────────────────────────────────────────────
  //  MODE / CONTROLS
  // ─────────────────────────────────────────────────────────────
  function setShadowMode(mode) {
    shadowMode = mode;
    ['tinted', 'custom', 'black'].forEach(m => {
      document.getElementById('mode-' + m).classList.toggle('active', m === mode);
    });
    document.getElementById('custom-color-row').style.display =
      mode === 'custom' ? '' : 'none';

    // Tint slider only meaningful in tinted mode
    document.getElementById('tint-slider').disabled = mode !== 'tinted';
    document.getElementById('tint-val').style.opacity = mode === 'tinted' ? '1' : '0.35';

    updateAll();
  }

  function setLight(dir, btn) {
    lightDir = dir;
    ['above', 'angled', 'flat'].forEach(d => {
      document.getElementById('light-' + d).classList.toggle('active', d === dir);
    });
    updateAll();
  }

  function setStopCount(n) {
    n = Math.max(3, Math.min(6, parseInt(n) || 5));
    stopCount = n;
    document.getElementById('stop-count').value = n;
    updateAll();
  }

  // ─────────────────────────────────────────────────────────────
  //  EXPORT / OUTPUT
  // ─────────────────────────────────────────────────────────────
  function genCSS() {
    const levels = ALL_LEVELS.slice(0, stopCount);
    const lines = [':root {'];
    levels.forEach((level, i) => {
      lines.push(`  --${level.name}: ${buildShadowValue(level, i)};`);
    });
    lines.push('}');
    return lines.join('\n');
  }

  function genJSON() {
    const levels = ALL_LEVELS.slice(0, stopCount);
    const obj = { shadows: {} };
    levels.forEach((level, i) => {
      obj.shadows[level.name] = {
        value: buildShadowValue(level, i),
        type: 'boxShadow',
      };
    });
    return JSON.stringify(obj, null, 2);
  }

  function hiCSS(code) {
    return code
      .replace(/(--[\w-]+)/g, '<span class="token-key">$1</span>')
      .replace(/(rgba\([^)]+\))/g, '<span class="token-val">$1</span>')
      .replace(/([{}:;])/g, '<span class="token-punct">$1</span>');
  }
  function hiJSON(code) {
    return code
      .replace(/(\"[\w-]+\")\s*:/g, '<span class="token-key">$1</span>:')
      .replace(/:\s*(\".*?\")/g, ': <span class="token-val">$1</span>')
      .replace(/([{}\[\],])/g, '<span class="token-punct">$1</span>');
  }

  function updateOutput() {
    const raw = currentTab === 'css' ? genCSS() : genJSON();
    document.getElementById('output').innerHTML =
      currentTab === 'css' ? hiCSS(raw) : hiJSON(raw);
  }

  function switchTab(tab) {
    currentTab = tab;
    document.getElementById('tab-css').classList.toggle('active', tab === 'css');
    document.getElementById('tab-json').classList.toggle('active', tab === 'json');
    updateOutput();
  }

  function openExportModal() {
    updateOutput();
    document.getElementById('export-modal').classList.add('open');
  }
  function closeExportModal() {
    document.getElementById('export-modal').classList.remove('open');
  }

  function copyOutput() {
    const raw = currentTab === 'css' ? genCSS() : genJSON();
    const lbl = document.getElementById('copy-label');
    copyText(raw, 'Copied!');
    lbl.textContent = 'Copied!';
    setTimeout(() => lbl.textContent = 'Copy', 2000);
    recordExport('shadow', currentTab === 'css' ? 'CSS variables' : 'Figma JSON', 'shadow scale', raw);
  }

  // ─────────────────────────────────────────────────────────────
  //  SAVE / DRAFT
  // ─────────────────────────────────────────────────────────────
  function saveShadowScale() {
    const all = JSON.parse(localStorage.getItem(SHADOW_STORAGE_KEY) || '[]');
    const levels = ALL_LEVELS.slice(0, stopCount);
    all.push({
      id: 's-' + Date.now(),
      name: 'shadow scale',
      savedAt: Date.now(),
      tokens: levels.map((level, i) => ({
        name: level.name,
        value: buildShadowValue(level, i),
      })),
    });
    localStorage.setItem(SHADOW_STORAGE_KEY, JSON.stringify(all));
    const lbl = document.getElementById('save-label');
    lbl.textContent = 'Saved!';
    setTimeout(() => lbl.textContent = 'Save scale', 2000);
    showToast('Shadow scale saved');
  }

  function persistDraft() {
    clearTimeout(_draftTimer);
    _draftTimer = setTimeout(() => {
      localStorage.setItem(SHADOW_DRAFT_KEY, JSON.stringify({
        shadowMode,
        lightDir,
        stopCount,
        customHex,
        activePalIndex,
        tint: parseInt(document.getElementById('tint-slider').value),
        opacity: parseInt(document.getElementById('opacity-slider').value),
      }));
    }, 300);
  }

  function loadDraft() {
    try {
      const d = JSON.parse(localStorage.getItem(SHADOW_DRAFT_KEY));
      if (!d) return;
      if (d.shadowMode) shadowMode = d.shadowMode;
      if (d.lightDir) lightDir = d.lightDir;
      if (d.stopCount) stopCount = d.stopCount;
      if (d.customHex) customHex = d.customHex;
      if (d.activePalIndex !== undefined) activePalIndex = d.activePalIndex;
      if (d.tint !== undefined) document.getElementById('tint-slider').value = d.tint;
      if (d.opacity !== undefined) document.getElementById('opacity-slider').value = d.opacity;
    } catch (_) { }
  }

  // ─────────────────────────────────────────────────────────────
  //  CUSTOM COLOUR PICKER wiring
  // ─────────────────────────────────────────────────────────────
  function initCustomPicker() {
    const picker = document.getElementById('custom-picker');
    const hexEl = document.getElementById('custom-hex');
    const prev = document.getElementById('custom-preview');

    picker.addEventListener('input', e => {
      customHex = e.target.value;
      hexEl.value = customHex;
      prev.style.background = customHex;
      updateAll();
    });
    hexEl.addEventListener('input', e => {
      const v = e.target.value.trim();
      if (/^#[0-9a-fA-F]{6}$/.test(v)) {
        customHex = v;
        picker.value = v;
        prev.style.background = v;
        updateAll();
      }
    });
    hexEl.addEventListener('blur', e => {
      if (!/^#[0-9a-fA-F]{6}$/.test(e.target.value.trim())) hexEl.value = picker.value;
    });
  }

  // ─────────────────────────────────────────────────────────────
  //  INIT
  // ─────────────────────────────────────────────────────────────
  (function init() {
    loadDraft();
    loadPaletteFromStorage();

    // Sync UI state to loaded draft values
    document.getElementById('stop-count').value = stopCount;
    document.getElementById('tint-val').textContent = document.getElementById('tint-slider').value + '%';
    document.getElementById('opacity-val').textContent = document.getElementById('opacity-slider').value + '%';

    ['tinted', 'custom', 'black'].forEach(m => {
      document.getElementById('mode-' + m).classList.toggle('active', m === shadowMode);
    });
    ['above', 'angled', 'flat'].forEach(d => {
      document.getElementById('light-' + d).classList.toggle('active', d === lightDir);
    });

    document.getElementById('tint-slider').disabled = shadowMode !== 'tinted';
    document.getElementById('tint-val').style.opacity = shadowMode === 'tinted' ? '1' : '0.35';

    initCustomPicker();
    renderHandoff();
    updateAll();
  })();

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeExportModal();
  });
</script>

<?php require '../includes/footer.php'; ?>