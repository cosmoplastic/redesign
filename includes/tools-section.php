<?php
// ── "Or use the tools one by one" — uniform artifact tool grid ─────────
// Sits under the convergence hero. Frames the tools as an à-la-carte
// alternative to the guided flow: every tool's output is rendered as an
// identical 132×40 artifact chip on an identical stage so no single card
// dominates. Ported from the tools-section design handoff; descriptions
// carried over from the previous cards.
?>
<section class="ts-section" aria-label="Use the tools one by one">

  <div class="ts-header">
    <div class="ts-rule"></div>
    <div class="ts-head-text">
      <h2 class="ts-title">Or use the tools <em>one by one</em></h2>
      <p class="ts-sub">Every tool works standalone — anything you make can still be added to your design file.</p>
    </div>
    <div class="ts-rule"></div>
  </div>

  <div class="ts-grid">

    <a href="/palette/" class="ts-card">
      <div class="ts-stage">
        <span class="ts-chip ts-chip--palette" aria-hidden="true">
          <span style="background:#dbeafe"></span><span style="background:#93c5fd"></span><span
            style="background:#3b82f6"></span><span style="background:#1d4ed8"></span><span
            style="background:#fecdd3"></span><span style="background:#fb7185"></span><span
            style="background:#e11d48"></span><span style="background:#9f1239"></span>
        </span>
      </div>
      <div class="ts-row">
        <h2 class="ts-name">Palette generator</h2>
        <svg class="ts-arrow" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"></path>
        </svg>
      </div>
      <p class="ts-desc">Generate full 50–900 shade scales from any color using perceptually uniform OKLCH math.
        Export as CSS variables or Figma-ready JSON.</p>
    </a>

    <a href="/gradient/" class="ts-card">
      <div class="ts-stage">
        <span class="ts-chip ts-chip--gradient" aria-hidden="true"></span>
      </div>
      <div class="ts-row">
        <h2 class="ts-name">Gradient studio</h2>
        <svg class="ts-arrow" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"></path>
        </svg>
      </div>
      <p class="ts-desc">Build gradients that actually look good. Interpolate through OKLCH to avoid the grey, muddy
        band that ruins most CSS gradients.</p>
    </a>

    <a href="/color-picker/" class="ts-card">
      <div class="ts-stage">
        <span class="ts-chip ts-chip--picker" aria-hidden="true"><span class="ts-picker-handle"></span></span>
      </div>
      <div class="ts-row">
        <h2 class="ts-name">OKLCH color picker</h2>
        <svg class="ts-arrow" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"></path>
        </svg>
      </div>
      <p class="ts-desc">Pick colors natively in the OKLCH space. Drag the gamut canvas, spin the hue wheel, and export
        in any format — hex, oklch, rgb, hsl.</p>
    </a>

    <a href="/type-guide/" class="ts-card">
      <div class="ts-stage">
        <span class="ts-chip ts-chip--type" aria-hidden="true">
          <span class="ts-type-ag">Ag</span>
          <span class="ts-type-bars"><span></span><span></span></span>
        </span>
      </div>
      <div class="ts-row">
        <h2 class="ts-name">Type guide</h2>
        <svg class="ts-arrow" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"></path>
        </svg>
      </div>
      <p class="ts-desc">Set typography standards for desktop and mobile. Choose a modular scale ratio, load Google
        Fonts, and export CSS variables or utility classes.</p>
    </a>

    <a href="/button-maker/" class="ts-card">
      <div class="ts-stage">
        <span class="ts-chip ts-chip--button" aria-hidden="true"><span class="ts-btn-pill">PRIMARY</span></span>
      </div>
      <div class="ts-row">
        <h2 class="ts-name">Button maker</h2>
        <svg class="ts-arrow" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"></path>
        </svg>
      </div>
      <p class="ts-desc">Design primary and secondary buttons in three sizes. Dial in border radius, padding, font
        size, and weight — then export production-ready CSS.</p>
    </a>

    <a href="/shadow/" class="ts-card">
      <div class="ts-stage">
        <span class="ts-chip ts-chip--shadow" aria-hidden="true"></span>
      </div>
      <div class="ts-row">
        <h2 class="ts-name">Shadow &amp; elevation</h2>
        <svg class="ts-arrow" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"></path>
        </svg>
      </div>
      <p class="ts-desc">Build a semantic shadow scale tinted from your palette. Preview on light and dark surfaces and
        export as CSS tokens or Figma JSON.</p>
    </a>

    <a href="/border-glow/" class="ts-card">
      <div class="ts-stage">
        <span class="ts-chip ts-chip--glow" aria-hidden="true"></span>
      </div>
      <div class="ts-row">
        <h2 class="ts-name">Border glow</h2>
        <svg class="ts-arrow" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"></path>
        </svg>
      </div>
      <p class="ts-desc">Wrap a card, button, or search field in an animated conic-gradient border beam. Tune palette,
        geometry, and motion, then export pure CSS.</p>
    </a>

    <a href="/case-converter/" class="ts-card">
      <div class="ts-stage">
        <span class="ts-chip ts-chip--case" aria-hidden="true">aA <span class="ts-case-arrow">→</span> a-a</span>
      </div>
      <div class="ts-row">
        <h2 class="ts-name">Case converter</h2>
        <svg class="ts-arrow" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M5 12h14M12 5l7 7-7 7"></path>
        </svg>
      </div>
      <p class="ts-desc">Transform text between 13 different cases and formats — sentence, title, camel, snake, kebab,
        slug, and more. Plus copy clean-up utilities.</p>
    </a>

  </div>
