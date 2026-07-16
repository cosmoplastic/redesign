<?php
// ── Convergence hero — "Every tool in. One file out." ──────────────────
// Homepage hero introducing the guided flow: seven "tool threads" flow in
// and converge into a single design-file.css chip. Ported from the design
// handoff (desktop 1130×480, mobile 390×800), implemented responsively:
// the thread canvas stretches with the container (SVG viewBox +
// percentage-placed labels) while copy, chip, and CTA keep their designed
// sizes and anchors.
?>
<section class="ch-hero" aria-label="Build your design file">

  <!-- ═══════════ DESKTOP ═══════════ -->
  <div class="ch-fit ch-fit--desktop">
    <div class="ch-stage ch-stage--desktop">
      <svg class="ch-threads" viewBox="0 0 1130 480" preserveAspectRatio="none" aria-hidden="true">
        <path d="M -20 70 C 120 20, 260 130, 400 75 S 600 190, 740 160 S 880 235, 928 254" fill="none"
          stroke="oklch(0.64 0.21 300.6)" stroke-width="1.5" opacity="0.6" stroke-dasharray="10 14"
          style="--ch-travel:-408px;animation:ch-dash-in 12s linear infinite;"></path>
        <path d="M -20 120 C 140 180, 280 60, 430 140 S 620 110, 730 190 S 878 242, 928 256" fill="none"
          stroke="oklch(60% 0.19 265)" stroke-width="1.5" opacity="0.55" stroke-dasharray="8 12"
          style="--ch-travel:-400px;animation:ch-dash-in 10s linear infinite;"></path>
        <path d="M -20 185 C 130 130, 250 260, 400 190 S 590 260, 700 215 S 876 250, 928 258" fill="none"
          stroke="oklch(62% 0.2 340)" stroke-width="1.5" opacity="0.55" stroke-dasharray="3 8"
          style="--ch-travel:-396px;animation:ch-dash-in 9s linear infinite;"></path>
        <path d="M -20 265 C 150 320, 290 200, 430 285 S 620 230, 730 275 S 882 262, 928 260" fill="none"
          stroke="oklch(68% 0.14 180)" stroke-width="1.5" opacity="0.6" stroke-dasharray="12 10"
          style="--ch-travel:-396px;animation:ch-dash-in 11s linear infinite;"></path>
        <path d="M -20 330 C 120 280, 270 390, 420 320 S 610 350, 720 300 S 880 268, 928 262" fill="none"
          stroke="oklch(75% 0.15 90)" stroke-width="1.5" opacity="0.55" stroke-dasharray="4 9"
          style="--ch-travel:-403px;animation:ch-dash-in 8s linear infinite;"></path>
        <path d="M -20 395 C 140 450, 280 330, 430 400 S 620 320, 740 340 S 884 274, 928 264" fill="none"
          stroke="oklch(65% 0.18 25)" stroke-width="1.5" opacity="0.6" stroke-dasharray="9 13"
          style="--ch-travel:-396px;animation:ch-dash-in 13s linear infinite;"></path>
        <path d="M -20 450 C 130 400, 290 480, 440 420 S 630 440, 750 370 S 888 280, 928 266" fill="none"
          stroke="oklch(70% 0.16 140)" stroke-width="1.5" opacity="0.5" stroke-dasharray="6 11"
          style="--ch-travel:-408px;animation:ch-dash-in 10.5s linear infinite;"></path>
      </svg>

      <div class="ch-scrim ch-scrim--desktop"></div>

      <!-- Label lefts are percentages of the 1130 design width so they track
           the stretched threads; tops stay px (stage height is fixed). -->
      <div class="ch-label" style="left:17.7%;top:44px;color:oklch(0.64 0.21 300.6);transform:rotate(-2deg);">gradients
      </div>
      <div class="ch-label" style="left:14.8%;top:86px;color:oklch(60% 0.19 265);transform:rotate(-1deg);">palette
      </div>
      <div class="ch-label" style="left:20.35%;top:162px;color:oklch(62% 0.2 340);transform:rotate(2deg);">colors</div>
      <div class="ch-label" style="left:13.27%;top:238px;color:oklch(68% 0.14 180);transform:rotate(-2deg);">type scale
      </div>
      <div class="ch-label" style="left:25.66%;top:302px;color:oklch(75% 0.15 90);transform:rotate(3deg);">shadows</div>
      <div class="ch-label" style="left:15.93%;top:358px;color:oklch(65% 0.18 25);transform:rotate(-3deg);">buttons
      </div>
      <div class="ch-label" style="left:28.32%;top:424px;color:oklch(70% 0.16 140);transform:rotate(2deg);">border glow
      </div>

      <h1 class="ch-headline ch-headline--desktop">Every tool in.<br><em>One file out.</em></h1>

      <div class="ch-chip ch-chip--desktop">
        <svg class="ch-file ch-file--desktop" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
          <polyline points="13 2 13 9 20 9"></polyline>
        </svg>
        <span class="ch-chip-name">design-file.css</span>
      </div>

      <div class="ch-cta-block ch-cta-block--desktop">
        <p class="ch-copy">Walk the tools in order — palettes, type, shadows, buttons all converge into a single design
          file. Export once, as CSS or Figma JSON.</p>
        <button type="button" class="ch-cta">Start your design file<svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M5 12h14M12 5l7 7-7 7"></path>
          </svg></button>
      </div>
    </div>
  </div>

  <!-- ═══════════ MOBILE ═══════════ -->
  <div class="ch-fit ch-fit--mobile">
    <div class="ch-stage ch-stage--mobile">
      <svg class="ch-threads" viewBox="0 0 390 800" preserveAspectRatio="none" aria-hidden="true">
        <path d="M -10 130 C 80 90, 150 190, 110 260 S 160 380, 186 448" fill="none" stroke="oklch(0.64 0.21 300.6)"
          stroke-width="1.5" opacity="0.6" stroke-dasharray="10 14" style="--ch-travel:-408px;animation:ch-dash-in 12s linear infinite;">
        </path>
        <path d="M 60 -10 C 20 90, 120 140, 80 230 S 170 370, 190 450" fill="none" stroke="oklch(60% 0.19 265)"
          stroke-width="1.5" opacity="0.55" stroke-dasharray="8 12" style="--ch-travel:-400px;animation:ch-dash-in 10s linear infinite;">
        </path>
        <path d="M 170 -10 C 210 70, 130 150, 175 240 S 180 380, 193 452" fill="none" stroke="oklch(62% 0.2 340)"
          stroke-width="1.5" opacity="0.55" stroke-dasharray="3 8" style="--ch-travel:-396px;animation:ch-dash-in 9s linear infinite;">
        </path>
        <path d="M 280 -10 C 240 80, 320 160, 265 250 S 205 380, 197 452" fill="none" stroke="oklch(68% 0.14 180)"
          stroke-width="1.5" opacity="0.6" stroke-dasharray="12 10" style="--ch-travel:-396px;animation:ch-dash-in 11s linear infinite;">
        </path>
        <path d="M 400 110 C 320 80, 280 190, 320 260 S 225 390, 201 450" fill="none" stroke="oklch(75% 0.15 90)"
          stroke-width="1.5" opacity="0.55" stroke-dasharray="4 9" style="--ch-travel:-403px;animation:ch-dash-in 8s linear infinite;">
        </path>
        <path d="M -10 320 C 70 290, 130 360, 120 400 S 175 420, 189 452" fill="none" stroke="oklch(65% 0.18 25)"
          stroke-width="1.5" opacity="0.6" stroke-dasharray="9 13" style="--ch-travel:-396px;animation:ch-dash-in 13s linear infinite;">
        </path>
        <path d="M 400 330 C 330 310, 270 380, 285 410 S 215 430, 203 452" fill="none" stroke="oklch(70% 0.16 140)"
          stroke-width="1.5" opacity="0.5" stroke-dasharray="6 11" style="--ch-travel:-408px;animation:ch-dash-in 10.5s linear infinite;">
        </path>
      </svg>

      <div class="ch-scrim ch-scrim--top"></div>
      <div class="ch-scrim ch-scrim--bottom"></div>

      <!-- Label lefts are percentages of the 390 design width. -->
      <div class="ch-label" style="left:26.92%;top:196px;color:oklch(0.64 0.21 300.6);transform:rotate(-4deg);">gradients
      </div>
      <div class="ch-label" style="left:7.18%;top:252px;color:oklch(60% 0.19 265);transform:rotate(3deg);">palette</div>
      <div class="ch-label" style="left:38.46%;top:290px;color:oklch(62% 0.2 340);transform:rotate(-2deg);">colors</div>
      <div class="ch-label" style="left:67.18%;top:238px;color:oklch(68% 0.14 180);transform:rotate(4deg);">type scale
      </div>
      <div class="ch-label" style="left:76.92%;top:300px;color:oklch(75% 0.15 90);transform:rotate(-3deg);">shadows
      </div>
      <div class="ch-label" style="left:12.31%;top:352px;color:oklch(65% 0.18 25);transform:rotate(2deg);">buttons</div>
      <div class="ch-label" style="left:64.62%;top:388px;color:oklch(70% 0.16 140);transform:rotate(-2deg);">border glow
      </div>

      <div class="ch-headwrap--mobile">
        <div class="ch-badge"><span class="ch-badge-dot"></span>New — guided flow</div>
        <h1 class="ch-headline ch-headline--mobile">Every tool in.<br><em>One file out.</em></h1>
      </div>

      <div class="ch-chip-wrap--mobile">
        <div class="ch-chip ch-chip--mobile">
          <svg class="ch-file ch-file--mobile" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
            <polyline points="13 2 13 9 20 9"></polyline>
          </svg>
          <span class="ch-chip-name">design-file.css</span>
        </div>
      </div>

      <div class="ch-cta-block ch-cta-block--mobile">
        <p class="ch-copy">Walk the tools in order — palettes, type, shadows, buttons all converge into a single design
          file. Export once, as CSS or Figma JSON.</p>
        <button type="button" class="ch-cta ch-cta--mobile">Start your design file<svg viewBox="0 0 24 24"
            aria-hidden="true">
            <path d="M5 12h14M12 5l7 7-7 7"></path>
          </svg></button>
      </div>
    </div>
  </div>
