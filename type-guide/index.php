<?php
$pageTitle = 'Type Scale — ONE design';
$activePage = 'type-guide';
$shellClass = 'full-height';
require '../includes/header.php';
?>

<main class="panel">

  <div class="topstrip">
    <span class="topstrip-title">Typography <em>guide</em></span>
  </div>

  <div class="workspace">

    <!-- ── LEFT CONTROLS ─────────────────────────────── -->
    <div class="grad-panel">

      <div class="grad-section">
        <label class="field-label">Scale</label>
        <div class="type-scale-row">
          <span class="type-scale-device">Desktop</span>
          <input type="number" class="type-base-input" id="d-base" value="16" min="10" max="24" step="1">
          <span class="type-scale-unit">px base</span>
        </div>
        <div class="type-ratio-row">
          <select class="type-ratio-select" id="d-ratio">
            <option value="1.067">Minor Second · 1.067</option>
            <option value="1.125" selected>Major Second · 1.125</option>
            <option value="1.200">Minor Third · 1.200</option>
            <option value="1.250">Major Third · 1.250</option>
            <option value="1.333">Perfect Fourth · 1.333</option>
            <option value="1.414">Aug. Fourth · 1.414</option>
            <option value="1.500">Perfect Fifth · 1.500</option>
            <option value="1.618">Golden Ratio · 1.618</option>
          </select>
        </div>
      </div>

      <div class="grad-section">
        <div class="type-scale-row">
          <span class="type-scale-device">Mobile</span>
          <input type="number" class="type-base-input" id="m-base" value="15" min="10" max="22" step="1">
          <span class="type-scale-unit">px base</span>
        </div>
        <div class="type-ratio-row">
          <select class="type-ratio-select" id="m-ratio">
            <option value="1.067">Minor Second · 1.067</option>
            <option value="1.125" selected>Major Second · 1.125</option>
            <option value="1.200">Minor Third · 1.200</option>
            <option value="1.250">Major Third · 1.250</option>
            <option value="1.333">Perfect Fourth · 1.333</option>
            <option value="1.414">Aug. Fourth · 1.414</option>
            <option value="1.500">Perfect Fifth · 1.500</option>
            <option value="1.618">Golden Ratio · 1.618</option>
          </select>
        </div>
      </div>

      <div class="grad-section">
        <label class="field-label">Fonts</label>
        <div class="type-font-row">
          <span class="type-font-label">Heading</span>
          <input type="text" class="type-font-input" id="heading-font" value="Fraunces" spellcheck="false">
          <button class="type-font-load" onclick="loadFont('heading')">Load</button>
        </div>
        <div class="type-font-row">
          <span class="type-font-label">Body</span>
          <input type="text" class="type-font-input" id="body-font" value="DM Mono" spellcheck="false">
          <button class="type-font-load" onclick="loadFont('body')">Load</button>
        </div>
        <p class="type-font-hint">Any Google Font name</p>
      </div>

      <div class="grad-section">
        <label class="field-label">Preview</label>
        <div class="tabs">
          <button class="tab-btn active" id="prev-desktop" onclick="setPreview('desktop')">Desktop</button>
          <button class="tab-btn" id="prev-mobile" onclick="setPreview('mobile')">Mobile</button>
        </div>
      </div>

      <div class="grad-section" style="margin-top:auto;padding-top:16px;">
        <button class="btn" style="width:100%;justify-content:center;" onclick="openExportModal()">
          <svg viewBox="0 0 24 24">
            <rect x="9" y="9" width="13" height="13" rx="2" />
            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
          </svg>
          Export CSS
        </button>
      </div>

    </div>

    <!-- ── RIGHT AREA ─────────────────────────────────── -->
    <div class="grad-main" style="display:flex;flex-direction:column;overflow:hidden;">

      <div class="type-preview-wrap" id="type-preview-wrap">
        <div class="type-preview" id="type-preview"></div>
      </div>

    </div>
  </div>
</main>
</div>

<div class="toast" id="toast"></div>

<div class="export-modal" id="export-modal">
  <div class="export-modal-backdrop" onclick="closeExportModal()"></div>
  <div class="export-modal-box">
    <div class="export-modal-header">
      <div class="tabs">
        <button class="tab-btn active" id="tab-vars" onclick="switchOutputTab('vars')">CSS variables</button>
        <button class="tab-btn" id="tab-classes" onclick="switchOutputTab('classes')">CSS classes</button>
      </div>
      <div class="export-modal-actions">
        <button class="btn" onclick="copyCSS()">
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
      <pre class="export-modal-code" id="type-output"></pre>
    </div>
  </div>
