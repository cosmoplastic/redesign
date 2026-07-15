<?php
$pageTitle = 'Gradient Generator (OKLCH) — CSS Gradient Studio | ONE design';
$pageDescription = 'Create smoother CSS gradients with OKLCH interpolation, edit stops visually, and copy linear or radial code ready for production.';
$activePage = 'gradient';
$shellClass = 'full-height';
require '../includes/header.php';
?>

<main class="panel">

  <div class="topstrip">
    <div class="topstrip-head">
      <h1 class="topstrip-title">Gradient <em>studio</em></h1>
      <p class="topstrip-intro">Build linear and radial gradients that stay vivid across the whole blend with OKLCH interpolation — no grey, muddy midpoints.</p>
    </div>
    <div class="topstrip-actions">
      <button class="btn" onclick="openExportModal()">
        <svg viewBox="0 0 24 24">
          <rect x="9" y="9" width="13" height="13" rx="2" />
          <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
        </svg>
        Export
      </button>
      <button class="btn btn-primary" onclick="saveGradient()">
        <svg viewBox="0 0 24 24">
          <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" />
        </svg>
        Save
      </button>
    </div>
  </div>

  <div class="workspace">

    <!-- ── LEFT PANEL ────────────────────────────── -->
    <div class="grad-panel">

      <div class="grad-section">
        <label class="field-label">Type</label>
        <div class="tabs">
          <button class="tab-btn active" id="type-linear" onclick="setType('linear')">Linear</button>
          <button class="tab-btn" id="type-radial" onclick="setType('radial')">Radial</button>
        </div>
      </div>

      <div class="grad-section" id="angle-section">
        <label class="field-label">Direction</label>
        <div class="angle-row">
          <div class="angle-wheel" id="angle-wheel">
            <div class="angle-dot" id="angle-dot"></div>
          </div>
          <div class="angle-right">
            <div class="angle-input-wrap">
              <input type="number" id="angle-input" class="angle-input" value="135" min="0" max="359">
              <span class="angle-unit">°</span>
            </div>
            <div class="dir-presets">
              <button onclick="setAngle(315)" title="up-left">↖</button>
              <button onclick="setAngle(0)" title="up">↑</button>
              <button onclick="setAngle(45)" title="up-right">↗</button>
              <button onclick="setAngle(270)" title="left">←</button>
              <span class="dir-center">·</span>
              <button onclick="setAngle(90)" title="right">→</button>
              <button onclick="setAngle(225)" title="down-left">↙</button>
              <button onclick="setAngle(180)" title="down">↓</button>
              <button onclick="setAngle(135)" title="down-right">↘</button>
            </div>
          </div>
        </div>
      </div>

      <div class="grad-section grad-section-stops">
        <label class="field-label">Color stops</label>
        <div id="stops-list"></div>
        <button class="add-stop-btn" onclick="addStopDefault()">
          <svg viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          Add stop
        </button>
      </div>

    </div>

    <!-- ── RIGHT AREA ─────────────────────────────── -->
    <div class="grad-main">

      <div class="grad-preview-section">
        <div class="grad-preview" id="grad-preview"></div>
        <div class="grad-track-outer" id="grad-track-outer">
          <div class="grad-track" id="grad-track"></div>
          <div class="grad-handles" id="grad-handles"></div>
        </div>
        <p class="grad-track-hint">Drag handles · Click track to add stop</p>
      </div>

      <div class="grad-saves-wrap" id="grad-saves-wrap" style="display:none">
        <div class="grad-saves-header">
          <span class="scales-header-label">Saved gradients</span>
        </div>
        <div class="grad-save-list" id="grad-save-list"></div>
      </div>


    </div>
  </div>
</main>
</div>

