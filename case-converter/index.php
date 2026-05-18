<?php
$pageTitle = 'Case Converter — OKLCH Tools';
$activePage = 'case-converter';
require '../includes/header.php';
?>

<main class="panel">
  <div class="topstrip">
    <div class="topstrip-title">Case <em>converter</em></div>
    <div class="topstrip-actions">
      <button class="btn" id="reset-btn">
        <svg viewBox="0 0 24 24">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
          <path d="M3 3v5h5" />
        </svg>
        Reset
      </button>
      <button class="btn" id="copy-all-btn">
        <svg viewBox="0 0 24 24">
          <rect x="9" y="9" width="13" height="13" rx="2" />
          <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
        </svg>
        Copy
      </button>
    </div>
  </div>

  <div class="scroll-area">

    <div class="input-section">
      <label class="field-label" for="main-input">Your text</label>
      <div class="textarea-wrap">
        <textarea id="main-input" placeholder="Enter your text here…" spellcheck="false"></textarea>
      </div>
      <div class="input-meta">
        <div class="input-stats">
          <span id="char-count">0 characters</span>
          <span class="stat-dot">·</span>
          <span id="word-count">0 words</span>
          <span class="stat-dot">·</span>
          <span id="line-count">0 lines</span>
        </div>
        <button class="btn" id="sample-btn">Insert sample text</button>
      </div>
    </div>

    <div class="tx-section">
      <h5 class="tx-section-title">Standard Transformations</h5>
      <div class="tx-grid cols-4" id="standard-grid"></div>
    </div>

    <div class="tx-section">
      <h5 class="tx-section-title">Developer Transformations</h5>
      <div class="tx-grid cols-5" id="developer-grid"></div>
    </div>

    <div class="tx-section">
      <h5 class="tx-section-title">Copy Clean Up</h5>
      <div class="tx-grid cols-auto" id="cleanup-grid"></div>
    </div>

  </div>
</main>
</div>

<div class="toast" id="toast"></div>

