<?php
/* ─────────────────────────────────────────────────────────────
   SNIPPETS — paste these into the relevant existing files
   ───────────────────────────────────────────────────────────── */
?>

<!-- ══════════════════════════════════════════════════════════
     1.  includes/header.php  — add inside the <nav> Tools list
         (after the Type Guide <li>)
     ══════════════════════════════════════════════════════════ -->

<li>
  <a href="/shadow/" class="nav-link <?= $activePage === 'shadow' ? 'active' : '' ?>">
    <!-- Shadow icon: stacked layers -->
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <rect x="3" y="8" width="18" height="10" rx="2"/>
      <rect x="5" y="5" width="14" height="3" rx="1" opacity=".5"/>
    </svg>
    Shadow &amp; Elevation
  </a>
</li>


<!-- ══════════════════════════════════════════════════════════
     2.  index.php (homepage)  — add a 6th tool card
         (after the Type Guide card in the tool grid)
     ══════════════════════════════════════════════════════════ -->

<a href="/shadow/" class="tool-card">
  <div class="tool-card-preview tool-card-preview--shadow" aria-hidden="true">
    <!-- Floating cards that cast shadows — purely decorative -->
    <div class="shadow-card-demo">
      <div class="demo-card demo-card--xs"></div>
      <div class="demo-card demo-card--md"></div>
      <div class="demo-card demo-card--xl"></div>
    </div>
  </div>
  <div class="tool-card-body">
    <h3>Shadow &amp; Elevation</h3>
    <p>Build a semantic shadow scale tinted from your palette. Preview on light and dark surfaces and export as CSS tokens or Figma JSON.</p>
    <ul class="tool-card-tags">
      <li>Palette tint</li>
      <li>Light &amp; dark preview</li>
      <li>CSS export</li>
    </ul>
  </div>
</a>


<!-- ══════════════════════════════════════════════════════════
     3.  assets/style.css  — homepage card preview thumbnail
         (add alongside your other .tool-card-preview styles)
     ══════════════════════════════════════════════════════════ -->

<?php /* Paste the following into style.css, NOT in a PHP file */ ?>
/*
.tool-card-preview--shadow {
  background: #0f172a;
  display: flex;
  align-items: center;
  justify-content: center;
}

.shadow-card-demo {
  display: flex;
  align-items: flex-end;
  gap: 16px;
}

.demo-card {
  background: #1e293b;
  border-radius: 6px;
}

.demo-card--xs {
  width: 36px;
  height: 36px;
  box-shadow: 0 1px 3px rgba(30,60,180,.45);
}

.demo-card--md {
  width: 52px;
  height: 52px;
  box-shadow: 0 6px 18px rgba(30,60,180,.55);
}

.demo-card--xl {
  width: 36px;
  height: 70px;
  box-shadow: 0 18px 48px rgba(30,60,180,.65);
}
*/
