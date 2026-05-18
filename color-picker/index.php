<?php
$pageTitle = 'Color Picker — OKLCH Tools';
$activePage = 'picker';
$shellClass = 'full-height';
require '../includes/header.php';
?>

<main class="panel">
  <div class="topstrip">
    <div class="topstrip-title">Color <em>picker</em></div>
    <div class="topstrip-actions">
      <div class="harmony-tabs" id="harmony-tabs">
        <button class="htab active" data-mode="none">None</button>
        <button class="htab" data-mode="complementary">Complementary</button>
        <button class="htab" data-mode="triadic">Triadic</button>
        <button class="htab" data-mode="analogous">Analogous</button>
        <button class="htab" data-mode="split">Split</button>
        <button class="htab" data-mode="tetradic">Tetradic</button>
      </div>
    </div>
  </div>

  <div class="workspace">

    <div class="picker-panel">
      <div class="canvas-stack" id="canvas-stack">
        <canvas id="hue-canvas" width="280" height="280"></canvas>
        <canvas id="gamut-canvas" width="220" height="220"></canvas>
        <div class="gamut-thumb" id="gamut-thumb"></div>
        <div class="hue-thumb" id="hue-thumb"></div>
      </div>

      <div class="sliders">
        <div class="slider-row">
          <span class="slider-label">L</span>
          <div class="slider-wrap">
            <div class="slider-track" id="l-track"></div>
            <input type="range" class="oklch-slider" id="l-slider" min="0" max="100" step="0.5">
          </div>
          <span class="slider-val" id="l-val">60.0</span>
        </div>
        <div class="slider-row">
          <span class="slider-label">C</span>
          <div class="slider-wrap">
            <div class="slider-track" id="c-track"></div>
            <input type="range" class="oklch-slider" id="c-slider" min="0" max="40" step="0.1">
          </div>
          <span class="slider-val" id="c-val">0.178</span>
        </div>
        <div class="slider-row">
          <span class="slider-label">H</span>
          <div class="slider-wrap">
            <div class="slider-track" id="h-track"></div>
            <input type="range" class="oklch-slider" id="h-slider" min="0" max="360" step="0.5">
          </div>
          <span class="slider-val" id="h-val">264°</span>
        </div>
        <div class="slider-row">
          <span class="slider-label">A</span>
          <div class="slider-wrap">
            <div class="slider-track" id="a-track"></div>
            <input type="range" class="oklch-slider" id="a-slider" min="0" max="100" step="1" value="100">
          </div>
          <span class="slider-val" id="a-val">100%</span>
        </div>
      </div>

      <div class="picker-controls">
        <div id="hex-preview" class="color-swatch-btn" style="width:36px;height:36px;border-radius:8px;"></div>
        <input type="text" id="hex-input" class="hex-input" maxlength="9" spellcheck="false" value="#2563eb">
      </div>
    </div>

    <div class="output-panel">
      <div class="color-stage" id="color-stage">
        <div class="stage-values">
          <span class="stage-hex" id="stage-hex">#2563eb</span>
          <span class="stage-oklch" id="stage-oklch">oklch(60.0% 0.178
            264°)</span>
        </div>
        <button class="stage-copy-btn" id="stage-copy" onclick="copyText(getHex(),'Hex copied')">
          <svg viewBox="0 0 24 24">
            <rect x="9" y="9" width="13" height="13" rx="2" />
            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
          </svg>
          Copy hex
        </button>
      </div>

      <div class="output-scroll">
        <div>
          <div class="out-section-title">Formats</div>
          <div class="format-list" id="format-list"></div>
        </div>
        <div id="harmony-section" style="display:none;">
          <div class="out-section-title" id="harmony-label">Harmony</div>
          <div class="harmony-strip" id="harmony-strip"></div>
        </div>
        <div>
          <div class="out-section-title">Contrast</div>
          <div class="contrast-grid" id="contrast-grid"></div>
        </div>
      </div>
    </div>

  </div>
</main>
</div>

<div class="toast" id="toast"></div>

