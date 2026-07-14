/* ── Guided flow — "Build your design file" ──────────────────────────────
   Client-side flow state + chrome. While a flow is active this script:
   · injects the "Your design file" checklist into the sidebar (all pages)
   · injects the flow chrome bar at the top of the current tool page
   · wires the homepage hero CTA to start/resume the flow
   Tool internals are untouched — the flow is navigation + completion only.
   State: localStorage `one-design-flow` = { current, completed[], startedAt }.
─────────────────────────────────────────────────────────────────────────── */
(function () {
  'use strict';

  var KEY = 'one-design-flow';

  var STEPS = [
    { id: 'palette', label: 'Palette', tool: 'Palette', href: '/palette/', glyph: '<span class="df-glyph"><span class="df-sw" style="background:#3b82f6"></span><span class="df-sw" style="background:#e11d48"></span></span>' },
    { id: 'colors', label: 'Colors', tool: 'Colors', href: '/color-picker/', glyph: '' },
    { id: 'gradients', label: 'Gradients', tool: 'Gradients', href: '/gradient/', glyph: '<span class="df-glyph"><span class="df-grad"></span></span>' },
    { id: 'type', label: 'Type', tool: 'Type', href: '/type-guide/', glyph: '<span class="df-glyph"><span class="df-ag">Ag</span></span>' },
    { id: 'shadows', label: 'Shadows', tool: 'Shadows', href: '/shadow/', glyph: '' },
    { id: 'buttons', label: 'Buttons', tool: 'Buttons', href: '/button-maker/', glyph: '' },
    { id: 'borderglow', label: 'Border glow', tool: 'Border glow', href: '/border-glow/', glyph: '' }
  ];

  var ICONS = {
    file: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>',
    check: '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>',
    download: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>',
    trash: '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>',
    arrow: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>'
  };

  /* ── state ── */
  function load() {
    try {
      var s = JSON.parse(localStorage.getItem(KEY));
      return (s && Array.isArray(s.completed)) ? s : null;
    } catch (e) { return null; }
  }
  function save(s) { localStorage.setItem(KEY, JSON.stringify(s)); }
  function wipe() { localStorage.removeItem(KEY); }
  function isDone(s, id) { return s.completed.indexOf(id) !== -1; }
  function isComplete(s) { return s.completed.length >= STEPS.length; }

  var path = location.pathname;
  function pageStepIndex() {
    for (var i = 0; i < STEPS.length; i++) if (path === STEPS[i].href) return i;
    return -1;
  }

  /* First incomplete step at-or-after `from` (wraps); -1 when all complete. */
  function nextIncomplete(s, from) {
    for (var k = 0; k < STEPS.length; k++) {
      var i = (from + k) % STEPS.length;
      if (!isDone(s, STEPS[i].id)) return i;
    }
    return -1;
  }

  function go(i) { location.href = i === -1 ? '/design-file/' : STEPS[i].href; }

  /* ── design-file.css assembly (scaffold from completed steps) ── */
  function buildCss(s) {
    var lines = [
      '/* ══════════════════════════════════════════',
      '   design-file.css · ONE design',
      '   Built with the guided flow — ' + s.completed.length + '/7 steps',
      '   ══════════════════════════════════════════ */',
      ''
    ];
    STEPS.forEach(function (st, i) {
      lines.push('/* ' + ('0' + (i + 1)).slice(-2) + ' · ' + st.label + (isDone(s, st.id) ? '' : '  (skipped)') + ' */');
      if (isDone(s, st.id)) lines.push('/* exported from ' + location.origin + st.href + ' */');
      lines.push('');
    });
    return lines.join('\n');
  }

  function downloadFile(name, mime, text) {
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([text], { type: mime }));
    a.download = name;
    document.body.appendChild(a);
    a.click();
    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 0);
  }

  /* ── sidebar checklist ── */
  function renderChecklist(s) {
    var aside = document.querySelector('aside');
    var logo = aside && aside.querySelector('.sidebar-logo');
    if (!aside || !logo) return;
    var existing = aside.querySelector('.df-checklist');
    if (existing) existing.remove();

    var onTool = pageStepIndex();
    var currentIdx = onTool !== -1 ? onTool : Math.min(Math.max(s.current - 1, 0), 6);
    var fillPct = (Math.max(s.completed.length, 1) / STEPS.length) * 100;

    var rows = STEPS.map(function (st, i) {
      if (isDone(s, st.id)) {
        return '<a class="df-step df-step--done" href="' + st.href + '">' +
          '<span class="df-check">' + ICONS.check + '</span>' +
          '<span class="df-label">' + st.label + '</span>' + st.glyph + '</a>';
      }
      if (i === currentIdx && !isComplete(s)) {
        return '<a class="df-step df-step--current" href="' + st.href + '">' +
          '<span class="df-ring"></span>' + st.label + '<span class="df-now">Now</span></a>';
      }
      return '<a class="df-step" href="' + st.href + '"><span class="df-ring"></span>' + st.label + '</a>';
    }).join('');

    var section = document.createElement('div');
    section.className = 'df-checklist';
    section.innerHTML =
      '<div class="df-checklist-head">' +
      '<span class="df-checklist-label">Your design file</span>' +
      '<span class="df-checklist-count">' + s.completed.length + '/7</span>' +
      '</div>' +
      '<div class="df-progress"><div class="df-progress-fill" style="width:' + fillPct + '%"></div></div>' +
      '<div class="df-steps">' + rows + '</div>' +
      '<div class="df-actions">' +
      '<button type="button" class="df-action df-action--export' + (isComplete(s) ? ' df-action--ready' : '') + '">' + ICONS.download + 'Export design file</button>' +
      '<button type="button" class="df-action df-action--clear">' + ICONS.trash + 'Clear design</button>' +
      '</div>';

    logo.insertAdjacentElement('afterend', section);

    section.querySelector('.df-action--export').addEventListener('click', function () {
      if (isComplete(s)) location.href = '/design-file/';
      else downloadFile('design-file.css', 'text/css', buildCss(s));
    });
    section.querySelector('.df-action--clear').addEventListener('click', function () {
      if (!confirm('Clear your design file and reset the flow?')) return;
      wipe();
      section.remove();
      var chrome = document.querySelector('.df-chrome');
      if (chrome) chrome.remove();
      if (path === '/design-file/') location.href = '/';
    });
  }

  /* ── flow chrome bar on the current tool page ── */
  function renderChrome(s, idx) {
    var main = document.querySelector('main');
    if (!main || main.querySelector('.df-chrome')) return;
    var bar = document.createElement('div');
    bar.className = 'df-chrome';
    bar.innerHTML =
      '<div class="df-chrome-step">' + ICONS.file +
      '<span>Step ' + (idx + 1) + ' of 7 · ' + STEPS[idx].tool + '</span></div>' +
      '<div class="df-progress"><div class="df-progress-fill" style="width:' + (((idx + 1) / 7) * 100) + '%"></div></div>' +
      '<div class="df-chrome-actions">' +
      '<button type="button" class="df-btn-skip">Skip</button>' +
      '<button type="button" class="df-btn-continue">Add to file &amp; continue' + ICONS.arrow + '</button>' +
      '</div>';
    main.insertBefore(bar, main.firstChild);

    bar.querySelector('.df-btn-continue').addEventListener('click', function () {
      if (!isDone(s, STEPS[idx].id)) s.completed.push(STEPS[idx].id);
      var next = nextIncomplete(s, idx + 1);
      s.current = next === -1 ? 7 : next + 1;
      save(s);
      go(next);
    });
    bar.querySelector('.df-btn-skip').addEventListener('click', function () {
      var next = nextIncomplete(s, idx + 1);
      /* Skipping the current step: don't land right back on it unless it's
         the only one left — then the finish is unreachable, so stay linear. */
      if (next === idx) next = (idx + 1) % STEPS.length === idx ? next : nextIncomplete(s, idx + 1);
      s.current = next === -1 ? 7 : next + 1;
      save(s);
      go(next === idx ? -1 : next);
    });
  }

  /* ── boot ── */
  function init() {
    var state = load();

    /* Hero CTA — start (or resume) the flow. */
    document.querySelectorAll('.ch-cta').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var s = load();
        if (!s) { s = { current: 1, completed: [], startedAt: new Date().toISOString() }; save(s); }
        var next = isComplete(s) ? -1 : Math.min(Math.max(s.current - 1, 0), 6);
        go(next);
      });
    });

    if (!state) return;

    var idx = pageStepIndex();
    if (idx !== -1) {
      state.current = idx + 1;
      save(state);
      renderChrome(state, idx);
    }
    renderChecklist(state);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

  /* Shared with the finish page. */
  window.OneDesignFlow = { load: load, save: save, wipe: wipe, isComplete: isComplete, buildCss: buildCss, download: downloadFile, STEPS: STEPS };
})();
