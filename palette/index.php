<?php
$pageTitle = 'Palette Generator — ONE design';
$activePage = 'palette';
require '../includes/header.php';
?>

<main class="scrollable">

  <div class="topbar">
    <div class="topbar-greeting">
      <h2>Palette <em>generator</em></h2>
      <p>Pick your colors, get a full scale.</p>
    </div>
    <div class="topbar-right">
      <div class="swatch-count-control">
        <button class="swatch-count-btn" onclick="setStopCount(stopCount - 1)" aria-label="Fewer swatches">−</button>
        <input type="number" id="stop-count" min="4" max="14" value="10" aria-label="Number of swatches"
          onchange="setStopCount(this.value)" oninput="setStopCount(this.value)">
        <button class="swatch-count-btn" onclick="setStopCount(stopCount + 1)" aria-label="More swatches">+</button>
        <span class="swatch-count-label">swatches</span>
      </div>
      <div class="tabs">
        <button class="tab-btn active" id="mode-oklch" onclick="setMode('oklch')">OKLCH</button>
        <button class="tab-btn" id="mode-tintshade" onclick="setMode('tint-shade')">Tint / Shade</button>
      </div>
      <button class="btn" onclick="savePalette()">
        <svg viewBox="0 0 24 24">
          <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" />
        </svg>
        <span id="save-label">Save palette</span>
      </button>
      <div class="badge">
        <span class="badge-dot"></span>
        <span id="mode-badge">oklch color theory</span>
      </div>
    </div>
  </div>

  <div class="palette-sections">

    <div class="pickers-grid" id="pickers-grid"></div>

    <div class="scales-section" id="scales-section"></div>

    <div class="output-section">
      <div class="output-header">
        <div class="tabs">
          <button class="tab-btn active" id="tab-css" onclick="switchTab('css')">CSS variables</button>
          <button class="tab-btn" id="tab-json" onclick="switchTab('json')">Figma JSON</button>
        </div>
        <button class="btn" onclick="copyOutput()">
          <svg viewBox="0 0 24 24">
            <rect x="9" y="9" width="13" height="13" rx="2" />
            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
          </svg>
          <span id="copy-label">Copy</span>
        </button>
      </div>
      <pre class="output-box" id="output"></pre>
    </div>

  </div><!-- /.palette-sections -->

</main>
</div>

<div class="toast" id="toast"></div>

<script src="/assets/color-math.js"></script>
<script>
  const MAX_COLORS = 4;
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
    document.getElementById('mode-badge').textContent = mode === 'oklch' ? 'oklch color theory' : 'tint / shade';
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

    if (colors.length < MAX_COLORS) {
      const btn = document.createElement('button');
      btn.className = 'add-color-card';
      btn.innerHTML = `<div class="add-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>Add color`;
      btn.addEventListener('click', addColor);
      grid.appendChild(btn);
    }
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
    copyText(currentTab === 'css' ? genCSS() : genJSON(), 'Copied!');
    const lbl = document.getElementById('copy-label');
    lbl.textContent = 'Copied!'; setTimeout(() => lbl.textContent = 'Copy', 2000);
  }

  // ── SAVE / LOAD ──────────────────────────────────────
  const STORAGE_KEY = 'oklch-palettes';

  function savePalette() {
    const all = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    const name = colors.slice(0, 2).map(c => c.name).join(' · ');
    all.push({
      id: 'p-' + Date.now(), name, savedAt: Date.now(),
      colors: colors.map(c => ({ name: c.name, hex: c.hex }))
    });
    localStorage.setItem(STORAGE_KEY, JSON.stringify(all));
    const lbl = document.getElementById('save-label');
    lbl.textContent = 'Saved!';
    setTimeout(() => lbl.textContent = 'Save palette', 2000);
    showToast('Palette saved');
  }

  // Init — check for ?load=<id>, then build scales and render
  (function () {
    const id = new URLSearchParams(location.search).get('load');
    if (id) {
      const all = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
      const palette = all.find(p => p.id === id);
      if (palette && palette.colors.length >= 1) {
        colors = palette.colors.map((c, i) => ({ id: 'c' + i, name: c.name, hex: c.hex, scale: [] }));
        nextId = colors.length;
      }
    } else {
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
            document.getElementById('mode-badge').textContent = scaleMode === 'oklch' ? 'oklch color theory' : 'tint / shade';
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
  })();
</script>

<?php require '../includes/footer.php'; ?>