<script src="/assets/color-math.js"></script>
<script>
  let state = { L: 0.60, C: 0.178, H: 264, A: 1.0 };
  let harmonyMode = 'none', activeHarmonyIdx = 0;

  const hueCanvas = document.getElementById('hue-canvas');
  const gamutCanvas = document.getElementById('gamut-canvas');
  const hueCtx = hueCanvas.getContext('2d');
  const gamutCtx = gamutCanvas.getContext('2d');
  const HUE_OUTER = 140, HUE_INNER = 110, GAMUT_R = 110;

  // position gamut canvas centred inside hue canvas
  gamutCanvas.style.cssText = 'position:absolute;top:30px;left:30px;border-radius:50%;cursor:crosshair;';

  function drawHueWheel() {
    hueCtx.clearRect(0, 0, 280, 280);
    const cx = 140, cy = 140;
    for (let i = 0; i < 360; i++) {
      const a1 = (i / 360) * Math.PI * 2 - Math.PI / 2, a2 = ((i + 1) / 360) * Math.PI * 2 - Math.PI / 2;
      const [r, g, b] = oklchToRgb(0.65, 0.18, i);
      hueCtx.beginPath(); hueCtx.moveTo(cx, cy); hueCtx.arc(cx, cy, HUE_OUTER, a1, a2); hueCtx.closePath();
      hueCtx.fillStyle = `rgb(${r},${g},${b})`; hueCtx.fill();
    }
    hueCtx.globalCompositeOperation = 'destination-out';
    hueCtx.beginPath(); hueCtx.arc(cx, cy, HUE_INNER, 0, Math.PI * 2); hueCtx.fill();
    hueCtx.globalCompositeOperation = 'source-over';
  }

  function drawGamut() {
    const size = 220, cx = 110, cy = 110, maxC = 0.37;
    gamutCtx.clearRect(0, 0, size, size);
    gamutCtx.save();
    gamutCtx.beginPath(); gamutCtx.arc(cx, cy, GAMUT_R, 0, Math.PI * 2); gamutCtx.clip();
    const img = gamutCtx.createImageData(size, size);
    for (let py = 0; py < size; py++) {
      for (let px = 0; px < size; px++) {
        const [r, g, b] = oklchToRgb(1 - (py / size), (px / size) * maxC, state.H);
        const idx = (py * size + px) * 4;
        img.data[idx] = r; img.data[idx + 1] = g; img.data[idx + 2] = b; img.data[idx + 3] = 255;
      }
    }
    gamutCtx.putImageData(img, 0, 0);
    for (let py = 0; py < size; py += 2) {
      for (let px = 0; px < size; px += 2) {
        if (!isInGamut(1 - (py / size), (px / size) * maxC, state.H)) {
          gamutCtx.fillStyle = 'rgba(10,10,11,0.45)'; gamutCtx.fillRect(px, py, 2, 2);
        }
      }
    }
    gamutCtx.restore();
    gamutCtx.beginPath(); gamutCtx.arc(cx, cy, GAMUT_R, 0, Math.PI * 2);
    gamutCtx.strokeStyle = 'rgba(255,255,255,0.08)'; gamutCtx.lineWidth = 1; gamutCtx.stroke();
  }

  function updateHueThumb() {
    const r = (HUE_OUTER + HUE_INNER) / 2;
    const angle = (state.H - 90) * Math.PI / 180;
    const t = document.getElementById('hue-thumb');
    t.style.left = (140 + r * Math.cos(angle)) + 'px';
    t.style.top = (140 + r * Math.sin(angle)) + 'px';
    const [r2, g, b] = oklchToRgb(0.65, 0.18, state.H);
    t.style.background = `rgb(${r2},${g},${b})`;
  }

  function updateGamutThumb() {
    const maxC = 0.37;
    const px = clamp(state.C / maxC, 0, 1) * 220;
    const py = clamp(1 - state.L, 0, 1) * 220;
    const t = document.getElementById('gamut-thumb');
    t.style.left = (30 + px) + 'px'; t.style.top = (30 + py) + 'px';
    t.style.background = oklchToHex(state.L, state.C, state.H);
  }

  function updateSliderTracks() {
    const maxC = 0.40;
    document.getElementById('l-track').style.background = `linear-gradient(to right,${oklchToHex(0, 0, state.H)},${oklchToHex(1, 0, state.H)})`;
    document.getElementById('c-track').style.background = `linear-gradient(to right,${oklchToHex(state.L, 0, state.H)},${oklchToHex(state.L, maxC, state.H)})`;
    const hstops = [];
    for (let h = 0; h <= 360; h += 30) hstops.push(oklchToHex(state.L, clamp(state.C, 0.1, 0.2), h));
    document.getElementById('h-track').style.background = `linear-gradient(to right,${hstops.join(',')})`;
    const hex = oklchToHex(state.L, state.C, state.H);
    document.getElementById('a-track').style.background = `linear-gradient(to right,transparent,${hex}),repeating-conic-gradient(#666 0% 25%,#999 0% 50%) 0 0/8px 8px`;
    document.getElementById('l-slider').value = state.L * 100;
    document.getElementById('c-slider').value = state.C * 100;
    document.getElementById('h-slider').value = state.H;
    document.getElementById('a-slider').value = state.A * 100;
    document.getElementById('l-val').textContent = (state.L * 100).toFixed(1);
    document.getElementById('c-val').textContent = state.C.toFixed(3);
    document.getElementById('h-val').textContent = Math.round(state.H) + '°';
    document.getElementById('a-val').textContent = Math.round(state.A * 100) + '%';
  }

  function getHex() { return oklchToHex(state.L, state.C, state.H); }

  function updateStage() {
    const hex = getHex(); const [r, g, b] = hexToRgb(hex) || [0, 0, 0];
    const lum = .2126 * (r / 255) + .7152 * (g / 255) + .0722 * (b / 255);
    const tc = lum > .5 ? 'rgba(0,0,0,0.75)' : 'rgba(255,255,255,0.9)';
    const tc2 = lum > .5 ? 'rgba(0,0,0,0.45)' : 'rgba(255,255,255,0.5)';
    const stage = document.getElementById('color-stage'); stage.style.background = hex;
    const stageHex = document.getElementById('stage-hex');
    stageHex.style.color = tc; stageHex.textContent = hex;
    const stageOklch = document.getElementById('stage-oklch');
    stageOklch.style.color = tc2;
    stageOklch.textContent = `oklch(${(state.L * 100).toFixed(1)}% ${state.C.toFixed(3)} ${Math.round(state.H)}°)`;
    const stageCopy = document.getElementById('stage-copy');
    stageCopy.style.color = tc;
    stageCopy.style.borderColor = lum > .5 ? 'rgba(0,0,0,0.2)' : 'rgba(255,255,255,0.22)';
    stageCopy.style.background = lum > .5 ? 'rgba(0,0,0,0.1)' : 'rgba(0,0,0,0.2)';
    document.getElementById('hex-input').value = hex;
    document.getElementById('hex-preview').style.background = hex;
  }

  function renderFormats() {
    const hex = getHex(); const [r, g, b] = hexToRgb(hex) || [0, 0, 0];
    const rn = r / 255, gn = g / 255, bn = b / 255;
    const max = Math.max(rn, gn, bn), min = Math.min(rn, gn, bn), l2 = (max + min) / 2;
    let h2 = 0, s2 = 0;
    if (max !== min) { const d = max - min; s2 = l2 > .5 ? d / (2 - max - min) : d / (max + min); if (max === rn) h2 = ((gn - bn) / d + (gn < bn ? 6 : 0)) / 6; else if (max === gn) h2 = ((bn - rn) / d + 2) / 6; else h2 = ((rn - gn) / d + 4) / 6; }
    const A = state.A, L = state.L, C = state.C, H = state.H;
    const aStr = A < 1 ? ` / ${Math.round(A * 100)}%` : '';
    const formats = [
      { label: 'hex', value: A < 1 ? hex + (Math.round(A * 255)).toString(16).padStart(2, '0') : hex },
      { label: 'oklch', value: `oklch(${(L * 100).toFixed(1)}% ${C.toFixed(4)} ${H.toFixed(1)}${aStr})` },
      { label: 'rgb', value: A < 1 ? `rgba(${r},${g},${b},${A.toFixed(2)})` : `rgb(${r},${g},${b})` },
      { label: 'hsl', value: A < 1 ? `hsla(${Math.round(h2 * 360)},${Math.round(s2 * 100)}%,${Math.round(l2 * 100)}%,${A.toFixed(2)})` : `hsl(${Math.round(h2 * 360)},${Math.round(s2 * 100)}%,${Math.round(l2 * 100)}%)` },
      { label: 'css var', value: `--color: ${hex};` },
      { label: 'figma', value: `oklch(${(L * 100).toFixed(1)}% ${C.toFixed(3)} ${H.toFixed(0)})` },
    ];
    const list = document.getElementById('format-list'); list.innerHTML = '';
    formats.forEach(({ label, value }) => {
      const row = document.createElement('div'); row.className = 'format-row';
      row.innerHTML = `<span class="format-label">${label}</span><span class="format-value">${value}</span><span class="format-copy"><svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg></span>`;
      row.addEventListener('click', () => copyText(value, label + ' copied'));
      list.appendChild(row);
    });
  }

  function getHarmonyHues(mode, H) {
    const m = { 'complementary': [H, (H + 180) % 360], 'triadic': [H, (H + 120) % 360, (H + 240) % 360], 'analogous': [H, (H + 30) % 360, (H - 30 + 360) % 360], 'split': [H, (H + 150) % 360, (H + 210) % 360], 'tetradic': [H, (H + 90) % 360, (H + 180) % 360, (H + 270) % 360] };
    return m[mode] || [H];
  }

  const HARMONY_NAMES = { complementary: ['Base', 'Complement'], triadic: ['Base', 'Triad 2', 'Triad 3'], analogous: ['Base', 'Analog +30', 'Analog −30'], split: ['Base', 'Split 1', 'Split 2'], tetradic: ['Base', 'Tetrad 2', 'Tetrad 3', 'Tetrad 4'] };

  function renderHarmony() {
    const section = document.getElementById('harmony-section');
    const strip = document.getElementById('harmony-strip');
    const label = document.getElementById('harmony-label');
    if (harmonyMode === 'none') { section.style.display = 'none'; return; }
    section.style.display = 'block';
    label.textContent = harmonyMode.charAt(0).toUpperCase() + harmonyMode.slice(1) + ' harmony';
    const hues = getHarmonyHues(harmonyMode, state.H);
    const names = HARMONY_NAMES[harmonyMode] || [];
    strip.innerHTML = '';
    hues.forEach((h, i) => {
      const hex = oklchToHex(state.L, state.C, h);
      const div = document.createElement('div');
      div.className = 'harmony-swatch' + (i === activeHarmonyIdx ? ' active' : '');
      div.innerHTML = `<div class="harmony-swatch-color" style="background:${hex}"></div><div class="harmony-swatch-label">${names[i] || 'Color ' + (i + 1)}</div>`;
      div.addEventListener('click', () => { if (i === 0) return; state.H = h; activeHarmonyIdx = i; syncAll(); });
      strip.appendChild(div);
    });
  }

  function relativeLuminance(hex) { const rgb = hexToRgb(hex) || [0, 0, 0]; return .2126 * lin(rgb[0]) + .7152 * lin(rgb[1]) + .0722 * lin(rgb[2]); }
  function contrastRatio(h1, h2) { const l1 = relativeLuminance(h1), l2 = relativeLuminance(h2); return (Math.max(l1, l2) + .05) / (Math.min(l1, l2) + .05); }

  function renderContrast() {
    const hex = getHex();
    const grid = document.getElementById('contrast-grid'); grid.innerHTML = '';
    [{ label: 'vs white', bg: '#ffffff' }, { label: 'vs black', bg: '#000000' }].forEach(({ label, bg }) => {
      const ratio = contrastRatio(hex, bg);
      const card = document.createElement('div'); card.className = 'contrast-card';
      card.innerHTML = `
      <div class="contrast-against"><div class="contrast-swatch-dot" style="background:${bg}"></div>${label}</div>
      <div class="contrast-ratio">${ratio.toFixed(2)}<span>:1</span></div>
      <div class="contrast-badges">
        <span class="wcag-badge ${ratio >= 3 ? 'wcag-pass' : 'wcag-fail'}">AA Lg</span>
        <span class="wcag-badge ${ratio >= 4.5 ? 'wcag-pass' : 'wcag-fail'}">AA</span>
        <span class="wcag-badge ${ratio >= 7 ? 'wcag-pass' : 'wcag-fail'}">AAA</span>
      </div>`;
      grid.appendChild(card);
    });
  }

  function syncAll() {
    const [L, C, H] = clampToGamut(state.L, state.C, state.H);
    state.L = L; state.C = C; state.H = H;
    drawGamut(); updateHueThumb(); updateGamutThumb();
    updateSliderTracks(); updateStage(); renderFormats(); renderHarmony(); renderContrast();
  }

  // canvas interactions
  let dragging = null;
  function hueCoords(e) { const rect = hueCanvas.getBoundingClientRect(); const sx = hueCanvas.width / rect.width, sy = hueCanvas.height / rect.height; const ct = e.touches ? e.touches[0] : e; return [(ct.clientX - rect.left) * sx, (ct.clientY - rect.top) * sy]; }
  function gamutCoords(e) { const rect = gamutCanvas.getBoundingClientRect(); const sx = gamutCanvas.width / rect.width, sy = gamutCanvas.height / rect.height; const ct = e.touches ? e.touches[0] : e; return [(ct.clientX - rect.left) * sx, (ct.clientY - rect.top) * sy]; }
  function inRing(cx, cy, ri, ro, x, y) { const d = Math.hypot(x - cx, y - cy); return d >= ri && d <= ro; }
  function inCircle(cx, cy, r, x, y) { return Math.hypot(x - cx, y - cy) <= r; }
  function angle(cx, cy, x, y) { return ((Math.atan2(y - cy, x - cx) * 180 / Math.PI) + 90 + 360) % 360; }
  function applyGamut(x, y) { state.L = clamp(1 - (y / 220), 0, 1); state.C = clamp((x / 220) * 0.37, 0, 0.37); syncAll(); }

  hueCanvas.addEventListener('mousedown', e => { e.preventDefault(); const [x, y] = hueCoords(e); if (inRing(140, 140, HUE_INNER, HUE_OUTER, x, y)) { dragging = 'hue'; state.H = angle(140, 140, x, y); syncAll(); } else if (inCircle(140, 140, HUE_INNER, x, y)) { dragging = 'gamut_h'; applyGamut(x - 30, y - 30); } });
  gamutCanvas.addEventListener('mousedown', e => { e.preventDefault(); const [x, y] = gamutCoords(e); if (inCircle(110, 110, GAMUT_R, x, y)) { dragging = 'gamut'; applyGamut(x, y); } });
  window.addEventListener('mousemove', e => { if (!dragging) return; e.preventDefault(); if (dragging === 'hue') { const [x, y] = hueCoords(e); state.H = angle(140, 140, x, y); syncAll(); } else if (dragging === 'gamut') { const [x, y] = gamutCoords(e); applyGamut(x, y); } else if (dragging === 'gamut_h') { const [x, y] = hueCoords(e); applyGamut(x - 30, y - 30); } });
  window.addEventListener('mouseup', () => dragging = null);
  hueCanvas.addEventListener('touchstart', e => { e.preventDefault(); const [x, y] = hueCoords(e); if (inRing(140, 140, HUE_INNER, HUE_OUTER, x, y)) { dragging = 'hue'; state.H = angle(140, 140, x, y); syncAll(); } else if (inCircle(140, 140, HUE_INNER, x, y)) { dragging = 'gamut_h'; applyGamut(x - 30, y - 30); } }, { passive: false });
  gamutCanvas.addEventListener('touchstart', e => { e.preventDefault(); const [x, y] = gamutCoords(e); if (inCircle(110, 110, GAMUT_R, x, y)) { dragging = 'gamut'; applyGamut(x, y); } }, { passive: false });
  window.addEventListener('touchmove', e => { if (!dragging) return; e.preventDefault(); if (dragging === 'hue') { const [x, y] = hueCoords(e); state.H = angle(140, 140, x, y); syncAll(); } else if (dragging === 'gamut') { const [x, y] = gamutCoords(e); applyGamut(x, y); } else if (dragging === 'gamut_h') { const [x, y] = hueCoords(e); applyGamut(x - 30, y - 30); } }, { passive: false });
  window.addEventListener('touchend', () => dragging = null);

  document.getElementById('l-slider').addEventListener('input', e => { state.L = e.target.value / 100; syncAll(); });
  document.getElementById('c-slider').addEventListener('input', e => { state.C = e.target.value / 100; syncAll(); });
  document.getElementById('h-slider').addEventListener('input', e => { state.H = parseFloat(e.target.value); syncAll(); });
  document.getElementById('a-slider').addEventListener('input', e => { state.A = e.target.value / 100; updateSliderTracks(); updateStage(); renderFormats(); });

  document.getElementById('hex-input').addEventListener('input', e => {
    let v = e.target.value.trim(); if (!v.startsWith('#')) v = '#' + v;
    const rgb = hexToRgb(v); if (rgb) { const [L, C, H] = rgbToOklch(...rgb); state.L = L; state.C = C; state.H = H; syncAll(); }
  });

  document.getElementById('harmony-tabs').addEventListener('click', e => {
    const btn = e.target.closest('.htab'); if (!btn) return;
    document.querySelectorAll('.htab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active'); harmonyMode = btn.dataset.mode; activeHarmonyIdx = 0; renderHarmony();
  });

  document.addEventListener('keydown', e => {
    if (document.activeElement.tagName === 'INPUT') return;
    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') { const d = e.shiftKey ? 10 : 1; state.H = (state.H + (e.key === 'ArrowRight' ? d : -d) + 360) % 360; syncAll(); }
    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') { const d = e.shiftKey ? .05 : .01; state.L = clamp(state.L + (e.key === 'ArrowUp' ? d : -d), 0, 1); syncAll(); }
    if ((e.metaKey || e.ctrlKey) && e.key === 'c') copyText(getHex(), 'Hex copied');
  });

  drawHueWheel(); syncAll();
</script>

<?php require '../includes/footer.php'; ?>