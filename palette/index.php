<?php
$pageTitle = 'Palette Generator — ONE design';
$activePage = 'palette';
$shellClass = 'full-height';
require '../includes/header.php';
?>

<style>
  /* Left pickers inherit the shared .grad-panel width; only internal styling here. */
  .palette-page .grad-panel .pickers-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  /* Mode tabs fill the panel width */
  .palette-page .grad-panel .tabs {
    width: 100%;
  }

  .palette-page .grad-panel .tab-btn {
    flex: 1;
    text-align: center;
  }

  /* Right side: swatch scales scroll area */
  .palette-page .palette-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 24px 28px 40px;
  }
</style>

<main class="panel palette-page">

  <div class="topstrip">
    <span class="topstrip-title">Palette <em>generator</em></span>
    <div class="topstrip-actions">
      <button class="btn" onclick="openExportModal()">
        <svg viewBox="0 0 24 24">
          <rect x="9" y="9" width="13" height="13" rx="2" />
          <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
        </svg>
        Export
      </button>
      <button class="btn btn-primary" onclick="savePalette()">
        <svg viewBox="0 0 24 24">
          <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" />
        </svg>
        <span id="save-label">Save palette</span>
      </button>
    </div>
  </div>

  <div class="workspace">

    <!-- ── LEFT: mode + color pickers (flush to sidebar) ── -->
    <div class="grad-panel">
      <div class="grad-section">
        <label class="field-label">Mode</label>
        <div class="tabs">
          <button class="tab-btn active" id="mode-oklch" onclick="setMode('oklch')">OKLCH</button>
          <button class="tab-btn" id="mode-tintshade" onclick="setMode('tint-shade')">Tint / Shade</button>
        </div>
      </div>
      <div class="pickers-grid" id="pickers-grid"></div>
    </div>

    <!-- ── RIGHT: swatch scales ──────────────────────── -->
    <div class="grad-main">
      <div class="palette-scroll">
        <div class="scales-header">
          <span class="scales-header-label">Swatches</span>
          <div class="swatch-count-control">
            <button class="swatch-count-btn" onclick="setStopCount(stopCount - 1)"
              aria-label="Fewer swatches">−</button>
            <input type="number" id="stop-count" min="4" max="14" value="10" aria-label="Number of swatches"
              onchange="setStopCount(this.value)" oninput="setStopCount(this.value)">
            <button class="swatch-count-btn" onclick="setStopCount(stopCount + 1)" aria-label="More swatches">+</button>
          </div>
        </div>
        <div class="scales-section" id="scales-section"></div>
      </div>
    </div>

  </div><!-- /.workspace -->

</main>
</div>

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

<div class="presets-modal" id="presets-modal">
  <div class="presets-modal-backdrop" onclick="closePresetsModal()"></div>
  <div class="presets-modal-box">
    <div class="presets-modal-header">
      <span>Color library</span>
      <button class="export-modal-close" onclick="closePresetsModal()">×</button>
    </div>
    <div class="presets-modal-grid" id="presets-grid"></div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="/assets/color-math.js?v=<?= APP_VERSION ?>"></script>