</section>

<style>
  .ts-section {
    --ts-surface: oklch(17.4% 0.002 17.3);
    --ts-stagechip: oklch(21.4% 0.004 84.6);
    --ts-cream: oklch(95.2% 0.003 84.6);
    --ts-sub: oklch(66.5% 0.022 84.6);
    margin: 56px 0 64px;
    text-transform: none;
  }

  /* ── Divider header ── */
  .ts-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 0 4px;
  }

  .ts-rule {
    flex: 1;
    height: 1px;
    background: rgba(255, 255, 255, 0.14);
  }

  .ts-head-text {
    text-align: center;
  }

  .ts-title {
    font-family: var(--serif);
    font-size: 24px;
    font-weight: 300;
    color: var(--ts-cream);
    margin: 0;
  }

  .ts-title em {
    font-style: italic;
  }

  .ts-sub {
    font-family: var(--mono);
    /* font-size: 11px; */
    letter-spacing: 0.03em;
    color: var(--ts-sub);
    margin: 6px 0 0;
  }

  /* ── Card grid: 4 / 3 / 2 / 1 columns ── */
  .ts-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: 36px;
  }

  @media (max-width: 1099px) {
    .ts-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 819px) {
    .ts-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 559px) {
    .ts-grid {
      grid-template-columns: 1fr;
    }
  }

  /* ── Card ── */
  .ts-card {
    display: flex;
    flex-direction: column;
    background: var(--ts-surface);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    overflow: hidden;
    text-decoration: none;
    transition: border-color 0.15s ease;
  }

  .ts-card:hover {
    border-color: rgba(255, 255, 255, 0.24);
  }

  /* Every tool's output on an identical stage — no card dominates. */
  .ts-stage {
    height: 88px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .ts-chip {
    width: 132px;
    height: 40px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  /* ── Per-tool artifacts ── */
  .ts-chip--palette {
    overflow: hidden;
  }

  .ts-chip--palette>span {
    flex: 1;
    height: 100%;
  }

  .ts-chip--gradient {
    background: linear-gradient(90deg, oklch(72% 0.18 265), oklch(72% 0.18 340), oklch(72% 0.13 180));
  }

  .ts-chip--picker {
    background: linear-gradient(90deg, oklch(65% 0.15 0), oklch(65% 0.15 60), oklch(65% 0.15 120), oklch(65% 0.15 180), oklch(65% 0.15 240), oklch(65% 0.15 300), oklch(65% 0.15 360));
  }

  .ts-picker-handle {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
  }

  .ts-chip--type {
    background: var(--ts-stagechip);
    gap: 8px;
  }

  .ts-type-ag {
    font-family: var(--serif);
    font-size: 20px;
    font-weight: 300;
    color: var(--ts-cream);
    line-height: 1;
  }

  .ts-type-bars {
    display: flex;
    flex-direction: column;
    gap: 3px;
  }

  .ts-type-bars span {
    height: 3px;
    border-radius: 2px;
    background: rgba(255, 255, 255, 0.22);
  }

  .ts-type-bars span:first-child {
    width: 36px;
  }

  .ts-type-bars span:last-child {
    width: 26px;
  }

  .ts-chip--button {
    background: var(--ts-stagechip);
  }

  .ts-btn-pill {
    font-family: var(--mono);
    font-size: 9px;
    letter-spacing: 0.06em;
    padding: 6px 14px;
    border-radius: 100px;
    background: var(--ts-cream);
    color: oklch(14% 0 89.9);
  }

  /* The shadow itself is the artifact. */
  .ts-chip--shadow {
    background: var(--ts-stagechip);
    box-shadow: 0 6px 24px rgba(255, 255, 255, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  }

  /* Conic gradient ring via double background + border-box clipping. */
  .ts-chip--glow {
    border: 1.5px solid transparent;
    background-image:
      linear-gradient(var(--ts-surface), var(--ts-surface)),
      conic-gradient(from 90deg, oklch(72% 0.18 265), oklch(72% 0.18 340), oklch(72% 0.13 180), oklch(72% 0.18 265));
    background-origin: border-box;
    background-clip: padding-box, border-box;
  }

  .ts-chip--case {
    background: var(--ts-stagechip);
    font-family: var(--mono);
    font-size: 13px;
    color: oklch(88% 0.008 80.7);
    gap: 4px;
  }

  .ts-case-arrow {
    color: oklch(49.4% 0.033 82.4);
  }

  /* ── Title row + description ── */
  .ts-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 14px 18px 8px;
  }

  .ts-name {
    font-family: var(--serif);
    font-size: 15px;
    font-weight: 300;
    color: oklch(97.7% 0.002 67.8);
    margin: 0;
  }

  .ts-arrow {
    width: 12px;
    height: 12px;
    stroke: oklch(78.1% 0.014 82.4);
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
    flex-shrink: 0;
    transition: transform 0.15s ease;
  }

  .ts-card:hover .ts-arrow {
    transform: translateX(2px);
  }

  .ts-desc {
    font-family: var(--mono);
    font-size: 12px;
    line-height: 1.65;
    color: var(--ts-sub);
    margin: 0;
    padding: 0 18px 16px;
  }
</style>