<script>
  const SAMPLE = `The quick brown fox jumps over the lazy dog. Pack my box with five dozen liquor jugs. How vainly men themselves amaze to win the palm, the oak, or the bays.`;

  function ws(t) { return t.trim().replace(/[^a-zA-Z0-9\s]/g, '').split(/\s+/).filter(Boolean); }
  function toCamel(t) { const w = ws(t); return w.map((v, i) => i === 0 ? v.toLowerCase() : v[0].toUpperCase() + v.slice(1).toLowerCase()).join(''); }
  function toPascal(t) { return ws(t).map(v => v[0].toUpperCase() + v.slice(1).toLowerCase()).join(''); }
  function toSnake(t) { return ws(t).map(v => v.toLowerCase()).join('_'); }
  function toKebab(t) { return ws(t).map(v => v.toLowerCase()).join('-'); }

  const TRANSFORMS = {
    standard: [
      { id: 'sentence', name: 'Sentence case', desc: 'First letter of each sentence', fn: t => t.replace(/(^\s*\w|[.!?]\s+\w)/g, c => c.toUpperCase()) },
      { id: 'title', name: 'Capitalized Case', desc: 'First letter of each word', fn: t => t.replace(/\b\w/g, c => c.toUpperCase()) },
      { id: 'lower', name: 'lower case', desc: 'All letters lowercase', fn: t => t.toLowerCase() },
      { id: 'upper', name: 'UPPER CASE', desc: 'All letters uppercase', fn: t => t.toUpperCase() },
    ],
    developer: [
      { id: 'camel', name: 'camelCase', desc: 'First word lower, rest capitalized', fn: toCamel },
      { id: 'pascal', name: 'PascalCase', desc: 'All words capitalized, no spaces', fn: toPascal },
      { id: 'snake', name: 'snake_case', desc: 'Words separated by underscores', fn: toSnake },
      { id: 'kebab', name: 'kebab-case', desc: 'Words separated by hyphens', fn: toKebab },
      { id: 'constant', name: 'CONSTANT_CASE', desc: 'Uppercase with underscores', fn: t => toSnake(t).toUpperCase() },
    ],
    cleanup: [
      { id: 'nodouble', name: 'Remove double spaces', desc: 'Replace multiple spaces with one', fn: t => t.replace(/ {2,}/g, ' ') },
      { id: 'trim', name: 'Trim whitespace', desc: 'Remove leading and trailing spaces', fn: t => t.split('\n').map(l => l.trim()).join('\n').trim() },
      { id: 'nopunct', name: 'Remove punctuation', desc: 'Strip all punctuation marks', fn: t => t.replace(/[^\w\s]/g, '') },
      { id: 'nonumbers', name: 'Remove numbers', desc: 'Strip all numeric characters', fn: t => t.replace(/[0-9]/g, '') },
      { id: 'nolines', name: 'Remove line breaks', desc: 'Collapse to a single line', fn: t => t.replace(/[\r\n]+/g, ' ').replace(/ {2,}/g, ' ').trim() },
      { id: 'reverse', name: 'Reverse text', desc: 'Characters in reverse order', fn: t => t.split('').reverse().join('') },
      { id: 'slug', name: 'URL slug', desc: 'Lowercase, hyphens, no specials', fn: t => t.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/[\s_]+/g, '-').replace(/-+/g, '-').replace(/^-+|-+$/g, '') },
      { id: 'alternating', name: 'aLtErNaTiNg CaSe', desc: 'Alternating upper and lowercase', fn: t => { let i = 0; return t.replace(/[a-zA-Z]/g, c => (i++ % 2 === 0 ? c.toLowerCase() : c.toUpperCase())); } },
      { id: 'inverted', name: 'iNVERTED cASE', desc: 'Swap upper and lowercase letters', fn: t => t.split('').map(c => c === c.toUpperCase() ? c.toLowerCase() : c.toUpperCase()).join('') },
    ],
  };

  let activeTransform = null;
  const inputEl = document.getElementById('main-input');
  function getText() { return inputEl.value; }

  function applyTransform(tx) {
    const t = getText(); if (!t.trim()) { showToast('Enter some text first'); return; }
    inputEl.value = tx.fn(t);
    activeTransform = tx;
    const card = document.querySelector(`[data-id="${tx.id}"]`);
    if (card) { card.classList.add('flash'); setTimeout(() => card.classList.remove('flash'), 600); }
    updateStats(); updatePreviews();
    showToast(tx.name + ' applied');
  }

  function makeCard(tx) {
    const card = document.createElement('div');
    card.className = 'tx-card'; card.dataset.id = tx.id;
    const nm = document.createElement('div'); nm.className = 'tx-card-name'; nm.textContent = tx.name;
    const ds = document.createElement('div'); ds.className = 'tx-card-desc'; ds.textContent = tx.desc;
    const pv = document.createElement('div'); pv.className = 'tx-card-preview'; pv.dataset.preview = tx.id; pv.textContent = '—';
    card.append(nm, ds, pv);
    card.addEventListener('click', () => applyTransform(tx));
    return card;
  }

  function renderGrid(id, transforms) {
    const el = document.getElementById(id);
    transforms.forEach(tx => el.appendChild(makeCard(tx)));
  }

  renderGrid('standard-grid', TRANSFORMS.standard);
  renderGrid('developer-grid', TRANSFORMS.developer);
  renderGrid('cleanup-grid', TRANSFORMS.cleanup);

  function updatePreviews() {
    const sample = getText().trim() || SAMPLE;
    [...TRANSFORMS.standard, ...TRANSFORMS.developer, ...TRANSFORMS.cleanup].forEach(tx => {
      const el = document.querySelector(`[data-preview="${tx.id}"]`); if (!el) return;
      try { const out = tx.fn(sample); el.textContent = out.slice(0, 52) + (out.length > 52 ? '…' : ''); } catch (e) { el.textContent = '—'; }
    });
  }

  function updateStats() {
    const t = getText();
    document.getElementById('char-count').textContent = t.length + ' character' + (t.length !== 1 ? 's' : '');
    const w = t.trim() ? t.trim().split(/\s+/).filter(Boolean).length : 0;
    document.getElementById('word-count').textContent = w + ' word' + (w !== 1 ? 's' : '');
    const l = t.trim() ? t.split('\n').length : 0;
    document.getElementById('line-count').textContent = l + ' line' + (l !== 1 ? 's' : '');
  }

  function showToast(msg) {
    let t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._timer); t._timer = setTimeout(() => t.classList.remove('show'), 1800);
  }

  inputEl.addEventListener('input', () => { updateStats(); updatePreviews(); });

  document.getElementById('reset-btn').addEventListener('click', () => {
    inputEl.value = ''; activeTransform = null; updateStats(); updatePreviews(); inputEl.focus();
  });

  document.getElementById('copy-all-btn').addEventListener('click', () => {
    const t = getText(); if (!t) { showToast('Nothing to copy'); return; }
    navigator.clipboard.writeText(t); showToast('Copied to clipboard');
  });

  document.getElementById('sample-btn').addEventListener('click', () => {
    inputEl.value = SAMPLE; updateStats(); updatePreviews();
    if (activeTransform) { inputEl.value = activeTransform.fn(inputEl.value); }
  });

  updateStats(); updatePreviews();
</script>

<?php require '../includes/footer.php'; ?>