</div>
<script src="/assets/color-math.js"></script>
<script>
  // ── STATE ──────────────────────────────────────────────────────
  const DRAFT_KEY = 'oklch-type-draft';
  let outputTab = 'vars';
  let previewMode = 'desktop';
  let _draftTimer;

  const WEIGHTS = [100, 200, 300, 400, 500, 600, 700, 800, 900];
  const LINE_HEIGHTS = [1.0, 1.05, 1.1, 1.15, 1.2, 1.25, 1.3, 1.4, 1.5, 1.6, 1.65, 1.7, 1.8];
  const TRACKINGS = ['-0.05em', '-0.04em', '-0.03em', '-0.02em', '-0.015em', '-0.01em', '-0.005em', '0em', '0.005em', '0.01em', '0.02em', '0.03em', '0.05em', '0.08em', '0.1em', '0.12em', '0.15em'];

  const DEFAULT_LEVELS = [
    { key: 'display', label: 'Display', step: 5, font: 'heading', weight: 300, lh: 1.05, ls: '-0.02em', transform: 'none', sample: 'Make it beautiful.' },
    { key: 'h1', label: 'H1', step: 4, font: 'heading', weight: 300, lh: 1.1, ls: '-0.015em', transform: 'none', sample: 'The quick brown fox jumps.' },
    { key: 'h2', label: 'H2', step: 3, font: 'heading', weight: 300, lh: 1.15, ls: '-0.01em', transform: 'none', sample: 'Typography sets the tone.' },
    { key: 'h3', label: 'H3', step: 2, font: 'heading', weight: 400, lh: 1.2, ls: '-0.005em', transform: 'none', sample: 'Building great products.' },
    { key: 'lead', label: 'Lead', step: 1, font: 'body', weight: 400, lh: 1.5, ls: '0em', transform: 'none', sample: 'A well-crafted type scale makes reading effortless and gives your layout a natural visual rhythm.' },
    { key: 'body', label: 'Body', step: 0, font: 'body', weight: 400, lh: 1.65, ls: '0.01em', transform: 'none', sample: 'The relationship between font sizes creates hierarchy. Readers scan headings, slow for leads, and settle into body text.' },
    { key: 'sm', label: 'Small', step: -1, font: 'body', weight: 400, lh: 1.55, ls: '0.01em', transform: 'none', sample: 'Published May 2026 · 5 min read' },
    { key: 'xs', label: 'Caption', step: -2, font: 'body', weight: 400, lh: 1.4, ls: '0.03em', transform: 'none', sample: 'A photo of the workspace during a late evening session.' },
    { key: 'label', label: 'Label', step: -2, font: 'body', weight: 500, lh: 1.4, ls: '0.1em', transform: 'uppercase', sample: 'Section label' },
  ];

  let levels = DEFAULT_LEVELS.map(l => ({ ...l }));

  let settings = {
    desktopBase: 16,
    mobileBase: 15,
    desktopRatio: 1.333,
    mobileRatio: 1.250,
    headingFont: 'Fraunces',
    bodyFont: 'DM Mono',
  };

  // ── COMPUTE ────────────────────────────────────────────────────
  function computeSize(step, base, ratio) {
    return base * Math.pow(ratio, step);
  }

  function getSize(level) {
    const s = previewMode === 'desktop' ? settings : { ...settings, desktopBase: settings.mobileBase, desktopRatio: settings.mobileRatio };
    return computeSize(level.step, s.desktopBase, s.desktopRatio);
  }

  function getFontFamily(fontKey) {
    return fontKey === 'heading'
      ? `'${settings.headingFont}', serif`
      : `'${settings.bodyFont}', monospace`;
  }

  // ── RENDER PREVIEW ─────────────────────────────────────────────
  function render() {
    const wrap = document.getElementById('type-preview');
    wrap.innerHTML = '';

    levels.forEach(level => {
      const size = getSize(level);
      const rem = (size / 16).toFixed(3).replace(/\.?0+$/, '');
      const px = size.toFixed(1);

      const el = document.createElement('div');
      el.className = 'type-level-row';
      el.dataset.key = level.key;

      el.innerHTML = `
      <div class="type-level-meta">
        <span class="type-level-tag">${level.label}</span>
        <span class="type-level-size-badge">${Math.round(size)}px · ${rem}rem</span>
        <div class="type-level-ctrls">
          <select class="type-ctrl" data-key="${level.key}" data-prop="weight" title="Weight">
            ${WEIGHTS.map(w => `<option value="${w}"${level.weight === w ? ' selected' : ''}>${w}</option>`).join('')}
          </select>
          <select class="type-ctrl" data-key="${level.key}" data-prop="lh" title="Line height">
            ${LINE_HEIGHTS.map(v => `<option value="${v}"${level.lh === v ? ' selected' : ''}>${v}</option>`).join('')}
          </select>
          <select class="type-ctrl" data-key="${level.key}" data-prop="ls" title="Letter spacing">
            ${TRACKINGS.map(v => `<option value="${v}"${level.ls === v ? ' selected' : ''}>${v}</option>`).join('')}
          </select>
          <select class="type-ctrl" data-key="${level.key}" data-prop="font" title="Font family">
            <option value="heading"${level.font === 'heading' ? ' selected' : ''}>Heading</option>
            <option value="body"${level.font === 'body' ? ' selected' : ''}>Body</option>
          </select>
          <select class="type-ctrl" data-key="${level.key}" data-prop="transform" title="Transform">
            <option value="none"${level.transform === 'none' ? ' selected' : ''}>None</option>
            <option value="uppercase"${level.transform === 'uppercase' ? ' selected' : ''}>Caps</option>
          </select>
        </div>
      </div>
      <div class="type-level-sample" contenteditable="true"
        style="
          font-family:${getFontFamily(level.font)};
          font-size:${px}px;
          font-weight:${level.weight};
          line-height:${level.lh};
          letter-spacing:${level.ls};
          text-transform:${level.transform};
        ">${level.sample}</div>`;

      wrap.appendChild(el);

      // Wire up control changes
      el.querySelectorAll('.type-ctrl').forEach(ctrl => {
        ctrl.addEventListener('change', () => {
          const lv = levels.find(l => l.key === ctrl.dataset.key);
          const val = ctrl.value;
          const prop = ctrl.dataset.prop;
          if (prop === 'weight') lv.weight = parseInt(val);
          else if (prop === 'lh') lv.lh = parseFloat(val);
          else lv[prop] = val;
          render();
          renderOutput();
          persistDraft();
        });
      });

      // Save edits to sample text
      const sampleEl = el.querySelector('.type-level-sample');
      sampleEl.addEventListener('input', () => {
        level.sample = sampleEl.textContent;
        persistDraft();
      });
    });

    renderOutput();
  }

  // ── OUTPUT ─────────────────────────────────────────────────────
  function px(step, base, ratio) {
    return (base * Math.pow(ratio, step)).toFixed(2);
  }

  function genVarsCSS() {
    const dB = settings.desktopBase, dR = settings.desktopRatio;
    const mB = settings.mobileBase, mR = settings.mobileRatio;
    const ratioName = document.getElementById('d-ratio').selectedOptions[0]?.text?.split(' · ')[0] || '';
    const mRatioName = document.getElementById('m-ratio').selectedOptions[0]?.text?.split(' · ')[0] || '';

    const varLines = levels.map(l =>
      `  --type-${l.key}: ${px(l.step, dB, dR)}px;`
    );
    const mVarLines = levels.map(l =>
      `    --type-${l.key}: ${px(l.step, mB, mR)}px;`
    );
    const lhLines = levels.map(l => `  --lh-${l.key}: ${l.lh};`);
    const lsLines = levels.map(l => `  --ls-${l.key}: ${l.ls};`);

    return [
      `:root {`,
      `  /* Fonts */`,
      `  --font-heading: '${settings.headingFont}', serif;`,
      `  --font-body: '${settings.bodyFont}', monospace;`,
      ``,
      `  /* Scale — Desktop · ${ratioName} (${dR}) · Base ${dB}px */`,
      ...varLines,
      ``,
      `  /* Line heights */`,
      ...lhLines,
      ``,
      `  /* Letter spacing */`,
      ...lsLines,
      `}`,
      ``,
      `@media (max-width: 768px) {`,
      `  :root {`,
      `    /* Scale — Mobile · ${mRatioName} (${mR}) · Base ${mB}px */`,
      ...mVarLines,
      `  }`,
      `}`,
    ].join('\n');
  }

  function genClassesCSS() {
    const dB = settings.desktopBase, dR = settings.desktopRatio;
    const mB = settings.mobileBase, mR = settings.mobileRatio;

    const blocks = levels.map(l => {
      const dSize = px(l.step, dB, dR);
      const mSize = px(l.step, mB, mR);
      return [
        `.type-${l.key} {`,
        `  font-family: var(--font-${l.font});`,
        `  font-size: ${dSize}px;`,
        `  font-weight: ${l.weight};`,
        `  line-height: ${l.lh};`,
        `  letter-spacing: ${l.ls};`,
        l.transform !== 'none' ? `  text-transform: ${l.transform};` : null,
        `}`,
        `@media (max-width: 768px) {`,
        `  .type-${l.key} { font-size: ${mSize}px; }`,
        `}`,
      ].filter(x => x !== null).join('\n');
    });

    return blocks.join('\n\n');
  }

  function hiCSS(code) {
    return code
      .replace(/(\/\*.*?\*\/)/g, '<span class="token-comment">$1</span>')
      .replace(/(--[\w-]+)/g, '<span class="token-key">$1</span>')
      .replace(/(@media[^{]+)/g, '<span class="token-key">$1</span>')
      .replace(/:\s*('[^']+'[^;]*|[\d.]+px|[\d.]+|[a-z-]+(?:\([^)]*\))?)/g, (m, v) => `: <span class="token-val">${v}</span>`)
      .replace(/([{}])/g, '<span class="token-punct">$1</span>');
  }

  function renderOutput() {
    const raw = outputTab === 'vars' ? genVarsCSS() : genClassesCSS();
    document.getElementById('type-output').innerHTML = hiCSS(raw);
  }

  function switchOutputTab(tab) {
    outputTab = tab;
    document.getElementById('tab-vars').classList.toggle('active', tab === 'vars');
    document.getElementById('tab-classes').classList.toggle('active', tab === 'classes');
    renderOutput();
  }

  // ── CONTROLS ───────────────────────────────────────────────────
  function setPreview(mode) {
    previewMode = mode;
    document.getElementById('prev-desktop').classList.toggle('active', mode === 'desktop');
    document.getElementById('prev-mobile').classList.toggle('active', mode === 'mobile');
    render();
  }

  function readSettings() {
    settings.desktopBase = parseFloat(document.getElementById('d-base').value) || 16;
    settings.mobileBase = parseFloat(document.getElementById('m-base').value) || 15;
    settings.desktopRatio = parseFloat(document.getElementById('d-ratio').value) || 1.333;
    settings.mobileRatio = parseFloat(document.getElementById('m-ratio').value) || 1.250;
  }

  // ── FONT LOADING ───────────────────────────────────────────────
  const loadedFonts = new Set(['Fraunces', 'DM Mono']);

  function loadFont(which) {
    const input = document.getElementById(which + '-font');
    const name = input.value.trim();
    if (!name) return;

    if (which === 'heading') settings.headingFont = name;
    else settings.bodyFont = name;

    if (!loadedFonts.has(name)) {
      const encoded = encodeURIComponent(name);
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = `https://fonts.googleapis.com/css2?family=${encoded}:wght@300;400;500;600;700&display=swap`;
      document.head.appendChild(link);
      loadedFonts.add(name);
    }

    render();
    persistDraft();
  }

  // ── COPY ───────────────────────────────────────────────────────
  function copyCSS() {
    const raw = outputTab === 'vars' ? genVarsCSS() : genClassesCSS();
    navigator.clipboard.writeText(raw);
    const el = document.getElementById('copy-label');
    if (el) { const orig = el.textContent; el.textContent = 'Copied!'; setTimeout(() => el.textContent = orig, 2000); }
    showToast('Copied!');
  }

  // ── PERSIST ────────────────────────────────────────────────────
  function persistDraft() {
    clearTimeout(_draftTimer);
    _draftTimer = setTimeout(() => {
      localStorage.setItem(DRAFT_KEY, JSON.stringify({ settings, levels }));
    }, 400);
  }

  // ── INIT ───────────────────────────────────────────────────────
  (function () {
    // Restore draft
    try {
      const d = JSON.parse(localStorage.getItem(DRAFT_KEY));
      if (d) {
        if (d.settings) Object.assign(settings, d.settings);
        if (d.levels?.length === DEFAULT_LEVELS.length) levels = d.levels;
      }
    } catch (_) { }

    // Sync controls from settings
    document.getElementById('d-base').value = settings.desktopBase;
    document.getElementById('m-base').value = settings.mobileBase;
    document.getElementById('d-ratio').value = settings.desktopRatio;
    document.getElementById('m-ratio').value = settings.mobileRatio;
    document.getElementById('heading-font').value = settings.headingFont;
    document.getElementById('body-font').value = settings.bodyFont;

    // Load any non-default fonts
    [settings.headingFont, settings.bodyFont].forEach(name => {
      if (!loadedFonts.has(name)) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(name)}:wght@300;400;500;600;700&display=swap`;
        document.head.appendChild(link);
        loadedFonts.add(name);
      }
    });

    // Wire up scale controls
    ['d-base', 'm-base', 'd-ratio', 'm-ratio'].forEach(id => {
      document.getElementById(id).addEventListener('input', () => {
        readSettings();
        render();
        persistDraft();
      });
      document.getElementById(id).addEventListener('change', () => {
        readSettings();
        render();
        persistDraft();
      });
    });

    document.getElementById('heading-font').addEventListener('keydown', e => { if (e.key === 'Enter') loadFont('heading'); });
    document.getElementById('body-font').addEventListener('keydown', e => { if (e.key === 'Enter') loadFont('body'); });

    render();
  })();

  function openExportModal() {
    renderOutput();
    document.getElementById('export-modal').classList.add('open');
  }
  function closeExportModal() {
    document.getElementById('export-modal').classList.remove('open');
  }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeExportModal(); });
</script>

<?php require '../includes/footer.php'; ?>