</section>

<style>
  .ch-hero {
    margin: 4px 0 40px;
    text-transform: none;
  }

  /* Fluid width: the thread canvas stretches with the container (SVG
     preserveAspectRatio=none + percentage-placed labels) while copy, chip,
     and CTA keep their designed sizes and anchors. */
  .ch-fit--desktop {
    /* max-width: 1130px; */
  }

  .ch-fit--mobile {
    display: none;
    /* max-width: 520px; */
  }

  /* Desktop composition needs ~800px of stage; below that, the vertical
     mobile choreography takes over. */
  @media (max-width: 1099px) {
    .ch-fit--desktop {
      display: none;
    }

    .ch-fit--mobile {
      display: block;
    }
  }

  .ch-stage {
    position: relative;
    background: var(--bg2);
    border: 1px solid var(--border2);
    border-radius: var(--r-xl);
    overflow: hidden;
  }

  .ch-stage--desktop {
    width: 100%;
    height: 480px;
  }

  .ch-stage--mobile {
    width: 100%;
    height: 800px;
  }

  .ch-threads {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
  }

  .ch-threads path {
    stroke-width: 2.6;
  }

  .ch-label {
    position: absolute;
    font-family: var(--mono);
    letter-spacing: 0.14em;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .ch-stage--desktop .ch-label {
    font-size: 10px;
  }

  .ch-stage--mobile .ch-label {
    font-size: 9px;
    z-index: 1;
  }

  .ch-scrim {
    position: absolute;
    z-index: 2;
    pointer-events: none;
  }

  /* .ch-scrim--desktop {
    top: 0;
    right: 0;
    bottom: 0;
    width: 58%;
    background: linear-gradient(90deg, transparent 0%, color-mix(in srgb, var(--bg2) 55%, transparent) 38%, color-mix(in srgb, var(--bg2) 92%, transparent) 70%, var(--bg2) 100%);
  } */

  .ch-scrim--top {
    top: 0;
    left: 0;
    right: 0;
    height: 190px;
    background: linear-gradient(180deg, var(--bg2) 0%, color-mix(in srgb, var(--bg2) 85%, transparent) 55%, transparent 100%);
  }

  .ch-scrim--bottom {
    bottom: 0;
    left: 0;
    right: 0;
    height: 330px;
    background: linear-gradient(0deg, var(--bg2) 0%, var(--bg2) 55%, color-mix(in srgb, var(--bg2) 85%, transparent) 78%, transparent 100%);
  }

  .ch-headline {
    font-family: var(--serif);
    font-weight: 300;
    letter-spacing: -0.02em;
    color: var(--color-text-100);
    /* h1 element — kill the UA heading margin; layout comes from the
       absolute anchors (desktop) / headwrap (mobile). */
    margin: 0;
  }

  .ch-headline em {
    font-style: italic;
  }

  .ch-headline--desktop {
    position: absolute;
    right: 52px;
    top: 46px;
    text-align: right;
    z-index: 4;
    font-size: 46px;
    line-height: 1.1;
  }

  .ch-headline--mobile {
    font-size: 36px;
    line-height: 1.12;
    margin-top: 10px;
  }

  .ch-headwrap--mobile {
    position: absolute;
    top: 44px;
    left: 0;
    right: 0;
    text-align: center;
    z-index: 4;
    padding: 0 24px;
  }

  .ch-badge {
    font-family: var(--mono);
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--green);
    display: flex;
    align-items: center;
    gap: 6px;
    justify-content: center;
  }

  .ch-badge-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 6px var(--green);
  }

  .ch-chip {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--bg);
    border: 1.5px solid var(--color-text-100);
    border-radius: 100px;
    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.6), 0 0 0 8px rgba(255, 255, 255, 0.03);
  }

  .ch-chip--desktop {
    position: absolute;
    right: 52px;
    /* Sits just below the two-line headline (ends ≈147px); the retarget
       script reads this position, so the threads follow it. */
    top: 200px;
    transform: translateY(-50%);
    z-index: 3;
    padding: 14px 22px;
  }

  .ch-chip--mobile {
    padding: 13px 20px;
  }

  .ch-chip-wrap--mobile {
    position: absolute;
    left: 50%;
    top: 470px;
    transform: translate(-50%, -50%);
    z-index: 3;
  }

  .ch-chip-name {
    font-family: var(--mono);
    font-size: 12px;
    letter-spacing: 0.02em;
    color: var(--color-text-100);
  }

  .ch-file {
    flex-shrink: 0;
    fill: none;
    stroke: var(--color-text-100);
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
  }

  .ch-file--desktop {
    width: 16px;
    height: 16px;
  }

  .ch-file--mobile {
    width: 15px;
    height: 15px;
  }

  .ch-cta-block--desktop {
    position: absolute;
    right: 52px;
    bottom: 42px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 14px;
    z-index: 4;
    /* % of the stage: keeps the paragraph off the thread zone as the
       canvas narrows (the scrim gradient is % based too). */
    max-width: min(400px, 46%);
  }

  .ch-cta-block--mobile {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    z-index: 4;
    padding: 0 28px;
  }

  .ch-copy {
    font-family: var(--mono);
    font-size: 14.5px;
    line-height: 1.7;
    color: var(--color-text-200);
    margin: 0;
    margin-top: 8px;
  }

  .ch-cta-block--desktop .ch-copy {
    max-width: 100%;
    text-align: right;
  }

  .ch-cta-block--mobile .ch-copy {
    text-align: center;
  }

  .ch-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: var(--mono);
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 0.03em;
    padding: 13px 26px;
    border: none;
    border-radius: 100px;
    background: var(--color-text-100);
    color: var(--color-primary-900);
    cursor: pointer;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    transition: transform 0.15s ease, filter 0.15s ease;
    white-space: nowrap;
  }

  .ch-cta:hover {
    transform: scale(1.02);
    filter: brightness(1.05);
  }

  .ch-cta:focus-visible {
    outline: 2px solid var(--color-text-100);
    outline-offset: 3px;
  }

  .ch-cta svg {
    width: 13px;
    height: 13px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
  }

  .ch-cta--mobile {
    justify-content: center;
    padding: 15px 28px;
    align-self: stretch;
  }

  @media (prefers-reduced-motion: reduce) {
    .ch-threads path {
      animation: none !important;
    }
  }

  /* Each thread travels an exact multiple of its own dash period (set via
     --ch-travel on the path), so the loop restart is invisible — a plain
     -400 lands mid-period on most patterns and made the dashes jump. */
  @keyframes ch-dash-in {
    to {
      stroke-dashoffset: var(--ch-travel, -400px);
    }
  }
