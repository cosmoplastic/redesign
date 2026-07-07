<?php
$pageTitle = 'Saved Palettes & Type Guides — ONE design';
$pageDescription = 'Reopen saved palettes and type guides, keep your design tokens organized, and export them when you are ready.';
$activePage = 'saved-palettes';
require '../includes/header.php';
?>

<main class="scrollable">

  <div class="topbar">
    <div class="topbar-greeting">
      <h1>Saved <em>work</em></h1>
      <p>Your color palettes, gradients, and type guides.</p>
    </div>
  </div>

  <p class="section-label">Color palettes</p>
  <div class="palettes-grid" id="palettes-grid"></div>
  <div class="palettes-empty" id="palettes-empty" style="display:none">
    <div class="palettes-empty-icon">
      <svg viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" />
        <path d="M12 2a10 10 0 010 20" />
        <path d="M2 12h10" />
      </svg>
    </div>
    <h3>No saved palettes</h3>
    <p>Generate a palette and hit <em>Save palette</em> to keep it here.</p>
    <a href="/palette/" class="btn btn-primary" style="margin-top:4px">Open palette generator</a>
  </div>

  <p class="section-label" style="margin-top:40px">Gradients</p>
  <div class="palettes-grid" id="gradients-grid"></div>
  <div class="palettes-empty" id="gradients-empty" style="display:none">
    <div class="palettes-empty-icon">
      <svg viewBox="0 0 24 24">
        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
        <line x1="4" y1="22" x2="4" y2="15" />
      </svg>
    </div>
    <h3>No saved gradients</h3>
    <p>Build a gradient and hit <em>Save</em> to keep it here.</p>
    <a href="/gradient/" class="btn btn-primary" style="margin-top:4px">Open gradient studio</a>
  </div>

  <p class="section-label" style="margin-top:40px">Buttons</p>
  <div class="palettes-grid" id="buttons-grid"></div>
  <div class="palettes-empty" id="buttons-empty" style="display:none">
    <div class="palettes-empty-icon">
      <svg viewBox="0 0 24 24">
        <rect x="2" y="8" width="20" height="8" rx="3" />
      </svg>
    </div>
    <h3>No saved buttons</h3>
    <p>Design a button and hit <em>Save</em> to keep it here.</p>
    <a href="/button-maker/" class="btn btn-primary" style="margin-top:4px">Open button maker</a>
  </div>

  <p class="section-label" style="margin-top:40px">Type guides</p>
  <div class="palettes-grid" id="type-saves-grid"></div>
  <div class="palettes-empty" id="type-saves-empty" style="display:none">
    <div class="palettes-empty-icon">
      <svg viewBox="0 0 24 24">
        <line x1="3" y1="5" x2="21" y2="5" />
        <line x1="3" y1="10" x2="18" y2="10" />
        <line x1="3" y1="15" x2="14" y2="15" />
        <line x1="3" y1="20" x2="9" y2="20" />
      </svg>
    </div>
    <h3>No saved type guides</h3>
    <p>Set up your type scale and hit <em>Save</em> to keep it here.</p>
    <a href="/type-guide/" class="btn btn-primary" style="margin-top:4px">Open type guide</a>
  </div>

  <div id="export-footer" style="display:none; margin-top:48px; padding-top:24px; border-top:1px solid var(--border)">
    <button class="btn btn-primary" onclick="openExportModal()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
        <polyline points="7 10 12 15 17 10" />
        <line x1="12" y1="15" x2="12" y2="3" />
      </svg>
      Export all styles
    </button>
  </div>

</main>

<div class="export-modal" id="export-all-modal">
  <div class="export-modal-backdrop" onclick="closeExportModal()"></div>
  <div class="export-modal-box">
    <div class="export-modal-header">
      <span style="font-family:var(--mono);font-size:13px;font-weight:500">Export all styles</span>
      <div class="export-modal-actions">
        <button class="btn btn-primary" id="export-copy-btn" onclick="copyExportCSS()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
            <rect x="9" y="9" width="13" height="13" rx="2" />
            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
          </svg>
          Copy
        </button>
        <button class="export-modal-close" onclick="closeExportModal()" aria-label="Close">&times;</button>
      </div>
    </div>
    <div class="export-modal-body">
      <pre class="export-modal-code" id="export-modal-code"></pre>
    </div>
  </div>
