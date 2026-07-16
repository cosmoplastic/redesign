<?php
$pageTitle = 'Your design file — ONE design';
$pageDescription = 'All seven guided-flow steps converge into one design file — download as CSS or Figma JSON.';
$activePage = 'design-file';
$shellClass = 'full-height';
require '../includes/header.php';
?>

<style>
  .df-finish {
    text-transform: none;
  }

  /* ── Chrome (complete) ── */
  .df-finish-chrome {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 12px 24px;
    border-top: 1px solid var(--border2);
    background: var(--bg2);
    flex-shrink: 0;
  }

  .df-finish-chrome span {
    font-family: var(--mono);
    font-size: 11px;
    color: var(--color-text-100);
    flex-shrink: 0;
  }

  .df-finish-scroll {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
  }

  /* ── Thread arrival band ── */
  .df-arrival {
    position: relative;
    height: 300px;
    overflow: hidden;
    flex-shrink: 0;
  }

  .df-arrival svg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
  }

  /* The file illustration sits over the convergence point — the threads
     vanish behind it, pouring into the file. 84% matches the y≈210/250
     convergence in the (stretching) thread viewBox. */
  .df-arrival-chip-wrap {
    position: absolute;
    left: 50%;
    top: 84%;
    transform: translate(-50%, -50%);
    z-index: 3;
  }

  .df-arrival-file {
    display: block;
    height: 190px;
    width: auto;
  }

  /* ── Completion copy block ── */
  .df-done {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 28px 56px 72px;
    text-align: center;
  }

  .df-done-badge {
    font-family: var(--mono);
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--green);
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .df-done-badge-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 6px var(--green);
  }

  .df-done-title {
    font-family: var(--serif);
    font-size: 48px;
    font-weight: 300;
    line-height: 1.1;
    letter-spacing: -0.02em;
    color: var(--color-text-100);
    margin: 14px 0 0;
  }

  .df-done-title em {
    font-style: italic;
    color: var(--green);
  }

  .df-done-desc {
    font-family: var(--mono);
    font-size: 12.5px;
    color: var(--color-text-300);
    line-height: 1.7;
    margin: 12px 0 0;
    max-width: 460px;
  }

  .df-done-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 30px;
    width: min(360px, 100%);
  }

  .df-dl {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: var(--mono);
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 0.03em;
    padding: 14px 26px;
    border-radius: 100px;
    border: 1px solid var(--color-text-100);
    background: var(--bg);
    color: var(--color-text-100);
    cursor: pointer;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    transition: transform 0.15s ease, filter 0.15s ease;
  }

  .df-dl:hover {
    transform: scale(1.02);
    filter: brightness(1.15);
  }

  .df-dl svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
  }

  .df-ghost-row {
    display: flex;
    gap: 10px;
  }

  .df-ghost {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--mono);
    font-size: 11px;
    letter-spacing: 0.04em;
    padding: 11px;
    border-radius: 100px;
    border: 1px solid var(--border2);
    background: none;
    color: var(--color-text-300);
    cursor: pointer;
    transition: border-color 0.13s, color 0.13s;
  }

  .df-ghost:hover {
    border-color: var(--border3);
    color: var(--color-text-100);
  }

  .df-done-note {
    font-family: var(--mono);
    font-size: 10px;
    letter-spacing: 0.05em;
    color: var(--color-text-500);
    margin-top: 2px;
  }

  @media (prefers-reduced-motion: reduce) {
    .df-arrival path {
      animation: none !important;
    }
  }

  /* Threads travel an exact multiple of their dash period (--df-travel per
     path) so the infinite loop restarts invisibly. */
  @keyframes df-dash-in {
    to {
      stroke-dashoffset: var(--df-travel, -400px);
    }
  }
</style>