</style>

<script>
  // The desktop threads' convergence point is authored at x=928 in the
  // 1130-wide viewBox, but the stage is fluid and the chip is right-anchored
  // — so as the stage widens, the baked-in terminus drifts away from the
  // chip. Rewrite each path's final curve segment so it ends 35px inside the
  // chip's left edge (the same inset as the original composition), converted
  // into viewBox units.
  (function () {
    var stage = document.querySelector('.ch-stage--desktop');
    if (!stage) return;
    var chip = stage.querySelector('.ch-chip--desktop');
    var paths = Array.prototype.slice.call(stage.querySelectorAll('.ch-threads path'))
      .map(function (p) { return { el: p, d: p.getAttribute('d') }; });
    var END_RE = /S ([\d.]+) ([\d.]+), 928 ([\d.]+)\s*$/;
    function retarget() {
      var w = stage.clientWidth;
      if (!w) return;   // desktop frame hidden (mobile layout active)
      var dx = (chip.offsetLeft + 35) * 1130 / w - 928;
      // Vertical: the composition converges on y=260; follow the chip's
      // actual center (viewBox y == px, the 480 stage height is fixed).
      var dy = chip.offsetTop - 260;
      paths.forEach(function (p) {
        p.el.setAttribute('d', p.d.replace(END_RE, function (_, cx, cy, y) {
          return 'S ' + (parseFloat(cx) + dx).toFixed(1) + ' ' + (parseFloat(cy) + dy).toFixed(1) +
            ', ' + (928 + dx).toFixed(1) + ' ' + (parseFloat(y) + dy).toFixed(1);
        }));
      });
    }
    retarget();
    window.addEventListener('load', retarget);
    window.addEventListener('resize', retarget);
    if (window.ResizeObserver) new ResizeObserver(retarget).observe(stage);
  })();
</script>