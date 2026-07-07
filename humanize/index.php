<?php
session_start();

// ── Secrets (gitignored file; env var takes priority for the key) ──────
$secretFile = __DIR__ . '/../includes/humanizer-secret.php';
if (file_exists($secretFile))
  require_once $secretFile;
if (!defined('ANTHROPIC_API_KEY'))
  define('ANTHROPIC_API_KEY', '');
if (!defined('HUMANIZER_PASS'))
  define('HUMANIZER_PASS', '');

$MODEL = 'claude-opus-4-8';
$apiKey = getenv('ANTHROPIC_API_KEY') ?: ANTHROPIC_API_KEY;
$keyConfigured = $apiKey && $apiKey !== 'sk-ant-REPLACE_ME';
$passConfigured = HUMANIZER_PASS !== '' && HUMANIZER_PASS !== 'change-me';

// ── The humanizer instructions (based on blader/humanizer's SKILL.md) ──
$HUMANIZER_SYSTEM = <<<'PROMPT'
You are a text humanizer. You rewrite text to remove the tells of AI-generated writing so it reads as if written by a thoughtful human, while preserving the original meaning, facts, register, and structure.

OUTPUT RULES (critical):
- Return ONLY the rewritten text. No preamble, no explanation, no commentary, no notes, no markdown code fences.
- Preserve the input's paragraph breaks and overall structure.
- If the text is already clean, return it essentially unchanged (but still enforce the hard rules: zero em/en dashes, straight quotes).
- Never add facts, claims, or content that isn't in the original. Never omit information. Never invent plausible-sounding details.

Rewrite to fix these patterns:

CONTENT
1. Undue emphasis on significance ("stands as", "testament", "pivotal", "marks a shift", "reflects broader") - state facts directly, drop significance claims.
2. Undue emphasis on notability (listing coverage: "media outlets", "cited in") - use specific examples or quotes, not mere coverage.
3. Superficial -ing analyses (sentences trailing "symbolizing", "reflecting", "showcasing") - convert to active claims with a named actor; cut fake depth.
4. Promotional language ("nestled", "vibrant", "breathtaking", "rich", "stunning", "renowned", "must-visit") - use neutral descriptors and concrete detail.
5. Vague attributions ("experts argue", "observers", "some critics") - name specific people, studies, or publications, or drop the claim.
6. Formulaic "challenges" sections ("Despite its... faces several challenges... Despite these") - use specific problems grounded in concrete examples.

LANGUAGE & GRAMMAR
7. Overused AI vocabulary ("crucially", "delve", "landscape", "interplay", "intricacies", "underscore", "tapestry", "garner") - common synonyms or restructure.
8. Copula avoidance ("serves as", "stands as", "boasts", "features") - restore "is", "are", "has".
9. Negative parallelisms ("not only X but Y"; tailing negations like "no guessing, no wasted motion") - plain declarative clauses.
10. Rule-of-three overuse (ideas forced into three-item lists) - keep only what belongs together; cut padding.
11. Elegant variation (cycling synonyms for the same thing) - pick one term and use it consistently.
12. False ranges ("from X to Y" where the endpoints are not a real scale) - separate claims; drop the range.
13. Passive voice and subjectless fragments ("No config needed", "results are preserved") - explicit subject, active voice.

STYLE
14. EM AND EN DASHES - HARD RULE: the final text must contain ZERO em dashes and ZERO en dashes. Replace each with a period, comma, colon, parentheses, or restructure the sentence.
15. Overuse of boldface - remove mechanical bolding.
16. Inline-header vertical lists (a bold header plus a colon introducing items) - integrate into prose.
17. Title case in headings - use sentence case (capitalize only the first word and proper nouns).
18. Emojis - remove them entirely; make the text stand on its own.
19. Curly or smart quotes - convert to straight quotes.

COMMUNICATION
20. Collaborative artifacts ("I hope this helps", "Of course!", "Let me know", "Would you like me to") - remove them; begin with content.
21. Knowledge-cutoff disclaimers and gap-filling ("As of [date]", "based on available information", "likely grew up") - state known facts or omit; never guess.
22. Sycophantic tone ("Great question!", "You're absolutely right") - stay neutral; drop the people-pleasing voice.

