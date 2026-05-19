<?php
$pageTitle  = 'Export history — ONE design';
$activePage = 'export-history';
require '../includes/header.php';
?>

<main class="scrollable">
  <div class="topstrip">
    <div class="topstrip-title">Export history</div>
    <div class="topstrip-actions">
      <button class="btn btn-ghost" onclick="clearHistory()" id="clear-btn" style="display:none">Clear all</button>
    </div>
  </div>

  <div class="eh-wrap" id="eh-wrap">
    <div class="eh-empty" id="eh-empty" style="display:none">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="18" cy="5" r="3" /><circle cx="6" cy="12" r="3" /><circle cx="18" cy="19" r="3" />
        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
      </svg>
      <p>No exports yet. Copy CSS or JSON from any tool and it will appear here.</p>
    </div>
    <div class="eh-list" id="eh-list"></div>
  </div>
</main>

<script src="/assets/color-math.js?v=<?= APP_VERSION ?>"></script>
<script>
const TOOL_LABELS = { palette: 'Palette', gradient: 'Gradient', color: 'Color', type: 'Type guide' };

function timeAgo(ts) {
  const diff = Date.now() - ts;
  const mins = Math.floor(diff / 60000);
  const hrs  = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);
  if (mins < 1)  return 'just now';
  if (mins < 60) return mins + 'm ago';
  if (hrs  < 24) return hrs  + 'h ago';
  if (days < 7)  return days + 'd ago';
  return new Date(ts).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function truncate(str, max) {
  if (!str) return '';
  const lines = str.split('\n').slice(0, 4).join('\n');
  return lines.length > max ? lines.slice(0, max) + '…' : lines;
}

function loadHistory() {
  try { return JSON.parse(localStorage.getItem(EXPORT_HISTORY_KEY) || '[]'); } catch(_) { return []; }
}

function render() {
  const history = loadHistory();
  const list  = document.getElementById('eh-list');
  const empty = document.getElementById('eh-empty');
  const clearBtn = document.getElementById('clear-btn');

  if (!history.length) {
    empty.style.display = '';
    clearBtn.style.display = 'none';
    list.innerHTML = '';
    return;
  }

  empty.style.display = 'none';
  clearBtn.style.display = '';

  list.innerHTML = history.map((entry, i) => {
    const toolLabel   = TOOL_LABELS[entry.tool] || entry.tool;
    const preview     = truncate(entry.content || '', 300);
    const time        = timeAgo(entry.ts);
    return `
      <div class="eh-entry" data-id="${entry.id}">
        <div class="eh-entry-meta">
          <span class="eh-badge eh-badge-${entry.tool}">${toolLabel}</span>
          <span class="eh-format">${entry.format || ''}</span>
          <span class="eh-label">${entry.label || ''}</span>
          <span class="eh-time">${time}</span>
        </div>
        <pre class="eh-preview">${escHtml(preview)}</pre>
        <div class="eh-entry-actions">
          <button class="btn btn-sm" onclick="recopy(${i})">
            <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
            Copy again
          </button>
          <button class="btn btn-sm btn-ghost" onclick="deleteEntry('${entry.id}')">Delete</button>
        </div>
      </div>`;
  }).join('');
}

function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function recopy(i) {
  const history = loadHistory();
  if (!history[i]) return;
  navigator.clipboard.writeText(history[i].content || '').then(() => showToast('Copied!'));
}

function deleteEntry(id) {
  try {
    const h = loadHistory().filter(e => e.id !== id);
    localStorage.setItem(EXPORT_HISTORY_KEY, JSON.stringify(h));
    render();
  } catch(_){}
}

function clearHistory() {
  if (!confirm('Clear all export history?')) return;
  localStorage.removeItem(EXPORT_HISTORY_KEY);
  render();
}

render();
</script>

<?php require '../includes/footer.php'; ?>
