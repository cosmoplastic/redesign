<?php
$pageTitle  = 'Saved Palettes — OKLCH Tools';
$activePage = 'saved-palettes';
require '../includes/header.php';
?>

      <main class="scrollable">

        <div class="topbar">
          <div class="topbar-greeting">
            <h2>Saved <em>palettes</em></h2>
            <p>Your saved color scales, ready to open or copy.</p>
          </div>
          <a href="/palette/" class="btn">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New palette
          </a>
        </div>

        <div class="palettes-grid" id="palettes-grid"></div>

        <div class="palettes-empty" id="palettes-empty" style="display:none">
          <div class="palettes-empty-icon">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 010 20"/><path d="M2 12h10"/></svg>
          </div>
          <h3>No saved palettes yet</h3>
          <p>Generate a palette and hit <em>Save palette</em> to keep it here.</p>
          <a href="/palette/" class="btn btn-pill" style="margin-top:4px">Open palette generator</a>
        </div>

      </main>
    </div>

    <div class="toast" id="toast"></div>

    <script src="/assets/color-math.js"></script>
    <script>
const STORAGE_KEY = 'oklch-palettes';

function loadPalettes() { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
function savePalettes(p) { localStorage.setItem(STORAGE_KEY, JSON.stringify(p)); }

function timeAgo(ts) {
  const d = Date.now() - ts, m = Math.floor(d / 60000), h = Math.floor(m / 60), dy = Math.floor(h / 24);
  if (m < 1)  return 'just now';
  if (m < 60) return `${m}m ago`;
  if (h < 24) return `${h}h ago`;
  if (dy < 7) return `${dy}d ago`;
  return new Date(ts).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function toSlug(s) { return s.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/, '') || 'color'; }

function genCSSForPalette(palette) {
  const stops = [50,100,200,300,400,500,600,700,800,900];
  const lines = [':root {'];
  palette.colors.forEach(c => {
    genScale(c.hex).forEach((hex, i) => {
      const [L,C,H] = rgbToOklch(...hexToRgb(hex));
      lines.push(`  --color-${toSlug(c.name)}-${stops[i]}: ${hex}; /* oklch(${(L*100).toFixed(1)}% ${C.toFixed(4)} ${H.toFixed(1)}) */`);
    });
    lines.push('');
  });
  lines.push('}');
  return lines.join('\n');
}

function buildCard(palette) {
  const card = document.createElement('div');
  card.className = 'palette-card fade-in';
  card.dataset.id = palette.id;

  // ── header ──
  const header = document.createElement('div');
  header.className = 'palette-card-header';

  const nameInput = document.createElement('input');
  nameInput.type = 'text';
  nameInput.className = 'palette-name-input';
  nameInput.value = palette.name;
  nameInput.maxLength = 48;
  nameInput.spellcheck = false;
  nameInput.addEventListener('change', () => {
    const all = loadPalettes();
    const p = all.find(x => x.id === palette.id);
    if (p) { p.name = nameInput.value.trim() || palette.name; savePalettes(all); }
  });

  const meta = document.createElement('div');
  meta.className = 'palette-card-meta';

  const colorCount = document.createElement('span');
  colorCount.className = 'palette-meta-chip';
  colorCount.textContent = `${palette.colors.length} scale${palette.colors.length > 1 ? 's' : ''}`;

  const timeEl = document.createElement('span');
  timeEl.className = 'palette-meta-chip';
  timeEl.textContent = timeAgo(palette.savedAt);

  meta.appendChild(colorCount);
  meta.appendChild(timeEl);
  header.appendChild(nameInput);
  header.appendChild(meta);
  card.appendChild(header);

  // ── swatches ──
  const swatches = document.createElement('div');
  swatches.className = 'palette-swatches';

  palette.colors.forEach(c => {
    const row = document.createElement('div');
    row.className = 'palette-scale-row';

    const label = document.createElement('span');
    label.className = 'palette-scale-label';
    label.textContent = c.name;

    const strip = document.createElement('div');
    strip.className = 'palette-scale-strip';
    genScale(c.hex).forEach(hex => {
      const block = document.createElement('div');
      block.className = 'palette-scale-block';
      block.style.background = hex;
      block.title = hex;
      strip.appendChild(block);
    });

    row.appendChild(label);
    row.appendChild(strip);
    swatches.appendChild(row);
  });

  card.appendChild(swatches);

  // ── footer ──
  const footer = document.createElement('div');
  footer.className = 'palette-card-footer';

  const openBtn = document.createElement('a');
  openBtn.className = 'btn';
  openBtn.href = `/palette/?load=${palette.id}`;
  openBtn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Open`;

  const copyBtn = document.createElement('button');
  copyBtn.className = 'btn';
  copyBtn.innerHTML = `<svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy CSS`;
  copyBtn.addEventListener('click', () => {
    navigator.clipboard.writeText(genCSSForPalette(palette));
    showToast('CSS copied!');
  });

  const deleteBtn = document.createElement('button');
  deleteBtn.className = 'palette-delete-btn';
  deleteBtn.title = 'Delete palette';
  deleteBtn.innerHTML = `<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;
  deleteBtn.addEventListener('click', () => {
    card.style.transition = 'opacity 0.2s, transform 0.2s';
    card.style.opacity = '0';
    card.style.transform = 'scale(0.97)';
    setTimeout(() => {
      savePalettes(loadPalettes().filter(p => p.id !== palette.id));
      card.remove();
      if (!document.querySelector('.palette-card')) {
        document.getElementById('palettes-grid').style.display = 'none';
        document.getElementById('palettes-empty').style.display = 'flex';
      }
    }, 200);
  });

  footer.appendChild(openBtn);
  footer.appendChild(copyBtn);
  footer.appendChild(deleteBtn);
  card.appendChild(footer);

  return card;
}

function render() {
  const palettes = loadPalettes();
  const grid = document.getElementById('palettes-grid');
  const empty = document.getElementById('palettes-empty');

  if (palettes.length === 0) {
    grid.style.display = 'none';
    empty.style.display = 'flex';
    return;
  }

  grid.style.display = '';
  empty.style.display = 'none';
  grid.innerHTML = '';

  // newest first
  [...palettes].reverse().forEach((p, i) => {
    const card = buildCard(p);
    card.style.animationDelay = (i * 0.05) + 's';
    grid.appendChild(card);
  });
}

render();
    </script>

<?php require '../includes/footer.php'; ?>