</div>

</div>

<div class="toast" id="toast"></div>

<script src="/assets/color-math.js?v=<?= APP_VERSION ?>"></script>
<script>
  // ── SHARED HELPERS ────────────────────────────────────────────
  function timeAgo(ts) {
    const d = Date.now() - ts, m = Math.floor(d / 60000), h = Math.floor(m / 60), dy = Math.floor(h / 24);
    if (m < 1) return 'just now';
    if (m < 60) return `${m}m ago`;
    if (h < 24) return `${h}h ago`;
    if (dy < 7) return `${dy}d ago`;
    return new Date(ts).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  }

  // ── PALETTES ──────────────────────────────────────────────────
  const PAL_KEY = 'oklch-palettes';
  const TYPE_KEY = 'oklch-type-saves';
  const GRAD_KEY = 'oklch-gradients';
  const BTN_KEY = 'oklch-buttons';

  function loadPalettes() { return JSON.parse(localStorage.getItem(PAL_KEY) || '[]'); }
  function savePalettes(p) { localStorage.setItem(PAL_KEY, JSON.stringify(p)); }

  function genCSSForPalette(palette) {
    const stops = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
    return palette.colors.map(c => {
      return genScale(c.hex).map((hex, i) =>
        `--color-${c.name.toLowerCase().replace(/\s+/g, '-')}-${stops[i]}: ${hex};`
      ).join('\n');
    }).join('\n');
  }

  function buildPaletteCard(palette) {
    const card = document.createElement('div');
    card.className = 'palette-card fade-in';

    const header = document.createElement('div');
    header.className = 'palette-card-header';
    const nameInput = document.createElement('input');
    nameInput.className = 'palette-name-input'; nameInput.type = 'text';
    nameInput.value = palette.name; nameInput.spellcheck = false;
    nameInput.addEventListener('change', () => {
      const all = loadPalettes(), p = all.find(x => x.id === palette.id);
      if (p) { p.name = nameInput.value.trim() || palette.name; savePalettes(all); }
    });
    const meta = document.createElement('div'); meta.className = 'palette-card-meta';
    const cc = document.createElement('span'); cc.className = 'palette-meta-chip'; cc.textContent = `${palette.colors.length} scale${palette.colors.length > 1 ? 's' : ''}`;
    const te = document.createElement('span'); te.className = 'palette-meta-chip'; te.textContent = timeAgo(palette.savedAt);
    meta.appendChild(cc); meta.appendChild(te);
    header.appendChild(nameInput); header.appendChild(meta);
    card.appendChild(header);

    const swatches = document.createElement('div'); swatches.className = 'palette-swatches';
    palette.colors.forEach(c => {
      const row = document.createElement('div'); row.className = 'palette-scale-row';
      const label = document.createElement('span'); label.className = 'palette-scale-label'; label.textContent = c.name;
      const strip = document.createElement('div'); strip.className = 'palette-scale-strip';
      genScale(c.hex).forEach(hex => {
        const block = document.createElement('div'); block.className = 'palette-scale-block';
        block.style.background = hex; block.title = hex; strip.appendChild(block);
      });
      row.appendChild(label); row.appendChild(strip); swatches.appendChild(row);
    });
    card.appendChild(swatches);

    const footer = document.createElement('div'); footer.className = 'palette-card-footer';
    const openBtn = document.createElement('a'); openBtn.className = 'btn'; openBtn.href = `/palette/?load=${palette.id}`;
    openBtn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Open`;
    const copyBtn = document.createElement('button'); copyBtn.className = 'btn';
    copyBtn.innerHTML = `<svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy CSS`;
    copyBtn.addEventListener('click', () => { navigator.clipboard.writeText(genCSSForPalette(palette)); showToast('CSS copied!'); });
    const deleteBtn = document.createElement('button'); deleteBtn.className = 'palette-delete-btn'; deleteBtn.title = 'Delete';
    deleteBtn.innerHTML = `<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;
    deleteBtn.addEventListener('click', () => {
      card.style.transition = 'opacity .2s, transform .2s'; card.style.opacity = '0'; card.style.transform = 'scale(0.97)';
      setTimeout(() => {
        savePalettes(loadPalettes().filter(p => p.id !== palette.id)); card.remove();
        if (!document.querySelector('#palettes-grid .palette-card')) {
          document.getElementById('palettes-grid').style.display = 'none';
          document.getElementById('palettes-empty').style.display = 'flex';
        }
      }, 200);
    });
    footer.appendChild(openBtn); footer.appendChild(copyBtn); footer.appendChild(deleteBtn);
    card.appendChild(footer);
    return card;
  }

  // ── TYPE GUIDES ───────────────────────────────────────────────
  function loadTypeSaves() { return JSON.parse(localStorage.getItem(TYPE_KEY) || '[]'); }
  function saveTypeSaves(t) { localStorage.setItem(TYPE_KEY, JSON.stringify(t)); }

  function genTypeCSSFromSave(save) {
    const { settings: s, levels } = save;
    const px = (step, base, ratio) => (base * Math.pow(ratio, step)).toFixed(2);
    return [
      ':root {',
      `  --font-heading: '${s.headingFont}', serif;`,
      `  --font-body: '${s.bodyFont}', monospace;`,
      '', '  /* Desktop scale */',
      ...levels.map(l => `  --type-${l.key}: ${px(l.step, s.desktopBase, s.desktopRatio)}px;`),
      '', '  /* Line heights */',
      ...levels.map(l => `  --lh-${l.key}: ${l.lh};`),
      '', '  /* Letter spacing */',
      ...levels.map(l => `  --ls-${l.key}: ${l.ls};`),
      '}', '',
      '@media (max-width: 768px) {', '  :root {',
      ...levels.map(l => `    --type-${l.key}: ${px(l.step, s.mobileBase, s.mobileRatio)}px;`),
      '  }', '}',
    ].join('\n');
  }

  function buildTypeCard(save) {
    const card = document.createElement('div');
    card.className = 'palette-card fade-in';

    const header = document.createElement('div'); header.className = 'palette-card-header';
    const nameInput = document.createElement('input');
    nameInput.className = 'palette-name-input'; nameInput.type = 'text';
    nameInput.value = save.name; nameInput.spellcheck = false;
    nameInput.addEventListener('change', () => {
      const all = loadTypeSaves(), t = all.find(x => x.id === save.id);
      if (t) { t.name = nameInput.value.trim() || save.name; saveTypeSaves(all); }
    });
    const meta = document.createElement('div'); meta.className = 'palette-card-meta';
    const s = save.settings;
    const chip1 = document.createElement('span'); chip1.className = 'palette-meta-chip'; chip1.textContent = `${s.desktopBase}px · ${s.desktopRatio}`;
    const chip2 = document.createElement('span'); chip2.className = 'palette-meta-chip'; chip2.textContent = timeAgo(save.savedAt);
    meta.appendChild(chip1); meta.appendChild(chip2);
    header.appendChild(nameInput); header.appendChild(meta);
    card.appendChild(header);

    // Scale bar preview
    const preview = document.createElement('div'); preview.className = 'type-save-preview';
    const previewLevels = save.levels.slice(0, 5);
    const maxSize = Math.max(...previewLevels.map(l => s.desktopBase * Math.pow(s.desktopRatio, l.step)));
    previewLevels.forEach(l => {
      const size = s.desktopBase * Math.pow(s.desktopRatio, l.step);
      const row = document.createElement('div'); row.className = 'type-save-row';
      const tag = document.createElement('span'); tag.className = 'type-save-tag'; tag.textContent = l.label;
      const bar = document.createElement('div'); bar.className = 'type-save-bar'; bar.style.width = ((size / maxSize) * 100).toFixed(1) + '%';
      row.appendChild(tag); row.appendChild(bar); preview.appendChild(row);
    });
    card.appendChild(preview);

    const footer = document.createElement('div'); footer.className = 'palette-card-footer';
    const openBtn = document.createElement('a'); openBtn.className = 'btn'; openBtn.href = `/type-guide/?load=${save.id}`;
    openBtn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Open`;
    const copyBtn = document.createElement('button'); copyBtn.className = 'btn';
    copyBtn.innerHTML = `<svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy CSS`;
    copyBtn.addEventListener('click', () => { navigator.clipboard.writeText(genTypeCSSFromSave(save)); showToast('CSS copied!'); });
    const deleteBtn = document.createElement('button'); deleteBtn.className = 'palette-delete-btn'; deleteBtn.title = 'Delete';
    deleteBtn.innerHTML = `<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;
    deleteBtn.addEventListener('click', () => {
      card.style.transition = 'opacity .2s, transform .2s'; card.style.opacity = '0'; card.style.transform = 'scale(0.97)';
      setTimeout(() => {
        saveTypeSaves(loadTypeSaves().filter(t => t.id !== save.id)); card.remove();
        if (!document.querySelector('#type-saves-grid .palette-card')) {
          document.getElementById('type-saves-grid').style.display = 'none';
          document.getElementById('type-saves-empty').style.display = 'flex';
        }
      }, 200);
    });
    footer.appendChild(openBtn); footer.appendChild(copyBtn); footer.appendChild(deleteBtn);
    card.appendChild(footer);
    return card;
  }

  // ── GRADIENTS ─────────────────────────────────────────────────
  function loadGradients() { return JSON.parse(localStorage.getItem(GRAD_KEY) || '[]'); }
  function saveGradients(g) { localStorage.setItem(GRAD_KEY, JSON.stringify(g)); }

  function buildGradientCard(g) {
    const card = document.createElement('div');
    card.className = 'palette-card fade-in';

    const header = document.createElement('div'); header.className = 'palette-card-header';
    const nameInput = document.createElement('input');
    nameInput.className = 'palette-name-input'; nameInput.type = 'text';
    nameInput.value = g.name; nameInput.spellcheck = false;
    nameInput.addEventListener('change', () => {
      const all = loadGradients(), item = all.find(x => x.id === g.id);
      if (item) { item.name = nameInput.value.trim() || g.name; saveGradients(all); }
    });
    const meta = document.createElement('div'); meta.className = 'palette-card-meta';
    const chip1 = document.createElement('span'); chip1.className = 'palette-meta-chip';
    chip1.textContent = g.gradType === 'linear' ? `linear · ${g.angle}°` : 'radial';
    const chip2 = document.createElement('span'); chip2.className = 'palette-meta-chip';
    chip2.textContent = `${g.stops.length} stops`;
    const chip3 = document.createElement('span'); chip3.className = 'palette-meta-chip';
    chip3.textContent = timeAgo(g.savedAt);
    meta.appendChild(chip1); meta.appendChild(chip2); meta.appendChild(chip3);
    header.appendChild(nameInput); header.appendChild(meta);
    card.appendChild(header);

    // Gradient preview bar
    const preview = document.createElement('div');
    preview.style.cssText = `height:52px;border-radius:var(--r);margin-bottom:0;background:${g.css};border:1px solid var(--border)`;
    card.appendChild(preview);

    const footer = document.createElement('div'); footer.className = 'palette-card-footer';
    const openBtn = document.createElement('a'); openBtn.className = 'btn'; openBtn.href = `/gradient/?load=${g.id}`;
    openBtn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Open`;
    const copyBtn = document.createElement('button'); copyBtn.className = 'btn';
    copyBtn.innerHTML = `<svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy CSS`;
    copyBtn.addEventListener('click', () => { navigator.clipboard.writeText(`background: ${g.css};`); showToast('CSS copied!'); });
    const deleteBtn = document.createElement('button'); deleteBtn.className = 'palette-delete-btn'; deleteBtn.title = 'Delete';
    deleteBtn.innerHTML = `<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;
    deleteBtn.addEventListener('click', () => {
      card.style.transition = 'opacity .2s, transform .2s'; card.style.opacity = '0'; card.style.transform = 'scale(0.97)';
      setTimeout(() => {
        saveGradients(loadGradients().filter(x => x.id !== g.id)); card.remove();
        if (!document.querySelector('#gradients-grid .palette-card')) {
          document.getElementById('gradients-grid').style.display = 'none';
          document.getElementById('gradients-empty').style.display = 'flex';
        }
        updateExportFooter();
      }, 200);
    });
    footer.appendChild(openBtn); footer.appendChild(copyBtn); footer.appendChild(deleteBtn);
    card.appendChild(footer);
    return card;
  }

  // ── BUTTONS ───────────────────────────────────────────────────
  const SYSTEM_STACK = "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif";
  const loadedBtnFonts = new Set();

  function loadButtons() { try { return JSON.parse(localStorage.getItem(BTN_KEY) || '[]'); } catch (_) { return []; } }
  function saveButtons(b) { localStorage.setItem(BTN_KEY, JSON.stringify(b)); }

  function slugify(str, fallback) {
    return (str || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || fallback;
  }

  function btnFontStack(st) { return st.fontStack || SYSTEM_STACK; }
  function isGoogleFont(st) { return st.fontFamily && st.fontFamily !== 'System UI'; }
  function btnFontImport(st) { return `https://fonts.googleapis.com/css2?family=${encodeURIComponent(st.fontFamily)}:wght@300;400;500;600;700&display=swap`; }

  function ensureBtnFont(st) {
    if (!isGoogleFont(st) || loadedBtnFonts.has(st.fontFamily)) return;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = btnFontImport(st);
    document.head.appendChild(link);
    loadedBtnFonts.add(st.fontFamily);
  }

  function buttonPreviewStyle(st, variant) {
    const p = variant === 'primary' ? 'p' : variant === 'secondary' ? 's' : 't';
    const bg = st[p + 'BgOn'] ? st[p + 'Bg'] : 'transparent';
    const border = st[p + 'BorderOn'] ? st[p + 'Border'] : 'transparent';
    return `font-family:${btnFontStack(st)};font-size:${st.fontSize}px;font-weight:${st.fontWeight};`
      + `padding:${st.padV}px ${st.padH}px;border-radius:${st.radius}px;`
      + `border:1.5px solid ${border};background:${bg};color:${st[p + 'Text']};`
      + `opacity:${(st[p + 'Opacity'] ?? 100) / 100};`;
  }

  function genButtonCSS(b, slugOverride, skipImport) {
    const st = b.s, px = (v, sc) => Math.round(v * sc);
    const cls = 'btn-' + (slugOverride || slugify(b.name, 'button'));
    return [
      ...(!skipImport && isGoogleFont(st) ? [`@import url('${btnFontImport(st)}');`, ''] : []),
      `.${cls} {`,
      '  display: inline-flex;',
      '  align-items: center;',
      '  justify-content: center;',
      '  gap: 6px;',
      `  font-family: ${btnFontStack(st)};`,
      `  font-size: ${st.fontSize}px;`,
      `  font-weight: ${st.fontWeight};`,
      `  padding: ${st.padV}px ${st.padH}px;`,
      `  border-radius: ${st.radius}px;`,
      '  border: 1.5px solid transparent;',
      '  line-height: 1;',
      '  cursor: pointer;',
      '  white-space: nowrap;',
      '}',
      `.${cls}-lg { font-size: ${px(st.fontSize, 1.2)}px; padding: ${px(st.padV, 1.3)}px ${px(st.padH, 1.3)}px; }`,
      `.${cls}-sm { font-size: ${px(st.fontSize, 0.82)}px; padding: ${px(st.padV, 0.72)}px ${px(st.padH, 0.72)}px; }`,
      `.${cls}-primary {`,
      `  background: ${st.pBgOn ? st.pBg : 'transparent'};`,
      `  color: ${st.pText};`,
      ...(st.pBorderOn ? [`  border-color: ${st.pBorder};`] : []),
      ...((st.pOpacity ?? 100) < 100 ? [`  opacity: ${(st.pOpacity / 100).toFixed(2)};`] : []),
      '}',
      `.${cls}-secondary {`,
      `  background: ${st.sBgOn ? st.sBg : 'transparent'};`,
      `  color: ${st.sText};`,
      ...(st.sBorderOn ? [`  border-color: ${st.sBorder};`] : []),
      ...((st.sOpacity ?? 100) < 100 ? [`  opacity: ${(st.sOpacity / 100).toFixed(2)};`] : []),
      '}',
      ...(st.tText != null ? [
        `.${cls}-tertiary {`,
        `  background: ${st.tBgOn ? st.tBg : 'transparent'};`,
        `  color: ${st.tText};`,
        ...(st.tBorderOn ? [`  border-color: ${st.tBorder};`] : []),
        ...((st.tOpacity ?? 100) < 100 ? [`  opacity: ${(st.tOpacity / 100).toFixed(2)};`] : []),
        '}',
      ] : []),
    ].join('\n');
  }

  function buildButtonCard(b) {
    ensureBtnFont(b.s);
    const card = document.createElement('div');
    card.className = 'palette-card fade-in';

    const header = document.createElement('div'); header.className = 'palette-card-header';
    const nameInput = document.createElement('input');
    nameInput.className = 'palette-name-input'; nameInput.type = 'text';
    nameInput.value = b.name; nameInput.spellcheck = false;
    nameInput.addEventListener('change', () => {
      const all = loadButtons(), item = all.find(x => x.id === b.id);
      if (item) { item.name = nameInput.value.trim() || b.name; saveButtons(all); }
    });
    const meta = document.createElement('div'); meta.className = 'palette-card-meta';
    const c1 = document.createElement('span'); c1.className = 'palette-meta-chip'; c1.textContent = `${b.s.radius}px radius`;
    const c2 = document.createElement('span'); c2.className = 'palette-meta-chip'; c2.textContent = `${b.s.fontSize}px`;
    const c3 = document.createElement('span'); c3.className = 'palette-meta-chip'; c3.textContent = timeAgo(b.savedAt);
    meta.appendChild(c1); meta.appendChild(c2); meta.appendChild(c3);
    header.appendChild(nameInput); header.appendChild(meta);
    card.appendChild(header);

    const preview = document.createElement('div'); preview.className = 'btn-save-preview';
    preview.innerHTML =
      `<button class="btn-save-demo" style="${buttonPreviewStyle(b.s, 'primary')}">Primary</button>`
      + `<button class="btn-save-demo" style="${buttonPreviewStyle(b.s, 'secondary')}">Secondary</button>`
      + (b.s.tText != null ? `<button class="btn-save-demo" style="${buttonPreviewStyle(b.s, 'tertiary')}">Tertiary</button>` : '');
    card.appendChild(preview);

    const footer = document.createElement('div'); footer.className = 'palette-card-footer';
    const openBtn = document.createElement('a'); openBtn.className = 'btn'; openBtn.href = `/button-maker/?load=${b.id}`;
    openBtn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Open`;
    const copyBtn = document.createElement('button'); copyBtn.className = 'btn';
    copyBtn.innerHTML = `<svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy CSS`;
    copyBtn.addEventListener('click', () => { navigator.clipboard.writeText(genButtonCSS(b)); showToast('CSS copied!'); });
    const deleteBtn = document.createElement('button'); deleteBtn.className = 'palette-delete-btn'; deleteBtn.title = 'Delete';
    deleteBtn.innerHTML = `<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;
    deleteBtn.addEventListener('click', () => {
      card.style.transition = 'opacity .2s, transform .2s'; card.style.opacity = '0'; card.style.transform = 'scale(0.97)';
      setTimeout(() => {
        saveButtons(loadButtons().filter(x => x.id !== b.id)); card.remove();
        if (!document.querySelector('#buttons-grid .palette-card')) {
          document.getElementById('buttons-grid').style.display = 'none';
          document.getElementById('buttons-empty').style.display = 'flex';
        }
        updateExportFooter();
      }, 200);
    });
    footer.appendChild(openBtn); footer.appendChild(copyBtn); footer.appendChild(deleteBtn);
    card.appendChild(footer);
    return card;
  }

  // ── RENDER ────────────────────────────────────────────────────
  function renderPalettes() {
    const palettes = loadPalettes();
    const grid = document.getElementById('palettes-grid');
    const empty = document.getElementById('palettes-empty');
    if (!palettes.length) { grid.style.display = 'none'; empty.style.display = 'flex'; return; }
    grid.style.display = ''; empty.style.display = 'none'; grid.innerHTML = '';
    [...palettes].reverse().forEach((p, i) => { const c = buildPaletteCard(p); c.style.animationDelay = (i * .05) + 's'; grid.appendChild(c); });
  }

  function renderTypes() {
    const saves = loadTypeSaves();
    const grid = document.getElementById('type-saves-grid');
    const empty = document.getElementById('type-saves-empty');
    if (!saves.length) { grid.style.display = 'none'; empty.style.display = 'flex'; return; }
    grid.style.display = ''; empty.style.display = 'none'; grid.innerHTML = '';
    [...saves].reverse().forEach((t, i) => { const c = buildTypeCard(t); c.style.animationDelay = (i * .05) + 's'; grid.appendChild(c); });
  }

  function renderGradients() {
    const grads = loadGradients();
    const grid = document.getElementById('gradients-grid');
    const empty = document.getElementById('gradients-empty');
    if (!grads.length) { grid.style.display = 'none'; empty.style.display = 'flex'; return; }
    grid.style.display = ''; empty.style.display = 'none'; grid.innerHTML = '';
    [...grads].reverse().forEach((g, i) => { const c = buildGradientCard(g); c.style.animationDelay = (i * .05) + 's'; grid.appendChild(c); });
  }

  function renderButtons() {
    const btns = loadButtons();
    const grid = document.getElementById('buttons-grid');
    const empty = document.getElementById('buttons-empty');
    if (!btns.length) { grid.style.display = 'none'; empty.style.display = 'flex'; return; }
    grid.style.display = ''; empty.style.display = 'none'; grid.innerHTML = '';
    [...btns].reverse().forEach((b, i) => { const c = buildButtonCard(b); c.style.animationDelay = (i * .05) + 's'; grid.appendChild(c); });
  }

  renderPalettes();
  renderGradients();
  renderButtons();
  renderTypes();

  // ── EXPORT FOOTER VISIBILITY ──────────────────────────────────
  function updateExportFooter() {
    const hasSaved = loadPalettes().length || loadTypeSaves().length || loadGradients().length || loadButtons().length;
    document.getElementById('export-footer').style.display = hasSaved ? '' : 'none';
  }
  updateExportFooter();

  // ── EXPORT ALL STYLES MODAL ───────────────────────────────────
  const STOPS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];

  function genAllExportCSS() {
    const palettes = loadPalettes();
    const types = loadTypeSaves();
    const lines = [];

    if (palettes.length) {
      lines.push('/* ── Color Palettes ─────────────────────────── */');
      lines.push(':root {');
      palettes.forEach(p => {
        lines.push('');
        lines.push(`  /* ${p.name} */`);
        p.colors.forEach(c => {
          const slug = c.name.toLowerCase().replace(/\s+/g, '-');
          genScale(c.hex).forEach((hex, i) => {
            lines.push(`  --color-${slug}-${STOPS[i]}: ${hex};`);
          });
        });
      });
      lines.push('}');
    }

    if (types.length) {
      types.forEach(t => {
        if (lines.length) lines.push('');
        lines.push(`/* ── Type Guide: ${t.name} ─────────────────── */`);
        lines.push(genTypeCSSFromSave(t));
      });
    }

    const grads = loadGradients();
    if (grads.length) {
      if (lines.length) lines.push('');
      lines.push('/* ── Gradients ─────────────────────────────── */');
      lines.push(':root {');
      grads.forEach(g => {
        const slug = g.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || 'gradient';
        lines.push(`  --gradient-${slug}: ${g.css};`);
      });
      lines.push('}');
    }

    const buttons = loadButtons();
    if (buttons.length) {
      if (lines.length) lines.push('');
      lines.push('/* ── Buttons ───────────────────────────────── */');
      const usedSlugs = {};
      buttons.forEach(b => {
        let slug = slugify(b.name, 'button');
        if (usedSlugs[slug]) { usedSlugs[slug]++; slug = `${slug}-${usedSlugs[slug]}`; }
        else usedSlugs[slug] = 1;
        lines.push('');
        lines.push(genButtonCSS(b, slug, true)); // skip per-block @import — hoisted below
      });
    }

    // Hoist any Google-Font @imports to the very top (CSS requires them before all rules)
    const imports = [...new Set(buttons.filter(b => isGoogleFont(b.s)).map(b => btnFontImport(b.s)))];
    if (imports.length) {
      const importLines = imports.map(u => `@import url('${u}');`);
      importLines.push('');
      lines.unshift(...importLines);
    }

    return lines.join('\n');
  }

  function openExportModal() {
    const css = genAllExportCSS();
    document.getElementById('export-modal-code').textContent = css;
    document.getElementById('export-all-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeExportModal() {
    document.getElementById('export-all-modal').classList.remove('open');
    document.body.style.overflow = '';
  }

  function copyExportCSS() {
    const code = document.getElementById('export-modal-code').textContent;
    navigator.clipboard.writeText(code).then(() => {
      const btn = document.getElementById('export-copy-btn');
      const orig = btn.innerHTML;
      btn.textContent = 'Copied!';
      setTimeout(() => { btn.innerHTML = orig; }, 1600);
    });
  }

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeExportModal();
  });
</script>

<?php require '../includes/footer.php'; ?>