FILLER & HEDGING
23. Filler phrases: "in order to" becomes "to", "due to the fact that" becomes "because", "at this point in time" becomes "now", "has the ability to" becomes "can".
24. Excessive hedging (stacked qualifiers like "could potentially possibly might") - one clear qualifier, or none.
25. Generic positive conclusions ("bright future", "exciting times", "major step forward") - concrete outcomes, or cut.
26. Hyphenated word-pair overuse - drop the hyphen in predicative position ("the report is high quality") but keep it attributive ("a high-quality report").
27. Persuasive authority tropes ("the real question is", "at its core", "what really matters", "fundamentally") - state the claim directly.
28. Signposting ("let's dive in", "here's what you need to know", "without further ado") - begin with the content.
29. Fragmented headers (a header followed by a one-line restatement) - delete the restatement; start the section.
30. Diff-anchored writing (explaining what changed) - describe the current state instead.
31. Manufactured punchlines and staccato drama (runs of short quotable sentences) - vary sentence length; merge fragments.
32. Aphorism formulas ("X is the Y of Z", "the currency of", "becomes a trap") - a concrete, specific claim instead.
33. Conversational rhetorical openers ("Honestly?", "Look,", "Here's the thing") - state the claim directly.

PRESERVE (do NOT change):
- Core meaning, information, and paragraph count.
- The original register (formal, casual, technical) and the author's voice.
- Direct quotes, titles, proper names, code, and any examples being discussed.
- Specific, hard-to-fabricate detail (addresses, odd quotes, local references).
- Legitimate human prose. Good grammar, formal vocabulary, or mixed registers are not by themselves AI tells. Do not flatten real writing.

DO NOT over-correct. A single common word or transition in isolation, genuine asides, mixed feelings, dated slang, and natural sentence-length variety are all fine. When in doubt, make the smallest change that removes the tell.

Process: read the text and spot clustered tells, rewrite it naturally, then audit your own draft for anything that still sounds AI (especially stray em/en dashes and curly quotes) and fix it before returning. Return only the final text.
PROMPT;

// ── Passphrase gate ────────────────────────────────────────────────────
if (isset($_POST['passphrase'])) {
  if ($passConfigured && hash_equals(HUMANIZER_PASS, (string) $_POST['passphrase'])) {
    $_SESSION['humanizer'] = true;
    header('Location: /humanize/');
    exit;
  }
  header('Location: /humanize/?err=1');
  exit;
}
if (isset($_POST['logout'])) {
  unset($_SESSION['humanizer']);
  header('Location: /humanize/');
  exit;
}
$authed = !empty($_SESSION['humanizer']);

// ── Humanize action (authed JSON endpoint) ─────────────────────────────
if ($authed && ($_POST['action'] ?? '') === 'humanize') {
  header('Content-Type: application/json');
  header('Cache-Control: no-store');

  $text = (string) ($_POST['text'] ?? '');
  if (trim($text) === '') {
    echo json_encode(['ok' => false, 'error' => 'Enter some text first.']);
    exit;
  }
  if (mb_strlen($text) > 24000) {
    echo json_encode(['ok' => false, 'error' => 'Text is too long (24,000 character limit). Split it into chunks.']);
    exit;
  }
  if (!$keyConfigured) {
    echo json_encode(['ok' => false, 'error' => 'Server not configured: add your Anthropic API key to includes/humanizer-secret.php.']);
    exit;
  }
  if (!function_exists('curl_init')) {
    echo json_encode(['ok' => false, 'error' => 'Server is missing the PHP curl extension.']);
    exit;
  }

  $payload = [
    'model' => $MODEL,
    'max_tokens' => 16000,
    'system' => [
      [
        'type' => 'text',
        'text' => $HUMANIZER_SYSTEM,
        'cache_control' => ['type' => 'ephemeral'], // reuse the instructions cheaply across runs
      ]
    ],
    'messages' => [['role' => 'user', 'content' => $text]],
  ];

  $ch = curl_init('https://api.anthropic.com/v1/messages');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
      'content-type: application/json',
      'x-api-key: ' . $apiKey,
      'anthropic-version: 2023-06-01',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 180,
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $cerr = curl_error($ch);

  if ($resp === false) {
    echo json_encode(['ok' => false, 'error' => 'Network error reaching the API: ' . $cerr]);
    exit;
  }
  $data = json_decode($resp, true);
  if ($code !== 200) {
    $msg = $data['error']['message'] ?? ('API error (' . $code . ').');
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
  }
  $out = '';
  foreach (($data['content'] ?? []) as $b) {
    if (($b['type'] ?? '') === 'text')
      $out .= $b['text'];
  }
  echo json_encode(['ok' => true, 'text' => $out], JSON_UNESCAPED_UNICODE);
  exit;
}