<script>
  const MAX_COLORS = 8;
  const ADD_DEFAULTS = ['#16a34a', '#f59e0b', '#8b5cf6', '#ec4899'];
  const ADD_NAMES = ['tertiary', 'quaternary'];
  const DRAFT_KEY = 'oklch-palette-draft';
  let _draftTimer;

  let colors = [
    { id: 'c0', name: 'primary', hex: '#2563eb', scale: [] },
    { id: 'c1', name: 'secondary', hex: '#e11d48', scale: [] },
  ];
  let nextId = 2, currentTab = 'css', scaleMode = 'oklch', stopCount = 10;

  function getActiveStops() {
    return ALL_STOPS.slice(0, stopCount).slice().sort((a, b) => a - b);
  }

  // ── TINT / SHADE SCALE ───────────────────────────────
  const TINT_AMOUNTS = { 25: .97, 50: .95, 75: .92, 100: .90, 200: .75, 300: .55, 400: .32 };
  const SHADE_AMOUNTS = { 600: .20, 700: .40, 800: .60, 900: .78, 950: .86, 975: .92 };

  function genScaleTintShade(hex, stops) {
    const [r, g, b] = hexToRgb(hex);
    return stops.map(stop => {
      if (stop === 500) return hex;
      if (stop < 500) {
        const t = TINT_AMOUNTS[stop];
        return rgbToHex(Math.round(r + (255 - r) * t), Math.round(g + (255 - g) * t), Math.round(b + (255 - b) * t));
      }
      const t = SHADE_AMOUNTS[stop];
      return rgbToHex(Math.round(r * (1 - t)), Math.round(g * (1 - t)), Math.round(b * (1 - t)));
    });
  }

  function getScale(hex) {
    const stops = getActiveStops();
    if (scaleMode === 'tint-shade') return genScaleTintShade(hex, stops);
    return genScaleWithStops(hex, stops);
  }

  function setMode(mode) {
    scaleMode = mode;
    document.getElementById('mode-oklch').classList.toggle('active', mode === 'oklch');
    document.getElementById('mode-tintshade').classList.toggle('active', mode === 'tint-shade');
    const _badge = document.getElementById('mode-badge');
    if (_badge) _badge.textContent = mode === 'oklch' ? 'oklch color theory' : 'tint / shade';
    const stops = getActiveStops();
    colors.forEach(col => {
      col.scale = getScale(col.hex).map((h, i) => {
        const [L, C, H] = rgbToOklch(...hexToRgb(h));
        return { stop: stops[i], hex: h, L, C, H };
      });
      renderSwatches(col.id, col.scale);
    });
    updateOutput();
  }

  function setStopCount(n) {
    n = Math.max(4, Math.min(14, parseInt(n) || 10));
    stopCount = n;
    document.getElementById('stop-count').value = n;
    const stops = getActiveStops();
    colors.forEach(col => {
      col.scale = getScale(col.hex).map((h, i) => {
        const [L, C, H] = rgbToOklch(...hexToRgb(h));
        return { stop: stops[i], hex: h, L, C, H };
      });
      renderSwatches(col.id, col.scale);
    });
    updateOutput();
  }

  function toSlug(s) { return s.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || 'color'; }

  function renderPickers() {
    const grid = document.getElementById('pickers-grid');
    grid.innerHTML = '';
    colors.forEach((col, idx) => {
      const card = document.createElement('div');
      card.className = 'picker-card fade-in'; card.dataset.id = col.id;
      card.style.animationDelay = (idx * .06) + 's';
      card.innerHTML = `
      ${colors.length > 1 ? `<button class="remove-btn" data-remove="${col.id}" aria-label="Remove ${col.name}">
        <svg viewBox="0 0 10 10"><line x1="2" y1="2" x2="8" y2="8"/><line x1="8" y1="2" x2="2" y2="8"/></svg>
      </button>`: ''}
      <div class="picker-card-header">
        <input class="name-input" type="text" value="${col.name}" maxlength="24" spellcheck="false"
          aria-label="Color name" data-name-id="${col.id}">
      </div>
      <div class="picker-controls">
        <div class="color-swatch-btn">
          <div class="color-swatch-preview" id="preview-${col.id}" style="background:${col.hex}"></div>
          <input type="color" value="${col.hex}" id="picker-${col.id}" aria-label="${col.name} color">
        </div>
        <input type="text" class="hex-input" value="${col.hex}" id="hex-${col.id}" maxlength="7" spellcheck="false" aria-label="${col.name} hex">
      </div>
      <span class="picker-oklch" id="oklch-${col.id}"></span>`;
      grid.appendChild(card);

      if (colors.length > 1) card.querySelector('[data-remove]').addEventListener('click', () => removeColor(col.id));
      const nameEl = card.querySelector('[data-name-id]');
      nameEl.addEventListener('change', () => renameColor(col.id, nameEl.value));
      nameEl.addEventListener('blur', () => renameColor(col.id, nameEl.value));
      const pickerEl = card.querySelector('#picker-' + col.id);
      const hexEl = card.querySelector('#hex-' + col.id);
      pickerEl.addEventListener('input', e => { hexEl.value = e.target.value; setColor(col.id, e.target.value); });
      hexEl.addEventListener('input', e => {
        const v = e.target.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(v)) { pickerEl.value = v; setColor(col.id, v); }
      });
      hexEl.addEventListener('blur', e => { if (!/^#[0-9a-fA-F]{6}$/.test(e.target.value.trim())) hexEl.value = pickerEl.value; });
      updateOklchBadge(col.id, col.hex);
    });

    const actionsCard = document.createElement('div');
    actionsCard.className = 'picker-actions-card';
    actionsCard.innerHTML = (colors.length < MAX_COLORS ? `
      <button class="picker-action picker-action--add" onclick="addColor()">
        <div class="add-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
        Add color
      </button>` : '') + `
      <button class="picker-action picker-action--lib" onclick="openPresetsModal()">
        <div class="add-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="4" height="4" rx=".75"/><rect x="10" y="3" width="4" height="4" rx=".75"/><rect x="17" y="3" width="4" height="4" rx=".75"/><rect x="3" y="10" width="4" height="4" rx=".75"/><rect x="10" y="10" width="4" height="4" rx=".75"/><rect x="17" y="10" width="4" height="4" rx=".75"/></svg></div>
        Color library
      </button>`;
    grid.appendChild(actionsCard);
  }

  function renderScales() {
    const section = document.getElementById('scales-section');
    section.innerHTML = '';
    colors.forEach((col, idx) => {
      const grp = document.createElement('div');
      grp.className = 'scale-group fade-in'; grp.dataset.id = col.id;
      grp.style.animationDelay = (idx * .07) + 's';
      grp.innerHTML = `
      <div class="scale-group-header">
        <div class="scale-dot" id="dot-${col.id}" style="background:${col.hex}"></div>
        <span class="scale-name-display" id="scalelabel-${col.id}">${col.name} scale</span>
      </div>
      <div class="swatches" id="swatches-${col.id}"></div>`;
      section.appendChild(grp);
      renderSwatches(col.id, col.scale);
    });
  }

  function renderSwatches(id, scale) {
    const el = document.getElementById('swatches-' + id);
    if (!el) return;
    el.innerHTML = '';
    scale.forEach(({ stop, hex }) => {
      const tc = textColorFor(hex);
      const div = document.createElement('div');
      div.className = 'swatch'; div.style.background = hex;
      div.innerHTML = `<span class="swatch-copy-hint" style="color:${tc}">copy</span>
      <span class="swatch-stop" style="color:${tc}">${stop}</span>
      <span class="swatch-hex" style="color:${tc}">${hex}</span>`;
      div.addEventListener('click', () => {
        copyText(hex, hex + ' copied');
        div.classList.add('copied');
        setTimeout(() => div.classList.remove('copied'), 900);
      });
      el.appendChild(div);
    });
  }

  function setColor(id, hex) {
    const col = colors.find(c => c.id === id); if (!col) return;
    col.hex = hex;
    const stops = getActiveStops();
    col.scale = getScale(hex).map((h, i) => {
      const [L, C, H] = rgbToOklch(...hexToRgb(h));
      return { stop: stops[i], hex: h, L, C, H };
    });
    const prev = document.getElementById('preview-' + id); if (prev) prev.style.background = hex;
    const dot = document.getElementById('dot-' + id); if (dot) dot.style.background = hex;
    updateOklchBadge(id, hex);
    renderSwatches(id, col.scale);
    updateOutput();
  }

  function updateOklchBadge(id, hex) {
    const [L, C, H] = rgbToOklch(...hexToRgb(hex));
    const el = document.getElementById('oklch-' + id);
    if (el) el.textContent = `oklch(${(L * 100).toFixed(1)}% ${C.toFixed(3)} ${H.toFixed(0)}°)`;
  }

  function renameColor(id, raw) {
    const col = colors.find(c => c.id === id); if (!col) return;
    const name = (raw || '').trim() || col.name;
    if (name === col.name) return;
    col.name = name;
    const lbl = document.getElementById('scalelabel-' + id); if (lbl) lbl.textContent = name + ' scale';
    updateOutput();
  }

  function addColor() {
    if (colors.length >= MAX_COLORS) return;
    const id = 'c' + nextId++;
    const hex = ADD_DEFAULTS[colors.length % ADD_DEFAULTS.length];
    const name = ADD_NAMES[colors.length - 2] || 'color-' + colors.length;
    const stops = getActiveStops();
    const scale = getScale(hex).map((h, i) => { const [L, C, H] = rgbToOklch(...hexToRgb(h)); return { stop: stops[i], hex: h, L, C, H }; });
    colors.push({ id, name, hex, scale });
    renderPickers();
    const section = document.getElementById('scales-section');
    const grp = document.createElement('div');
    grp.className = 'scale-group fade-in'; grp.dataset.id = id;
    grp.innerHTML = `<div class="scale-group-header"><div class="scale-dot" id="dot-${id}" style="background:${hex}"></div><span class="scale-name-display" id="scalelabel-${id}">${name} scale</span></div><div class="swatches" id="swatches-${id}"></div>`;
    section.appendChild(grp);
    renderSwatches(id, colors[colors.length - 1].scale);
    updateOutput();
  }

  function removeColor(id) {
    if (colors.length <= 1) return;
    colors = colors.filter(c => c.id !== id);
    const grp = document.querySelector(`.scale-group[data-id="${id}"]`); if (grp) grp.remove();
    renderPickers(); updateOutput();
  }

  function hiCSS(code) {
    return code
      .replace(/(--[\w-]+)/g, '<span class="token-key">$1</span>')
      .replace(/(oklch\([^)]+\))/g, '<span class="token-val">$1</span>')
      .replace(/([{}:;])/g, '<span class="token-punct">$1</span>');
  }
  function hiJSON(code) {
    return code
      .replace(/("[\w-]+")\s*:/g, '<span class="token-key">$1</span>:')
      .replace(/:\s*(".*?")/g, ': <span class="token-val">$1</span>')
      .replace(/([{}\[\],])/g, '<span class="token-punct">$1</span>');
  }

  function genCSS() {
    const lines = [':root {'];
    colors.forEach(col => {
      const slug = toSlug(col.name);
      col.scale.forEach(({ stop, L, C, H }) => {
        lines.push(`  --color-${slug}-${stop}: oklch(${(L * 100).toFixed(1)}% ${C.toFixed(3)} ${H.toFixed(1)});`);
      });
      lines.push('');
    });
    lines.push('}'); return lines.join('\n');
  }

  function genJSON() {
    const obj = { colors: {} };
    colors.forEach(col => {
      const slug = toSlug(col.name);
      obj.colors[slug] = {};
      col.scale.forEach(({ stop, hex }) => {
        obj.colors[slug][stop] = { value: hex, type: 'color' };
      });
    });
    return JSON.stringify(obj, null, 2);
  }

  function persistDraft() {
    clearTimeout(_draftTimer);
    _draftTimer = setTimeout(() => {
      localStorage.setItem(DRAFT_KEY, JSON.stringify({
        colors: colors.map(c => ({ name: c.name, hex: c.hex })),
        tab: currentTab,
        mode: scaleMode,
        stopCount,
      }));
    }, 300);
  }

  function updateOutput() {
    const raw = currentTab === 'css' ? genCSS() : genJSON();
    document.getElementById('output').innerHTML = currentTab === 'css' ? hiCSS(raw) : hiJSON(raw);
    persistDraft();
  }

  function switchTab(tab) {
    currentTab = tab;
    document.getElementById('tab-css').classList.toggle('active', tab === 'css');
    document.getElementById('tab-json').classList.toggle('active', tab === 'json');
    updateOutput();
  }

  function copyOutput() {
    const raw = currentTab === 'css' ? genCSS() : genJSON();
    copyText(raw, 'Copied!');
    const lbl = document.getElementById('copy-label');
    lbl.textContent = 'Copied!'; setTimeout(() => lbl.textContent = 'Copy', 2000);
    recordExport('palette', currentTab === 'css' ? 'CSS variables' : 'Figma JSON', colors.map(c => c.name).join(' · '), raw);
  }

  // ── SAVE / LOAD ──────────────────────────────────────
  const STORAGE_KEY = 'oklch-palettes';

  function refreshSaveLabel() {
    const exists = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]').length > 0;
    const lbl = document.getElementById('save-label');
    if (lbl) lbl.textContent = exists ? 'Update palette' : 'Save palette';
  }

  function savePalette() {
    // Single-palette model: saving updates your one palette rather than stacking up new ones.
    const prev = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    const existed = prev.length > 0;
    const id = prev[0]?.id || ('p-' + Date.now());
    const name = colors.slice(0, 2).map(c => c.name).join(' · ');
    localStorage.setItem(STORAGE_KEY, JSON.stringify([{
      id, name, savedAt: Date.now(),
      colors: colors.map(c => ({ name: c.name, hex: c.hex }))
    }]));
    const lbl = document.getElementById('save-label');
    lbl.textContent = existed ? 'Updated!' : 'Saved!';
    setTimeout(refreshSaveLabel, 2000);
    showToast(existed ? 'Palette updated' : 'Palette saved');
  }

  // Init — check for picker handoff, ?load=<id>, or draft
  let _fromPicker = false, _fromPickerCount = 0;

  (function () {
    // ── Picker handoff ──────────────────────────────────
    const handoffRaw = localStorage.getItem('picker-palette-handoff');
    if (handoffRaw) {
      try {
        const hexes = JSON.parse(handoffRaw);
        if (Array.isArray(hexes) && hexes.length) {
          const names = ['primary', 'secondary', 'tertiary', 'quaternary'];
          colors = hexes.slice(0, MAX_COLORS).map((hex, i) => ({
            id: 'c' + i, name: names[i] || ('color-' + (i + 1)), hex, scale: []
          }));
          nextId = colors.length;
          _fromPicker = true;
          _fromPickerCount = colors.length;
        }
      } catch (_) { }
      localStorage.removeItem('picker-palette-handoff');
    }

    const id = new URLSearchParams(location.search).get('load');
    if (!_fromPicker && id) {
      const all = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
      const palette = all.find(p => p.id === id);
      if (palette && palette.colors.length >= 1) {
        colors = palette.colors.map((c, i) => ({ id: 'c' + i, name: c.name, hex: c.hex, scale: [] }));
        nextId = colors.length;
      }
    } else if (!_fromPicker) {
      try {
        const draft = JSON.parse(localStorage.getItem(DRAFT_KEY));
        if (draft?.colors?.length >= 1) {
          colors = draft.colors.map((c, i) => ({ id: 'c' + i, name: c.name, hex: c.hex, scale: [] }));
          nextId = colors.length;
          if (draft.tab) currentTab = draft.tab;
          if (draft.stopCount) stopCount = draft.stopCount;
          if (draft.mode) {
            scaleMode = draft.mode;
            document.getElementById('mode-oklch').classList.toggle('active', scaleMode === 'oklch');
            document.getElementById('mode-tintshade').classList.toggle('active', scaleMode === 'tint-shade');
            const _badge2 = document.getElementById('mode-badge');
            if (_badge2) _badge2.textContent = scaleMode === 'oklch' ? 'oklch color theory' : 'tint / shade';
          }
        }
      } catch (_) { }
    }
    const stops = getActiveStops();
    colors.forEach(c => {
      c.scale = getScale(c.hex).map((h, i) => {
        const [L, C, H] = rgbToOklch(...hexToRgb(h));
        return { stop: stops[i], hex: h, L, C, H };
      });
    });
    document.getElementById('stop-count').value = stopCount;
    renderPickers(); renderScales(); updateOutput();

    colors.forEach((col, i) => {
      const card = document.querySelector('.picker-card[data-id="' + col.id + '"]');
      if (!card) return;
      const rgb = hexToRgb(col.hex) || [255, 255, 255];
      card.style.setProperty('--glow-solid', 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',0.55)');
      card.style.setProperty('--glow-dim', 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',0.2)');
      setTimeout(() => card.classList.add('new-from-picker'), i * 120);
    });

    refreshSaveLabel();
  })();

  // Handle BFCache restoration (Safari/Firefox may restore page from cache on forward navigation)
  window.addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    const handoffRaw = localStorage.getItem('picker-palette-handoff');
    if (!handoffRaw) return;
    try {
      const hexes = JSON.parse(handoffRaw);
      if (Array.isArray(hexes) && hexes.length) {
        const names = ['primary', 'secondary', 'tertiary', 'quaternary'];
        colors = hexes.slice(0, MAX_COLORS).map((hex, i) => ({
          id: 'c' + i, name: names[i] || ('color-' + (i + 1)), hex, scale: []
        }));
        nextId = colors.length;
        localStorage.removeItem('picker-palette-handoff');
        const stops = getActiveStops();
        colors.forEach(c => {
          c.scale = getScale(c.hex).map((h, j) => {
            const [L, C, H] = rgbToOklch(...hexToRgb(h));
            return { stop: stops[j], hex: h, L, C, H };
          });
        });
        renderPickers(); renderScales(); updateOutput();
      }
    } catch (_) { }
  });

  function openExportModal() {
    updateOutput();
    document.getElementById('export-modal').classList.add('open');
  }
  function closeExportModal() {
    document.getElementById('export-modal').classList.remove('open');
  }

  // ── PRESETS ──────────────────────────────────────────
  const PRESETS = [
    { name: 'Gray', L: 0.649, C: 0, H: 0 },
    { name: 'Slate', L: 0.645, C: 0.018, H: 256 },
    { name: 'Red', L: 0.647, C: 0.176, H: 17 },
    { name: 'Blue', L: 0.629, C: 0.187, H: 252 },
    { name: 'Green', L: 0.623, C: 0.178, H: 145 },
    { name: 'Yellow', L: 0.725, C: 0.187, H: 91 },
    { name: 'Orange', L: 0.670, C: 0.185, H: 55 },
    { name: 'Purple', L: 0.637, C: 0.185, H: 295 },
    { name: 'Pink', L: 0.641, C: 0.185, H: 343 },
    { name: 'Cyan', L: 0.623, C: 0.178, H: 210 },
    { name: 'Teal', L: 0.618, C: 0.182, H: 180 },
    { name: 'Indigo', L: 0.632, C: 0.185, H: 275 },
    { name: 'Amber', L: 0.733, C: 0.194, H: 75 },
    { name: 'Lime', L: 0.703, C: 0.205, H: 120 },
    { name: 'Mint', L: 0.609, C: 0.192, H: 165 },
    { name: 'Tomato', L: 0.657, C: 0.183, H: 25 },
  ];

  function openPresetsModal() {
    renderPresetsGrid();
    document.getElementById('presets-modal').classList.add('open');
  }
  function closePresetsModal() {
    document.getElementById('presets-modal').classList.remove('open');
  }

  function renderPresetsGrid() {
    const grid = document.getElementById('presets-grid');
    grid.innerHTML = PRESETS.map(p => {
      const seedHex = oklchToHex(p.L, p.C, p.H);
      const swatches = genScaleWithStops(seedHex, [50, 200, 400, 600, 800, 950]);
      const swatchHtml = swatches.map(h => `<span class="preset-swatch" style="background:${h}"></span>`).join('');
      return `<button class="preset-card" onclick="loadPreset('${p.name}','${seedHex}')">
        <div class="preset-swatches">${swatchHtml}</div>
        <span class="preset-name">${p.name}</span>
      </button>`;
    }).join('');
  }

  function loadPreset(name, seedHex) {
    const id = 'c' + nextId++;
    const stops = getActiveStops();
    const scale = getScale(seedHex).map((h, i) => {
      const [L, C, H] = rgbToOklch(...hexToRgb(h));
      return { stop: stops[i], hex: h, L, C, H };
    });
    colors.push({ id, name: name.toLowerCase(), hex: seedHex, scale });
    closePresetsModal();
    renderPickers();
    renderScales();
    updateOutput();
  }

  document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeExportModal(); closePresetsModal(); } });
</script>

<?php require '../includes/footer.php'; ?>