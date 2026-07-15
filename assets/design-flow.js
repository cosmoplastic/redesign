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
    { id: 'palette', label: 'Palette', tool: 'Palette', href: '/palette/' },
    { id: 'colors', label: 'Colors', tool: 'Colors', href: '/color-picker/' },
    { id: 'gradients', label: 'Gradients', tool: 'Gradients', href: '/gradient/' },
    { id: 'type', label: 'Type', tool: 'Type', href: '/type-guide/' },
    { id: 'shadows', label: 'Shadows', tool: 'Shadows', href: '/shadow/' },
    { id: 'buttons', label: 'Buttons', tool: 'Buttons', href: '/button-maker/' },
    { id: 'borderglow', label: 'Border glow', tool: 'Border glow', href: '/border-glow/' }
  ];

  var ICONS = {
    file: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>',
    check: '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>',
    download: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>',
    trash: '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>',
    arrow: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>',
    chevron: '<svg class="df-chevron" viewBox="0 0 24 24" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>'
  };

  /* ── artifact collectors ─────────────────────────────────────────────
     Run on the step's own page at commit time. Each reads the tool's
     top-level page globals (script-scope let/const are visible across
     classic scripts) and/or its rendered export output — tool internals
     stay untouched. Return { css, meta?, data? } or null. ── */
  var COLLECT = {
    palette: function () {
      var css = (typeof genCSS === 'function') ? genCSS() : null;
      if (!css) return null;
      var list = (typeof colors !== 'undefined' && colors && colors.length) ? colors : null;
      return {
        css: css,
        meta: list ? { swatches: list.slice(0, 2).map(function (c) { return c.hex; }) } : null,
        data: list ? list.map(function (c) { return { name: c.name, hex: c.hex }; }) : null
      };
    },
    colors: function () {
      var picked = null;
      if (typeof oklchToHex === 'function' && typeof state !== 'undefined' && state && typeof state.L === 'number') {
        picked = oklchToHex(state.L, state.C, state.H);
      } else {
        var input = document.getElementById('hex-input');
        picked = input ? input.value : null;
      }
      if (!picked) return null;
      var lines = [':root {', '  --picked-color: ' + picked + ';'];
      var swatches = [picked];
      var saved = [];
      if (typeof savedColors !== 'undefined' && savedColors && savedColors.length) {
        savedColors.forEach(function (c, i) {
          var hex = c.hex || c;
          lines.push('  --saved-color-' + (i + 1) + ': ' + hex + ';');
          saved.push(hex);
          if (swatches.length < 2) swatches.push(hex);
        });
      }
      lines.push('}');
      return { css: lines.join('\n'), meta: { swatches: swatches }, data: { picked: picked, saved: saved } };
    },
    gradients: function () {
      if (typeof modernCSS !== 'function' || typeof compatCSS !== 'function') return null;
      var indent = function (t) {
        return t.split('\n').map(function (l) { return '  ' + l; }).join('\n');
      };
      var css = '.gradient {\n' + indent(compatCSS()) + '\n\n' + indent(modernCSS()) + '\n}';
      return {
        css: css,
        meta: { gradient: (typeof gradientCSSValue === 'function') ? gradientCSSValue() : null },
        data: (typeof stops !== 'undefined' && stops) ? {
          type: (typeof gradType !== 'undefined') ? gradType : null,
          angle: (typeof angle !== 'undefined') ? angle : null,
          stops: stops
        } : null
      };
    },
    type: function () {
      var css = (typeof genVarsCSS === 'function') ? genVarsCSS() : null;
      return css ? { css: css, data: (typeof settings !== 'undefined') ? settings : null } : null;
    },
    shadows: function () {
      var css = (typeof genCSS === 'function') ? genCSS() : null;
      return css ? { css: css } : null;
    },
    buttons: function () {
      var css = (typeof genCSS === 'function') ? genCSS() : null;
      return css ? { css: css } : null;
    },
    borderglow: function () {
      var pre = document.getElementById('css');
      if (!pre || !pre.textContent) return null;
      return { css: "/* Border glow — the tool's full export (CSS + markup + driver) */\n" + pre.textContent };
    }
  };

  function collect(id) {
    try { return COLLECT[id] ? COLLECT[id]() : null; }
    catch (e) { return null; }
  }

  /* ── state ── */
  function load() {
    try {
      var s = JSON.parse(localStorage.getItem(KEY));
      if (s && Array.isArray(s.completed)) {
        s.artifacts = s.artifacts || {};
        s.skipped = s.skipped || [];
        return s;
      }
      return null;
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

  /* ── design-file.css assembly from captured artifacts ── */
  function buildCss(s) {
    var lines = [
      '/* ══════════════════════════════════════════',
      '   design-file.css · ONE design',
      '   Built with the guided flow — ' + s.completed.length + '/7 steps',
      '   ' + new Date().toISOString().slice(0, 10),
      '   ══════════════════════════════════════════ */',
      ''
    ];
    STEPS.forEach(function (st, i) {
      var head = ('0' + (i + 1)).slice(-2) + ' · ' + st.label;
      var art = s.artifacts && s.artifacts[st.id];
      if (isDone(s, st.id) && art && art.css) {
        lines.push('/* ── ' + head + ' ─────────────────────────── */');
        lines.push(art.css, '');
      } else {
        lines.push('/* ── ' + head + ' · ' + (isDone(s, st.id) ? 'completed — no artifact captured' : 'skipped') + ' ── */', '');
      }
    });
    return lines.join('\n');
  }

  /* ── Figma-ready token JSON from the same artifacts ── */
  function buildJson(s) {
    var tokens = { name: 'ONE design — design file', generated: new Date().toISOString(), steps: {} };
    STEPS.forEach(function (st, i) {
      var art = s.artifacts && s.artifacts[st.id];
      tokens.steps[st.id] = {
        order: i + 1,
        label: st.label,
        completed: isDone(s, st.id),
        data: (art && (art.data || art.meta)) || null,
        css: (art && art.css) || null
      };
    });
    return tokens;
  }

  function downloadFile(name, mime, text) {
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([text], { type: mime }));
    a.download = name;
    document.body.appendChild(a);
    a.click();
    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 0);
  }

  /* ── checklist dropdown (anchored to the chrome's stepper button) ── */
  function buildDropdown(s, currentIdx) {
    var skipCount = s.skipped.filter(function (id) { return !isDone(s, id); }).length;
    var count = s.completed.length + ' done' + (skipCount ? ' · ' + skipCount + ' skipped' : '');
    var fillPct = (Math.max(s.completed.length, 1) / STEPS.length) * 100;

    var rows = STEPS.map(function (st, i) {
      if (isDone(s, st.id)) {
        return '<a class="df-step df-step--done" href="' + st.href + '">' +
          '<span class="df-check">' + ICONS.check + '</span>' +
          '<span class="df-label">' + st.label + '</span></a>';
      }
      if (i === currentIdx && !isComplete(s)) {
        return '<a class="df-step df-step--current" href="' + st.href + '">' +
          '<span class="df-ring"></span>' + st.label + '<span class="df-now">Now</span></a>';
      }
      if (s.skipped.indexOf(st.id) !== -1) {
        return '<a class="df-step df-step--skipped" href="' + st.href + '">' +
          '<span class="df-ring"></span>' + st.label + '<span class="df-skip-tag">Skipped</span></a>';
      }
      return '<a class="df-step" href="' + st.href + '"><span class="df-ring"></span>' + st.label + '</a>';
    }).join('');

    var panel = document.createElement('div');
    panel.className = 'df-dropdown';
    panel.innerHTML =
      '<div class="df-checklist-head">' +
      '<span class="df-checklist-label">Your design file</span>' +
      '<span class="df-checklist-count">' + count + '</span>' +
      '</div>' +
      '<div class="df-progress"><div class="df-progress-fill" style="width:' + fillPct + '%"></div></div>' +
      '<div class="df-steps">' + rows + '</div>' +
      '<div class="df-actions">' +
      '<button type="button" class="df-action df-action--export' + (isComplete(s) ? ' df-action--ready' : '') + '">' + ICONS.download + 'Export design file</button>' +
      '<button type="button" class="df-action df-action--clear">' + ICONS.trash + 'Clear design</button>' +
      '</div>';

    panel.querySelector('.df-action--export').addEventListener('click', function () {
      if (isComplete(s)) location.href = '/design-file/';
      else downloadFile('design-file.css', 'text/css', buildCss(s));
    });
    panel.querySelector('.df-action--clear').addEventListener('click', function () {
      if (!confirm('Clear your design file and reset the flow?')) return;
      wipe();
      if (path === '/design-file/') { location.href = '/'; return; }
      var chrome = document.querySelector('.df-chrome');
      if (chrome) chrome.remove();
      var wrap = document.querySelector('.df-stepper-wrap');
      if (wrap) wrap.remove();
    });
    return panel;
  }

  /* Stepper trigger + dropdown, ready to insert into a chrome bar. */
  function buildStepper(s, currentIdx, label) {
    var wrap = document.createElement('div');
    wrap.className = 'df-stepper-wrap';
    wrap.innerHTML =
      '<button type="button" class="df-stepper" aria-expanded="false" aria-haspopup="true">' +
      ICONS.file + '<span>' + label + '</span>' + ICONS.chevron + '</button>';
    var dd = buildDropdown(s, currentIdx);
    wrap.appendChild(dd);

    var btn = wrap.querySelector('.df-stepper');
    function close() {
      dd.classList.remove('open');
      btn.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    }
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = dd.classList.toggle('open');
      btn.classList.toggle('open', open);
      btn.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', function (e) {
      if (!wrap.contains(e.target)) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
    });
    return wrap;
  }

  /* ── flow chrome bar on the current tool page ── */
  function renderChrome(s, idx) {
    var main = document.querySelector('main');
    if (!main || main.querySelector('.df-chrome')) return;
    var bar = document.createElement('div');
    bar.className = 'df-chrome';

    bar.appendChild(buildStepper(s, idx, 'Step ' + (idx + 1) + ' of 7 · ' + STEPS[idx].tool));

    var progress = document.createElement('div');
    progress.className = 'df-progress';
    progress.innerHTML = '<div class="df-progress-fill" style="width:' + (((idx + 1) / 7) * 100) + '%"></div>';
    bar.appendChild(progress);

    var actions = document.createElement('div');
    actions.className = 'df-chrome-actions';
    actions.innerHTML =
      '<button type="button" class="df-btn-skip">Skip</button>' +
      '<button type="button" class="df-btn-continue">Add to file &amp; continue' + ICONS.arrow + '</button>';
    bar.appendChild(actions);

    main.appendChild(bar);   // docked at the bottom of the main column

    bar.querySelector('.df-btn-continue').addEventListener('click', function () {
      /* Capture the tool's current output — also on revisits, so edits
         recommit a fresh artifact. */
      var id = STEPS[idx].id;
      var art = collect(id);
      if (art) s.artifacts[id] = art;
      if (!isDone(s, id)) s.completed.push(id);
      var k = s.skipped.indexOf(id);
      if (k !== -1) s.skipped.splice(k, 1);   // completing un-skips
      var next = nextIncomplete(s, idx + 1);
      s.current = next === -1 ? 7 : next + 1;
      save(s);
      go(next);
    });
    bar.querySelector('.df-btn-skip').addEventListener('click', function () {
      var id = STEPS[idx].id;
      if (!isDone(s, id) && s.skipped.indexOf(id) === -1) s.skipped.push(id);
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
        if (!s) { s = { current: 1, completed: [], skipped: [], artifacts: {}, startedAt: new Date().toISOString() }; save(s); }
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

    /* Finish page: swap the static chrome label for the same stepper
       dropdown, so the checklist (and Export/Clear) stay reachable. */
    if (path === '/design-file/') {
      var fin = document.querySelector('.df-finish-chrome');
      if (fin) {
        var label = fin.querySelector('span');
        if (label) label.remove();
        fin.insertBefore(buildStepper(state, -1, isComplete(state) ? 'All 7 steps complete' : 'Your design file'), fin.firstChild);
      }
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

  /* Shared with the finish page. */
  window.OneDesignFlow = { load: load, save: save, wipe: wipe, isComplete: isComplete, buildCss: buildCss, buildJson: buildJson, download: downloadFile, STEPS: STEPS };
})();