header('Cache-Control: no-store');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Humanizer</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,400&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,300&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css">
  <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg">
  <link rel="shortcut icon" href="/assets/favicon/favicon.ico">
  <style>
    .hz-wrap {
      max-width: 1080px;
      margin: 0 auto;
      padding: 40px 24px 64px;
    }

    /* ── Passphrase gate ── */
    .hz-gate {
      max-width: 340px;
      margin: 12vh auto 0;
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: 36px 32px 30px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .hz-gate-title {
      font-family: var(--serif);
      font-size: 24px;
      font-weight: 300;
      color: var(--color-text-100);
    }

    .hz-gate-sub {
      font-family: var(--mono);
      font-size: 12px;
      color: var(--color-text-400);
      margin-top: -8px;
    }

    .hz-gate-form {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .hz-gate-input {
      width: 100%;
      background: var(--bg3);
      border: 1px solid var(--border2);
      border-radius: var(--r);
      padding: 10px 14px;
      font-family: var(--mono);
      font-size: 15px;
      color: var(--color-text-50);
      outline: none;
      transition: border-color .15s;
    }

    .hz-gate-input:focus {
      border-color: var(--border3);
    }

    .hz-gate-err {
      font-family: var(--mono);
      font-size: 11px;
      color: #f87171;
      line-height: 1.5;
    }

    .hz-back {
      font-family: var(--mono);
      font-size: 12px;
      color: var(--color-text-400);
      text-decoration: none;
      text-align: center;
    }

    .hz-back:hover {
      color: var(--color-text-200);
    }

    /* ── App ── */
    .hz-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 28px;
    }

    .hz-title {
      font-family: var(--serif);
      font-size: 30px;
      font-weight: 300;
      letter-spacing: -0.015em;
      color: var(--color-text-100);
      line-height: 1.1;
    }

    .hz-tagline {
      font-family: var(--mono);
      font-size: 12px;
      color: var(--color-text-400);
      margin-top: 6px;
    }

    .hz-warn {
      font-family: var(--mono);
      font-size: 12px;
      line-height: 1.6;
      color: #fbbf77;
      background: rgba(251, 146, 60, 0.08);
      border: 1px solid rgba(251, 146, 60, 0.25);
      border-radius: var(--r);
      padding: 10px 14px;
      margin-bottom: 20px;
    }

    .hz-warn code {
      font-family: var(--mono);
      color: #fdba74;
    }

    .hz-panes {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      align-items: stretch;
    }

    .hz-pane {
      display: flex;
      flex-direction: column;
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      overflow: hidden;
      min-height: 460px;
    }

    .hz-pane-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding: 12px 16px;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }

    .hz-pane-label {
      font-family: var(--mono);
      font-size: 10px;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--color-text-400);
    }

    .hz-textarea {
      flex: 1;
      width: 100%;
      resize: none;
      border: none;
      outline: none;
      background: transparent;
      padding: 16px;
      font-family: var(--mono);
      font-size: 13px;
      line-height: 1.7;
      color: var(--color-text-100);
    }

    .hz-textarea::placeholder {
      color: var(--color-text-500);
    }

    .hz-foot {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 12px 16px;
      border-top: 1px solid var(--border);
      flex-shrink: 0;
    }

    .hz-count {
      font-family: var(--mono);
      font-size: 11px;
      color: var(--color-text-400);
      font-variant-numeric: tabular-nums;
    }

    .hz-output {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      font-family: var(--mono);
      font-size: 13px;
      line-height: 1.7;
      color: var(--color-text-100);
      white-space: pre-wrap;
      word-wrap: break-word;
      text-transform: none !important;
    }

    .hz-output-error {
      color: #f87171;
    }

    .hz-placeholder {
      color: var(--color-text-500);
    }

    .hz-copy {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: transparent;
      border: 1px solid var(--border2);
      border-radius: var(--r-sm);
      padding: 4px 10px;
      font-family: var(--mono);
      font-size: 11px;
      color: var(--color-text-300);
      cursor: pointer;
      transition: border-color .15s, color .15s, opacity .15s;
    }

    .hz-copy svg {
      width: 12px;
      height: 12px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
    }

    .hz-copy:hover {
      border-color: var(--border3);
      color: var(--color-text-100);
    }

    .hz-copy:disabled {
      opacity: 0.4;
      cursor: default;
    }

    .hz-copy.is-active {
      border-color: var(--green);
      color: var(--green);
    }

    .hz-head-actions {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    /* Diff highlights */
    .hz-del {
      background: rgba(248, 113, 113, 0.15);
      color: #fca5a5;
      text-decoration: line-through;
      text-decoration-color: rgba(248, 113, 113, 0.55);
      border-radius: 2px;
      box-decoration-break: clone;
      -webkit-box-decoration-break: clone;
    }

    .hz-ins {
      background: rgba(110, 231, 160, 0.16);
      color: #86efac;
      text-decoration: none;
      border-radius: 2px;
      box-decoration-break: clone;
      -webkit-box-decoration-break: clone;
    }

    .hz-spin {
      display: inline-block;
      width: 12px;
      height: 12px;
      border: 2px solid rgba(255, 255, 255, 0.35);
      border-top-color: #fff;
      border-radius: 50%;
      animation: hzspin 0.7s linear infinite;
      margin-right: 2px;
      vertical-align: -1px;
    }

    @keyframes hzspin {
      to {
        transform: rotate(360deg);
      }
    }

    .toast {
      position: fixed;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%) translateY(12px);
      background: var(--bg3);
      border: 1px solid var(--border2);
      border-radius: var(--r);
      padding: 9px 16px;
      font-family: var(--mono);
      font-size: 12px;
      color: var(--color-text-100);
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s, transform .2s;
      z-index: 100;
    }

    .toast.show {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }

    @media (max-width: 720px) {
      .hz-wrap {
        padding: 28px 16px 56px;
      }

      .hz-panes {
        grid-template-columns: 1fr;
      }

      .hz-pane {
        min-height: 300px;
      }
    }
  </style>