<main class="panel df-finish">

  <script>
    // Guard: the finish screen only exists once all 7 steps are complete.
    (function () {
      try {
        var s = JSON.parse(localStorage.getItem('one-design-flow'));
        if (!s || !Array.isArray(s.completed)) { location.replace('/'); return; }
        if (s.completed.length < 7) {
          var steps = ['/palette/', '/color-picker/', '/gradient/', '/type-guide/', '/shadow/', '/button-maker/', '/border-glow/'];
          location.replace(steps[Math.min(Math.max((s.current || 1) - 1, 0), 6)]);
        }
      } catch (e) { location.replace('/'); }
    })();
  </script>

  <div class="df-finish-scroll">

    <!-- Thread arrival — the seven tool threads pour in and land in the file -->
    <div class="df-arrival">
      <svg viewBox="0 0 1440 250" preserveAspectRatio="none" aria-hidden="true">
        <path d="M 180 -10 C 160 60, 250 100, 380 130 C 520 162, 645 190, 700 205" fill="none" stroke="oklch(0.64 0.21 300.6)" stroke-width="2.6" opacity="0.6" stroke-dasharray="10 14" style="--df-travel:-408px;animation:df-dash-in 12s linear infinite;"></path>
        <path d="M 360 -10 C 335 65, 430 105, 505 140 C 590 175, 660 195, 706 207" fill="none" stroke="oklch(60% 0.19 265)" stroke-width="2.6" opacity="0.55" stroke-dasharray="8 12" style="--df-travel:-400px;animation:df-dash-in 10s linear infinite;"></path>
        <path d="M 540 -10 C 515 60, 575 115, 615 150 C 665 185, 695 200, 712 209" fill="none" stroke="oklch(62% 0.2 340)" stroke-width="2.6" opacity="0.55" stroke-dasharray="3 8" style="--df-travel:-396px;animation:df-dash-in 9s linear infinite;"></path>
        <path d="M 720 -10 C 735 55, 695 115, 712 160 C 720 185, 719 198, 720 210" fill="none" stroke="oklch(68% 0.14 180)" stroke-width="2.6" opacity="0.6" stroke-dasharray="12 10" style="--df-travel:-396px;animation:df-dash-in 11s linear infinite;"></path>
        <path d="M 900 -10 C 925 60, 855 115, 805 150 C 762 180, 738 197, 728 210" fill="none" stroke="oklch(75% 0.15 90)" stroke-width="2.6" opacity="0.55" stroke-dasharray="4 9" style="--df-travel:-403px;animation:df-dash-in 8s linear infinite;"></path>
        <path d="M 1080 -10 C 1110 70, 990 115, 905 155 C 830 190, 765 203, 734 211" fill="none" stroke="oklch(65% 0.18 25)" stroke-width="2.6" opacity="0.6" stroke-dasharray="9 13" style="--df-travel:-396px;animation:df-dash-in 13s linear infinite;"></path>
        <path d="M 1260 -10 C 1295 80, 1140 125, 1010 165 C 890 200, 795 207, 740 212" fill="none" stroke="oklch(70% 0.16 140)" stroke-width="2.6" opacity="0.5" stroke-dasharray="6 11" style="--df-travel:-408px;animation:df-dash-in 10.5s linear infinite;"></path>
      </svg>
      <div class="df-arrival-chip-wrap">
        <img class="df-arrival-file" src="<?= asset_versioned_path('/assets/icons/cssicon.svg') ?>" alt="design-file.css — complete">
      </div>
    </div>

    <div class="df-done">
      <div class="df-done-badge"><span class="df-done-badge-dot"></span>Design file complete</div>
      <h1 class="df-done-title">Your file is <em>ready.</em></h1>
      <p class="df-done-desc">Everything you made — palette, gradients, type, shadows, buttons, glow — in one export.</p>
      <div class="df-done-actions">
        <button type="button" class="df-dl" id="df-download">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          Download design-file.css
        </button>
        <div class="df-ghost-row">
          <button type="button" class="df-ghost" id="df-figma">Figma JSON</button>
          <button type="button" class="df-ghost" id="df-copy">Copy CSS</button>
        </div>
        <div class="df-done-note">Saved to your workspace · edit any step anytime</div>
      </div>
    </div>

  </div>

  <div class="df-finish-chrome">
    <span>All 7 steps complete</span>
    <div class="df-progress"><div class="df-progress-fill" style="width:100%"></div></div>
  </div>
</main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var flow = window.OneDesignFlow;
    if (!flow) return;
    var state = flow.load();
    if (!state) return;

    document.getElementById('df-download').addEventListener('click', function () {
      flow.download('design-file.css', 'text/css', flow.buildCss(state));
    });

    document.getElementById('df-figma').addEventListener('click', function () {
      flow.download('design-file.tokens.json', 'application/json', JSON.stringify(flow.buildJson(state), null, 2));
    });

    var copyBtn = document.getElementById('df-copy');
    copyBtn.addEventListener('click', function () {
      navigator.clipboard.writeText(flow.buildCss(state)).then(function () {
        copyBtn.textContent = 'Copied';
        setTimeout(function () { copyBtn.textContent = 'Copy CSS'; }, 1600);
      });
    });
  });
</script>

<?php require '../includes/footer.php'; ?>