<div class="export-modal" id="export-modal">
  <div class="export-modal-backdrop" onclick="closeExportModal()"></div>
  <div class="export-modal-box">
    <div class="export-modal-header">
      <div class="tabs">
        <button class="tab-btn active" id="tab-modern" onclick="switchTab('modern')">Modern CSS</button>
        <button class="tab-btn" id="tab-compat" onclick="switchTab('compat')">Compatible</button>
      </div>
      <div class="export-modal-actions">
        <button class="btn" onclick="copyGradient()">
          <svg viewBox="0 0 24 24">
            <rect x="9" y="9" width="13" height="13" rx="2" />
            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
          </svg>
          Copy
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
  // ── STATE ──────────────────────────────────────────────────────
  let stops = [
    { id: 's0', hex: '#2563eb', pos: 0 },
    { id: 's1', hex: '#0f172a', pos: 100 },
  ];
  let gradType = 'linear';
  let angle = 135;
  let currentTab = 'modern';
  let selectedId = 's0';
  let nextId = 2;
  const PREVIEW_STEPS = 24;
  const OUTPUT_STEPS = 16;
  const DRAFT_KEY = 'oklch-gradient-draft';
  let _draftTimer;
  function persistDraft() {
    clearTimeout(_draftTimer);
    _draftTimer = setTimeout(() =>
      localStorage.setItem(DRAFT_KEY, JSON.stringify({ stops, gradType, angle, currentTab }))
      , 300);
  }

  // ── OKLCH INTERPOLATION ────────────────────────────────────────
  function lerpHue(h1, h2, t) {
    let d = ((h2 - h1) % 360 + 360) % 360;
    if (d > 180) d -= 360;
    return ((h1 + d * t) % 360 + 360) % 360;
  }

  function lerpOklch(hexA, hexB, t) {
    const ra = hexToRgb(hexA), rb = hexToRgb(hexB);
    if (!ra || !rb) return t < 0.5 ? hexA : hexB;
    const [La, Ca, Ha] = rgbToOklch(...ra);
    const [Lb, Cb, Hb] = rgbToOklch(...rb);
    const L = La + (Lb - La) * t;
    const C = Ca + (Cb - Ca) * t;
    let H;
    if (Ca < 0.015 && Cb < 0.015) H = 0;
    else if (Ca < 0.015) H = Hb;
    else if (Cb < 0.015) H = Ha;
    else H = lerpHue(Ha, Hb, t);
    return oklchToHex(L, C, H);
  }

  function buildInterpolated(segSteps) {
    const sorted = [...stops].sort((a, b) => a.pos - b.pos);
    if (sorted.length < 2) return sorted.map(s => ({ hex: s.hex, pos: s.pos }));
    const out = [];
    for (let i = 0; i < sorted.length - 1; i++) {
      const a = sorted[i], b = sorted[i + 1];
      for (let j = (i > 0 ? 1 : 0); j <= segSteps; j++) {
        const t = j / segSteps;
        out.push({
          hex: lerpOklch(a.hex, b.hex, t),
          pos: a.pos + (b.pos - a.pos) * t,
        });
      }
    }
    return out;
  }

  // ── CSS GENERATION ─────────────────────────────────────────────
  function modernCSS() {
    const sorted = [...stops].sort((a, b) => a.pos - b.pos);
    const ss = sorted.map(s => `  ${s.hex} ${s.pos}%`).join(',\n');
    if (gradType === 'linear')
      return `/* CSS Color 4 — Chrome 111+, Firefox 113+, Safari 16.2+ */\nbackground: linear-gradient(\n  ${angle}deg in oklch,\n${ss}\n);`;
    return `/* CSS Color 4 — Chrome 111+, Firefox 113+, Safari 16.2+ */\nbackground: radial-gradient(\n  circle in oklch,\n${ss}\n);`;
  }

  function compatCSS() {
    const interp = buildInterpolated(OUTPUT_STEPS);
    const ss = interp.map(s => `  ${s.hex} ${s.pos.toFixed(1)}%`).join(',\n');
    const n = interp.length;
    if (gradType === 'linear')
      return `/* OKLCH-interpolated fallback — ${n} computed stops */\nbackground: linear-gradient(\n  ${angle}deg,\n${ss}\n);`;
    return `/* OKLCH-interpolated fallback — ${n} computed stops */\nbackground: radial-gradient(\n  circle,\n${ss}\n);`;
  }

  function gradientForPreview() {
    const interp = buildInterpolated(PREVIEW_STEPS);
    const ss = interp.map(s => `${s.hex} ${s.pos.toFixed(1)}%`).join(', ');
    if (gradType === 'linear') return `linear-gradient(${angle}deg, ${ss})`;
    return `radial-gradient(circle, ${ss})`;
  }

  function syntaxHighlight(code) {
    return code
      .replace(/(\/\*.*?\*\/)/g, '<span class="token-comment">$1</span>')
      .replace(/(background|linear-gradient|radial-gradient|in oklch)/g, '<span class="token-key">$1</span>')
      .replace(/(#[0-9a-fA-F]{3,8})/g, '<span class="token-val">$1</span>')
      .replace(/([();,])/g, '<span class="token-punct">$1</span>');
  }

  // ── RENDER ─────────────────────────────────────────────────────
  function renderPreview() {
    const css = gradientForPreview();
    document.getElementById('grad-preview').style.background = css;
    const interp = buildInterpolated(PREVIEW_STEPS);
    const ss = interp.map(s => `${s.hex} ${s.pos.toFixed(1)}%`).join(', ');
    document.getElementById('grad-track').style.background =
      `linear-gradient(to right, ${ss})`;
  }

  function renderHandles() {
    const container = document.getElementById('grad-handles');
    container.innerHTML = '';
    stops.forEach(stop => {
      const h = document.createElement('div');
      h.className = 'grad-handle' + (stop.id === selectedId ? ' selected' : '');
      h.style.left = stop.pos + '%';
      h.style.background = stop.hex;
      h.title = `${stop.hex} · ${stop.pos}%`;
      h.dataset.id = stop.id;
      initHandleDrag(h, stop.id);
      h.addEventListener('click', e => { e.stopPropagation(); selectStop(stop.id); });
      container.appendChild(h);
    });
  }

  function renderStopsList() {
    const list = document.getElementById('stops-list');
    list.innerHTML = '';
    const sorted = [...stops].sort((a, b) => a.pos - b.pos);
    sorted.forEach(stop => {
      const row = document.createElement('div');
      row.className = 'stop-row' + (stop.id === selectedId ? ' selected' : '');
      row.dataset.id = stop.id;
      row.innerHTML = `
      <div class="stop-swatch-wrap">
        <div class="stop-swatch" style="background:${stop.hex}"></div>
        <input type="color" class="stop-color-input" value="${stop.hex}">
      </div>
      <input type="text" class="stop-hex-input" value="${stop.hex}" maxlength="7" spellcheck="false">
      <input type="number" class="stop-pos-input" value="${Math.round(stop.pos * 10) / 10}" min="0" max="100" step="1">
      <span class="stop-pos-label">%</span>
      ${stops.length > 2 ? `<button class="stop-remove" title="Remove stop">
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>` : '<div class="stop-remove-placeholder"></div>'}`;

      const colorInput = row.querySelector('.stop-color-input');
      const swatchEl = row.querySelector('.stop-swatch');
      const hexInput = row.querySelector('.stop-hex-input');
      const posInput = row.querySelector('.stop-pos-input');

      row.addEventListener('click', () => selectStop(stop.id));
      swatchEl.addEventListener('click', e => { e.stopPropagation(); colorInput.click(); });
      colorInput.addEventListener('input', e => setStopColor(stop.id, e.target.value));
      hexInput.addEventListener('input', e => {
        const v = e.target.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(v)) setStopColor(stop.id, v);
      });
      hexInput.addEventListener('blur', e => {
        const s = stops.find(x => x.id === stop.id);
        if (s && !/^#[0-9a-fA-F]{6}$/.test(e.target.value)) e.target.value = s.hex;
      });
      posInput.addEventListener('input', e => {
        const v = Math.max(0, Math.min(100, parseFloat(e.target.value) || 0));
        setStopPos(stop.id, v);
      });
      const removeBtn = row.querySelector('.stop-remove');
      if (removeBtn) {
        removeBtn.addEventListener('click', e => { e.stopPropagation(); removeStop(stop.id); });
      }
      list.appendChild(row);
    });
  }

  function renderOutput() {
    const raw = currentTab === 'modern' ? modernCSS() : compatCSS();
    document.getElementById('output').innerHTML = syntaxHighlight(raw);
  }

  function render() {
    renderPreview();
    renderHandles();
    renderStopsList();
    renderOutput();
    updateAngleWheel();
    persistDraft();
  }

  function renderFast() {
    renderPreview();
    renderHandles();
  }

  // ── STATE MUTATORS ─────────────────────────────────────────────
  function selectStop(id) {
    selectedId = id;
    document.querySelectorAll('.stop-row').forEach(r =>
      r.classList.toggle('selected', r.dataset.id === id));
    document.querySelectorAll('.grad-handle').forEach(h =>
      h.classList.toggle('selected', h.dataset.id === id));
  }

  function setStopColor(id, hex) {
    const s = stops.find(x => x.id === id); if (!s) return;
    s.hex = hex;
    render();
  }

  function setStopPos(id, pos) {
    const s = stops.find(x => x.id === id); if (!s) return;
    s.pos = pos;
    renderFast();
    renderOutput();
  }

  function removeStop(id) {
    if (stops.length <= 2) return;
    stops = stops.filter(s => s.id !== id);
    if (selectedId === id) selectedId = stops[0].id;
    render();
  }

  function addStopDefault() {
    if (stops.length >= 8) return;
    const sorted = [...stops].sort((a, b) => a.pos - b.pos);
    let maxGap = -1, gapA = sorted[0], gapB = sorted[1];
    for (let i = 0; i < sorted.length - 1; i++) {
      const gap = sorted[i + 1].pos - sorted[i].pos;
      if (gap > maxGap) { maxGap = gap; gapA = sorted[i]; gapB = sorted[i + 1]; }
    }
    const pos = (gapA.pos + gapB.pos) / 2;
    const hex = lerpOklch(gapA.hex, gapB.hex, 0.5);
    const id = 's' + nextId++;
    stops.push({ id, hex, pos });
    selectedId = id;
    render();
  }

  function setType(type) {
    gradType = type;
    document.getElementById('type-linear').classList.toggle('active', type === 'linear');
    document.getElementById('type-radial').classList.toggle('active', type === 'radial');
    document.getElementById('angle-section').style.display = type === 'linear' ? '' : 'none';
    render();
  }

  function setAngle(a) {
    angle = ((Math.round(a) % 360) + 360) % 360;
    document.getElementById('angle-input').value = angle;
    render();
  }

  function updateAngleWheel() {
    const dot = document.getElementById('angle-dot');
    const r = 34; // pct of half-width from center
    const rad = angle * Math.PI / 180;
    const x = 50 + r * Math.sin(rad);
    const y = 50 - r * Math.cos(rad);
    dot.style.left = x + '%';
    dot.style.top = y + '%';
  }

  // ── INTERACTIONS ───────────────────────────────────────────────
  function initAngleWheel() {
    const wheel = document.getElementById('angle-wheel');
    let dragging = false;

    const angleFromEvent = e => {
      const rect = wheel.getBoundingClientRect();
      const cx = rect.left + rect.width / 2;
      const cy = rect.top + rect.height / 2;
      return ((Math.atan2(e.clientX - cx, -(e.clientY - cy)) * 180 / Math.PI) + 360) % 360;
    };

    wheel.addEventListener('mousedown', e => {
      dragging = true;
      e.preventDefault();
      setAngle(angleFromEvent(e));
    });

    document.addEventListener('mousemove', e => {
      if (!dragging) return;
      setAngle(angleFromEvent(e));
    });

    document.addEventListener('mouseup', () => { dragging = false; });

    document.getElementById('angle-input').addEventListener('input', e => {
      const v = parseInt(e.target.value);
      if (!isNaN(v)) { angle = ((v % 360) + 360) % 360; renderPreview(); renderOutput(); updateAngleWheel(); }
    });
    document.getElementById('angle-input').addEventListener('change', e => {
      setAngle(parseInt(e.target.value) || 0);
    });
  }

  function initHandleDrag(el, id) {
    el.addEventListener('mousedown', e => {
      e.preventDefault();
      e.stopPropagation();
      selectStop(id);
      el.style.cursor = 'grabbing';

      const outer = document.getElementById('grad-track-outer');
      const onMove = e => {
        const rect = outer.getBoundingClientRect();
        const pos = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
        const s = stops.find(x => x.id === id);
        if (s) { s.pos = Math.round(pos * 10) / 10; renderFast(); }
      };
      const onUp = () => {
        el.style.cursor = '';
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', onUp);
        render();
      };
      document.addEventListener('mousemove', onMove);
      document.addEventListener('mouseup', onUp);
    });
  }

  function initTrackClick() {
    document.getElementById('grad-track-outer').addEventListener('click', e => {
      if (e.target.classList.contains('grad-handle')) return;
      const outer = document.getElementById('grad-track-outer');
      const rect = outer.getBoundingClientRect();
      const pos = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
      if (stops.length >= 8) return;

      const sorted = [...stops].sort((a, b) => a.pos - b.pos);
      let hex = sorted[0].hex;
      for (let i = 0; i < sorted.length - 1; i++) {
        if (pos >= sorted[i].pos && pos <= sorted[i + 1].pos) {
          const span = sorted[i + 1].pos - sorted[i].pos;
          hex = lerpOklch(sorted[i].hex, sorted[i + 1].hex, span > 0 ? (pos - sorted[i].pos) / span : 0);
          break;
        }
      }
      const id = 's' + nextId++;
      stops.push({ id, hex, pos: Math.round(pos * 10) / 10 });
      selectedId = id;
      render();
    });
  }

  function switchTab(tab) {
    currentTab = tab;
    document.getElementById('tab-modern').classList.toggle('active', tab === 'modern');
    document.getElementById('tab-compat').classList.toggle('active', tab === 'compat');
    renderOutput();
  }

  function copyGradient() {
    const raw = currentTab === 'modern' ? modernCSS() : compatCSS();
    copyText(raw, 'Copied!');
    const label = [...stops].sort((a, b) => a.pos - b.pos).map(s => s.hex).join(' → ');
    recordExport('gradient', currentTab === 'modern' ? 'Modern CSS' : 'Compatible CSS', label, raw);
  }

  function openExportModal() {
    renderOutput();
    document.getElementById('export-modal').classList.add('open');
  }
  function closeExportModal() {
    document.getElementById('export-modal').classList.remove('open');
  }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeExportModal(); });

  // ── SAVED GRADIENTS ────────────────────────────────────────────
  const GRAD_KEY = 'oklch-gradients';

  function loadGradients() {
    try { return JSON.parse(localStorage.getItem(GRAD_KEY) || '[]'); }
    catch (_) { return []; }
  }

  function gradientCSSValue() {
    const interp = buildInterpolated(OUTPUT_STEPS);
    const ss = interp.map(s => `${s.hex} ${s.pos.toFixed(1)}%`).join(', ');
    return gradType === 'linear'
      ? `linear-gradient(${angle}deg, ${ss})`
      : `radial-gradient(circle, ${ss})`;
  }

  function saveGradient() {
    const all = loadGradients();
    const sorted = [...stops].sort((a, b) => a.pos - b.pos);
    const label = sorted.map(s => s.hex).join(' → ');
    const item = {
      id: 'g-' + Date.now(),
      name: label,
      savedAt: Date.now(),
      gradType,
      angle,
      stops: sorted.map(s => ({ hex: s.hex, pos: s.pos })),
      css: gradientCSSValue(),
    };
    all.push(item);
    localStorage.setItem(GRAD_KEY, JSON.stringify(all));
    renderSavedGradients();
    showToast('Gradient saved');
  }

  function deleteGradient(id) {
    const all = loadGradients().filter(g => g.id !== id);
    localStorage.setItem(GRAD_KEY, JSON.stringify(all));
    renderSavedGradients();
  }

  function loadGradientById(id) {
    const g = loadGradients().find(x => x.id === id);
    if (!g) return;
    stops = g.stops.map((s, i) => ({ id: 's' + i, hex: s.hex, pos: s.pos }));
    nextId = stops.length;
    gradType = g.gradType;
    angle = g.angle ?? 135;
    document.getElementById('type-linear').classList.toggle('active', gradType === 'linear');
    document.getElementById('type-radial').classList.toggle('active', gradType === 'radial');
    document.getElementById('angle-section').style.display = gradType === 'linear' ? '' : 'none';
    document.getElementById('angle-input').value = angle;
    selectedId = stops[0]?.id ?? 's0';
    render();
    showToast('Gradient loaded');
  }

  function renderSavedGradients() {
    const all = loadGradients();
    const wrap = document.getElementById('grad-saves-wrap');
    const list = document.getElementById('grad-save-list');
    if (!wrap || !list) return;
    wrap.style.display = all.length ? '' : 'none';
    list.innerHTML = '';
    [...all].reverse().forEach(g => {
      const card = document.createElement('div');
      card.className = 'grad-save-card';
      const meta = g.gradType === 'linear' ? `linear · ${g.angle}° · ${g.stops.length} stops` : `radial · ${g.stops.length} stops`;
      card.innerHTML = `
        <div class="grad-save-bar" style="background:${g.css}"></div>
        <div class="grad-save-body">
          <input class="grad-save-name" value="${g.name}" spellcheck="false">
          <span class="grad-save-meta">${meta}</span>
          <div class="grad-save-actions">
            <button class="grad-save-load">Load</button>
            <button class="grad-save-del" title="Delete">
              <svg viewBox="0 0 10 10"><line x1="2" y1="2" x2="8" y2="8"/><line x1="8" y1="2" x2="2" y2="8"/></svg>
            </button>
          </div>
        </div>`;
      card.querySelector('.grad-save-name').addEventListener('change', e => {
        const all2 = loadGradients();
        const item = all2.find(x => x.id === g.id);
        if (item) { item.name = e.target.value.trim() || g.name; localStorage.setItem(GRAD_KEY, JSON.stringify(all2)); }
      });
      card.querySelector('.grad-save-load').addEventListener('click', () => loadGradientById(g.id));
      card.querySelector('.grad-save-del').addEventListener('click', () => {
        card.style.transition = 'opacity .18s'; card.style.opacity = '0';
        setTimeout(() => deleteGradient(g.id), 180);
      });
      list.appendChild(card);
    });
  }

  // ── INIT ───────────────────────────────────────────────────────
  (function () {
    let hasDraft = false;
    try {
      const draft = JSON.parse(localStorage.getItem(DRAFT_KEY));
      if (draft) {
        if (draft.stops?.length >= 2) {
          stops = draft.stops;
          nextId = Math.max(...stops.map(s => parseInt(s.id.slice(1)) + 1), nextId);
        }
        if (draft.gradType) gradType = draft.gradType;
        if (draft.angle != null) angle = draft.angle;
        if (draft.currentTab) currentTab = draft.currentTab;
        hasDraft = true;
      }
    } catch (_) { }

    if (!hasDraft) {
      // Seed from saved palette (most recent) or palette draft — use primary color's brightest + darkest stop
      try {
        const palettes = JSON.parse(localStorage.getItem('oklch-palettes') || '[]');
        let primaryHex = null;
        if (palettes.length) {
          primaryHex = palettes[palettes.length - 1].colors?.[0]?.hex;
        }
        if (!primaryHex) {
          const palDraft = JSON.parse(localStorage.getItem('oklch-palette-draft') || 'null');
          primaryHex = palDraft?.colors?.[0]?.hex;
        }
        if (primaryHex && hexToRgb(primaryHex)) {
          const scale = genScale(primaryHex);
          stops = [
            { id: 's0', hex: scale[0], pos: 0 },
            { id: 's1', hex: scale[scale.length - 1], pos: 100 },
          ];
        }
      } catch (_) { }
    }

    document.getElementById('type-linear').classList.toggle('active', gradType === 'linear');
    document.getElementById('type-radial').classList.toggle('active', gradType === 'radial');
    document.getElementById('angle-section').style.display = gradType === 'linear' ? '' : 'none';
    document.getElementById('angle-input').value = angle;
    document.getElementById('tab-modern').classList.toggle('active', currentTab === 'modern');
    document.getElementById('tab-compat').classList.toggle('active', currentTab === 'compat');

    // Handle ?load=<id> deep-link from Saved page
    const loadParam = new URLSearchParams(location.search).get('load');
    if (loadParam) {
      const linked = loadGradients().find(x => x.id === loadParam);
      if (linked) {
        stops = linked.stops.map((s, i) => ({ id: 's' + i, hex: s.hex, pos: s.pos }));
        nextId = stops.length;
        gradType = linked.gradType;
        angle = linked.angle ?? 135;
        document.getElementById('type-linear').classList.toggle('active', gradType === 'linear');
        document.getElementById('type-radial').classList.toggle('active', gradType === 'radial');
        document.getElementById('angle-section').style.display = gradType === 'linear' ? '' : 'none';
        document.getElementById('angle-input').value = angle;
        selectedId = stops[0]?.id ?? 's0';
      }
    }

    initAngleWheel();
    initTrackClick();
    render();
    renderSavedGradients();

    // ── Intro sweep overlay ────────────────────────────────
    const introEl = document.createElement('div');
    introEl.id = 'grad-intro-overlay';
    document.getElementById('grad-preview').appendChild(introEl);
    setTimeout(() => {
      introEl.classList.add('fade-out');
      introEl.addEventListener('transitionend', () => introEl.remove(), { once: true });
    }, 1100);
  })();
</script>

<?php require '../includes/footer.php'; ?>