</head>

<body>

  <div class="hz-wrap">

    <?php if (!$authed): ?>

      <div class="hz-gate">
        <div class="hz-gate-title">Humanizer</div>
        <p class="hz-gate-sub">Enter the passphrase to continue.</p>
        <form method="POST" class="hz-gate-form">
          <input type="password" name="passphrase" class="hz-gate-input" placeholder="Passphrase" autocomplete="off"
            autofocus>
          <?php if (isset($_GET['err'])): ?>
            <div class="hz-gate-err">Incorrect passphrase.</div>
          <?php endif; ?>
          <?php if (!$passConfigured): ?>
            <div class="hz-gate-err">Not configured yet: set <strong>HUMANIZER_PASS</strong> in
              includes/humanizer-secret.php.</div>
          <?php endif; ?>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;">Unlock</button>
        </form>
        <a href="/" class="hz-back">&larr; Back to site</a>
      </div>

    <?php else: ?>

      <header class="hz-header">
        <div>
          <h1 class="hz-title">Humanizer</h1>
          <p class="hz-tagline">Strip the AI tells out of any text.</p>
        </div>
        <form method="POST" style="margin:0;">
          <button class="btn" name="logout" value="1">Lock</button>
        </form>
      </header>

      <?php if (!$keyConfigured): ?>
        <div class="hz-warn">Server not configured &mdash; add your Anthropic API key to
          <code>includes/humanizer-secret.php</code> (or set the <code>ANTHROPIC_API_KEY</code> env var).</div>
      <?php endif; ?>

      <div class="hz-panes">
        <div class="hz-pane">
          <div class="hz-pane-head">
            <span class="hz-pane-label">Your text</span>
          </div>
          <textarea id="hz-input" class="hz-textarea" style="letter-spacing:0.01em;"
            placeholder="Paste your text here&hellip;" spellcheck="false"></textarea>
          <div class="hz-output" id="hz-input-diff" style="letter-spacing:0.01em;display:none"></div>
          <div class="hz-foot">
            <span class="hz-count" id="hz-count">0 chars</span>
            <button class="btn btn-primary" id="hz-run">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                <path d="M12 3l1.9 4.6L18.5 9.5 13.9 11.4 12 16l-1.9-4.6L5.5 9.5l4.6-1.9z" />
                <path d="M19 15l.8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8z" />
              </svg>
              Humanize
            </button>
          </div>
        </div>

        <div class="hz-pane">
          <div class="hz-pane-head">
            <span class="hz-pane-label">Humanized</span>
            <div class="hz-head-actions">
              <button class="hz-copy" id="hz-compare" disabled title="Highlight what changed">
                <svg viewBox="0 0 24 24">
                  <rect x="3" y="4" width="18" height="16" rx="2" />
                  <line x1="12" y1="4" x2="12" y2="20" />
                </svg>
                Compare
              </button>
              <button class="hz-copy" id="hz-copy" disabled>
                <svg viewBox="0 0 24 24">
                  <rect x="9" y="9" width="13" height="13" rx="2" />
                  <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
                </svg>
                Copy
              </button>
            </div>
          </div>
          <div class="hz-output" id="hz-output" style="letter-spacing:0.01em;"><span class="hz-placeholder">Your humanized
              text will appear here.</span></div>
        </div>
      </div>

    <?php endif; ?>

  </div>

  <div class="toast" id="toast"></div>

  <?php if ($authed): ?>
    <script>
      const input = document.getElementById('hz-input');
      const inputDiff = document.getElementById('hz-input-diff');
      const output = document.getElementById('hz-output');
      const runBtn = document.getElementById('hz-run');
      const copyBtn = document.getElementById('hz-copy');
      const compareBtn = document.getElementById('hz-compare');
      const countEl = document.getElementById('hz-count');
      let busy = false;
      let lastRun = null;   // { input, output } from the most recent humanize
      let comparing = false;

      function updateCount() {
        const n = input.value.length;
        countEl.textContent = n.toLocaleString() + (n === 1 ? ' char' : ' chars');
      }
      input.addEventListener('input', updateCount);
      updateCount();

      function showToast(msg) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(t._t);
        t._t = setTimeout(() => t.classList.remove('show'), 1800);
      }

      async function humanize() {
        if (busy) return;
        const text = input.value;
        if (!text.trim()) { showToast('Enter some text first'); return; }
        busy = true;
        runBtn.disabled = true;
        const orig = runBtn.innerHTML;
        runBtn.innerHTML = '<span class="hz-spin"></span> Humanizing&hellip;';
        copyBtn.disabled = true;
        compareBtn.disabled = true;
        if (comparing) exitCompare();
        output.classList.remove('hz-output-error');
        output.innerHTML = '<span class="hz-placeholder">Working&hellip;</span>';
        try {
          const fd = new FormData();
          fd.append('action', 'humanize');
          fd.append('text', text);
          const res = await fetch('/humanize/', { method: 'POST', body: fd });
          const raw = await res.text();
          let json;
          try { json = JSON.parse(raw); }
          catch (_) { throw new Error(raw.slice(0, 400).trim() || 'Empty response from server.'); }
          if (json.ok) {
            lastRun = { input: text, output: json.text };
            output.textContent = json.text;
            copyBtn.disabled = false;
            compareBtn.disabled = false;
          } else {
            output.textContent = json.error || 'Something went wrong.';
            output.classList.add('hz-output-error');
          }
        } catch (e) {
          output.textContent = (e && e.message) ? e.message : 'Request failed. Please try again.';
          output.classList.add('hz-output-error');
        } finally {
          busy = false;
          runBtn.disabled = false;
          runBtn.innerHTML = orig;
        }
      }

      runBtn.addEventListener('click', humanize);
      input.addEventListener('keydown', e => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') { e.preventDefault(); humanize(); }
      });
      copyBtn.addEventListener('click', () => {
        const txt = (lastRun && lastRun.output) || output.textContent;
        navigator.clipboard.writeText(txt).then(() => showToast('Copied!'));
      });

      // ── Compare (word-level diff, Myers) ─────────────────────────
      function escHtml(s) {
        return s.replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
      }
      // Each token is a word plus its trailing whitespace, so diffs land on whole words.
      function tokenizeWords(s) {
        return s.match(/\S+\s*|\s+/g) || [];
      }
      function diffTokens(a, b) {
        const N = a.length, M = b.length, MAX = N + M, OFF = MAX;
        const v = new Int32Array(2 * MAX + 1);
        const trace = [];
        let D = 0, done = false;
        for (; D <= MAX && !done; D++) {
          trace.push(v.slice());
          for (let k = -D; k <= D; k += 2) {
            let x;
            if (k === -D || (k !== D && v[k - 1 + OFF] < v[k + 1 + OFF])) x = v[k + 1 + OFF];
            else x = v[k - 1 + OFF] + 1;
            let y = x - k;
            while (x < N && y < M && a[x] === b[y]) { x++; y++; }
            v[k + OFF] = x;
            if (x >= N && y >= M) { done = true; break; }
          }
        }
        D--; // step back to the distance that finished
        const ops = [];
        let x = N, y = M;
        for (let d = D; d > 0; d--) {
          const vv = trace[d];
          const k = x - y;
          let prevK;
          if (k === -d || (k !== d && vv[k - 1 + OFF] < vv[k + 1 + OFF])) prevK = k + 1;
          else prevK = k - 1;
          const prevX = vv[prevK + OFF], prevY = prevX - prevK;
          while (x > prevX && y > prevY) { ops.push(['eq', a[x - 1]]); x--; y--; }
          if (x === prevX) { ops.push(['ins', b[y - 1]]); y--; }
          else { ops.push(['del', a[x - 1]]); x--; }
        }
        while (x > 0 && y > 0) { ops.push(['eq', a[x - 1]]); x--; y--; }
        while (x > 0) { ops.push(['del', a[--x]]); }
        while (y > 0) { ops.push(['ins', b[--y]]); }
        ops.reverse();
        return ops;
      }
      // side 'del' → original text (equal + removed), 'ins' → humanized (equal + added)
      function buildDiffHTML(ops, side) {
        let html = '', run = '', runType = null;
        const flush = () => {
          if (!run) return;
          if (runType === 'eq') html += escHtml(run);
          else if (runType === 'del') html += '<del class="hz-del">' + escHtml(run) + '</del>';
          else if (runType === 'ins') html += '<ins class="hz-ins">' + escHtml(run) + '</ins>';
          run = '';
        };
        for (const [t, val] of ops) {
          if ((side === 'del' && t === 'ins') || (side === 'ins' && t === 'del')) continue;
          if (t !== runType) { flush(); runType = t; }
          run += val;
        }
        flush();
        return html || '<span class="hz-placeholder">(no text)</span>';
      }

      function enterCompare() {
        if (!lastRun) return;
        const a = tokenizeWords(lastRun.input);
        const b = tokenizeWords(lastRun.output);
        if (a.length + b.length > 16000) { showToast('Text is too long to compare'); return; }
        const ops = diffTokens(a, b);
        inputDiff.innerHTML = buildDiffHTML(ops, 'del');
        output.innerHTML = buildDiffHTML(ops, 'ins');
        input.style.display = 'none';
        inputDiff.style.display = '';
        comparing = true;
        compareBtn.classList.add('is-active');
      }
      function exitCompare() {
        inputDiff.style.display = 'none';
        input.style.display = '';
        if (lastRun) output.textContent = lastRun.output;
        comparing = false;
        compareBtn.classList.remove('is-active');
      }
      compareBtn.addEventListener('click', () => {
        if (comparing) exitCompare();
        else enterCompare();
      });
    </script>
  <?php endif; ?>

</body>

</html>