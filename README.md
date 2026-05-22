# ONE design

**Tools for designers who care about the details.**  
A growing collection of free, browser-based design tools — built by a designer, for designers.

> Version `1.1.3` · Built with PHP + vanilla JS · No frameworks, no subscriptions

---

## Tools

### Palette Generator `/palette/`
Generates full 50–900 shade scales from any seed color using perceptually uniform **OKLCH color math** — avoiding the grey, washed-out midpoints that plague HSL-based tools.

- Up to 8 color scales simultaneously
- 4–14 stops per scale (adjustable)
- OKLCH mode (perceptually uniform) or Tint/Shade mode
- **Color harmony suggestions** — complementary, analogous, triadic, split-complementary, and tetradic options, each showing a live scale preview
- **Color library** — curated presets (Gray, Slate, Red, Blue, Green, etc.)
- Export as CSS custom properties or Figma-ready JSON
- **Apply to site** — apply your primary scale as a live theme across the entire app
- Drafts auto-saved to localStorage; named palettes saved to Saved work

### Color Picker `/color-picker/`
A full OKLCH color picker organized around a clear workflow: **select → contrast → harmony → save**.

- **Hue wheel** + **gamut canvas** for visual picking
- **L / C / H / A sliders** with live gradient tracks
- Hex input with validation
- **Contrast section** — WCAG AA (≥ 4.5:1) and AAA (≥ 7:1) checks against white and black, with a nested color preview swatch
- **Harmony section** — five harmony types (complementary, analogous, triadic, split, tetradic), each showing a 5-stop scale preview; click to load a harmony color into the picker or save it directly
- **Palette section** — saved color list with an "Open in palette generator" CTA that hands off all saved colors to the palette tool
- Export panel with hex, oklch, rgb, hsl, CSS variable, and Figma formats

### Gradient Studio `/gradient/`
Build gradients that actually look good. Interpolates through OKLCH to avoid the grey muddy band that ruins most CSS gradients.

- Linear and radial modes
- OKLCH interpolation
- Copy-ready CSS output

### Case Converter `/case-converter/`
Transform text between 13 different case formats instantly.

- camelCase, PascalCase, snake_case, kebab-case, SCREAMING_SNAKE, Title Case, Sentence case, lowercase, UPPERCASE, slug, dot.case, path/case, and more
- Live preview as you type
- Copy-clean utilities (trim whitespace, remove punctuation, etc.)

### Type Guide `/type-guide/`
Set typography standards for a project with a modular scale.

- Choose heading and body fonts (Google Fonts integration)
- Set base size and scale ratio independently for desktop and mobile
- Adjust line height and letter spacing per level
- Export as CSS custom properties or utility classes
- Save named type guides to Saved work

---

## Workspace

### Saved `/saved-palettes/`
A persistent library of saved palettes and type guides, stored in localStorage.

- Rename saved items inline
- Open any saved palette or type guide directly in its tool
- Copy CSS for individual palettes
- **Export all styles** — one-click modal that combines every saved palette and type guide into a single CSS block, ready to paste

### Export History `/export-history/`
A log of every export action taken across all tools.

---

## Architecture

### Tech stack
| Layer | Choice |
|---|---|
| Server | PHP (built-in dev server, no framework) |
| Templating | PHP includes (`includes/header.php`, `includes/footer.php`) |
| Styling | Single stylesheet `assets/style.css` — CSS custom properties throughout |
| Color math | `assets/color-math.js` — shared across all pages |
| State | `localStorage` (no backend database) |
| Fonts | DM Mono + Fraunces via Google Fonts |

### Key files
```
oneredesigns.com/
├── index.php                  # Homepage / tool grid
├── palette/index.php          # Palette generator
├── color-picker/index.php     # Color picker
├── gradient/index.php         # Gradient studio
├── case-converter/index.php   # Case converter
├── type-guide/index.php       # Type guide
├── saved-palettes/index.php   # Saved work
├── export-history/index.php   # Export history
├── admin/index.php            # Admin panel (file sharing)
├── assets/
│   ├── style.css              # Global stylesheet
│   ├── color-math.js          # OKLCH math (shared)
│   └── favicon/               # Favicon set
└── includes/
    ├── header.php             # Shared <head>, sidebar nav, mobile header
    ├── footer.php             # Shared scripts, toast, export helpers
    └── version.php            # APP_VERSION constant (cache-busting)
```

### Color math (`assets/color-math.js`)
All color work runs through a shared set of functions:

| Function | Purpose |
|---|---|
| `hexToRgb(hex)` | `#rrggbb` → `[r, g, b]` |
| `rgbToOklch(r, g, b)` | `[r,g,b]` → `[L, C, H]` |
| `oklchToHex(L, C, H)` | OKLCH → `#rrggbb` |
| `genScaleWithStops(hex, stops)` | Generate a shade scale at arbitrary stops |
| `genScale(hex)` | Standard 10-stop scale (50–900) |
| `isInGamut(L, C, H)` | sRGB gamut check |
| `clampToGamut(L, C, H)` | Clamp to sRGB boundary |
| `textColorFor(hex)` | Returns `#000` or `#fff` for legible overlay text |

### localStorage keys
| Key | Used by | Content |
|---|---|---|
| `oklch-palette-draft` | Palette generator | Current unsaved palette state |
| `oklch-palettes` | Palette generator, Saved | Array of saved palettes |
| `oklch-type-saves` | Type guide, Saved | Array of saved type guides |
| `oklch-picker-draft` | Color picker | Last picker state (L, C, H, A, harmony mode) |
| `picker-saved-colors` | Color picker | Array of saved hex colors |
| `picker-palette-handoff` | Color picker → Palette | Hex array for cross-tool handoff |
| `site-theme` | All pages | Active CSS variable overrides from "Apply to site" |
| `greeting-name` | Homepage | Personalised greeting name |
| `palette-apply-tip-dismissed` | Palette generator | One-time tooltip dismiss flag |

### Theming
The sidebar and UI are themed using CSS custom properties defined in `:root`. The palette generator's **Apply to site** feature overrides `--color-primary-50` through `--color-primary-900` on `document.documentElement` as inline styles, which cascade above the stylesheet. These overrides are written to `localStorage` as `site-theme` and re-applied by an inline `<script>` in `<head>` on every page load — before the stylesheet parses — to prevent a flash of unstyled content.

---

## Development

```bash
# Start the local dev server (PHP built-in, port 8765)
php -S localhost:8765 -t /Users/ryanpugh/Websites/oneredesigns.com

# Or via the project launch config
# .claude/launch.json is configured for this command
```

Open `http://localhost:8765` in your browser.

No build step. No npm. Edit a file, refresh the page.

---

## Design principles

- **OKLCH first** — perceptually uniform color math throughout; no HSL shortcuts
- **No backend** — everything runs in the browser via localStorage; zero accounts, zero servers
- **Built, not bought** — every tool was designed and coded from scratch to solve a real need
- **Designer UX** — workflows follow how designers actually think: pick → evaluate → explore → export
