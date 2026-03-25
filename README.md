# mod_vertical_megamenu — Joomla 5 Vertical Mega Menu

Vertical catalog-style mega menu module for Joomla 5.  
Perfect for online shops, catalogues, and sidebar navigation.

---

## Features

- **Vertical sidebar** navigation with right-side flyout panel
- **Up to 3 levels** of nested items
- **Multi-column mega panel** (2–4 columns, auto or manual distribution)
- **Responsive**: hover on desktop → click on tablet → accordion on mobile
- **Vanilla JS** (<5kb), no jQuery
- **CSS** (<10kb), full CSS variable theming
- **Bootstrap 5** compatible
- **Accessibility**: `aria-expanded`, `aria-controls`, `role="navigation"`, keyboard nav
- **SEO-safe** `<ul>/<li>/<a>` structure

---

## Installation

1. Create a ZIP from the `mod_vertical_megamenu` folder
2. Install via **Extensions → Install** in Joomla 5 back-end
3. Publish to any module position (recommended: sidebar-left)

---

## Module Parameters

| Parameter | Description | Default |
|-----------|-------------|---------|
| Menu Type | Which Joomla menu to use | mainmenu |
| Start Level | Tree start depth | 1 |
| End Level | Tree end depth | 3 |
| Max Columns | Columns in mega panel | 3 |
| Menu Width | Left bar width (px) | 260 |
| Mega Panel Width | Flyout width (px) | 750 |
| Mobile Breakpoint | Accordion breakpoint (px) | 768 |
| Show Icons | Render `vmm_icon` params | Yes |
| Show Badges | Render `vmm_badge` params | Yes |
| Open Trigger | hover or click (desktop) | hover |

---

## Per-Item Parameters (Note Field)

Add extra parameters to any menu item via its **Note** field using pipe-separated `key=value` pairs:

```
column=2|badge=NEW|icon=<svg>...</svg>|image=/images/door.png
```

| Key | Values | Description |
|-----|--------|-------------|
| column | 1–4 | Force item into a specific mega panel column |
| badge | any text | Small label (e.g. "NEW", "SALE") |
| icon | SVG string | Inline SVG icon |
| image | URL | Small image thumbnail |

---

## Column Distribution

**Manual (recommended):**  
Set `column=N` in the Note field of each child item.

**Automatic (fallback):**  
If no `column` param is set, items are distributed evenly:
- 10 items, 3 columns → 4 / 3 / 3

---

## CSS Theming

All colors, sizes and speeds are CSS custom properties.  
Override them in your template CSS:

```css
.vertical-megamenu {
    --vmm-bg: #1a1a2e;
    --vmm-accent: #e94560;
    --vmm-menu-width: 280px;
    --vmm-mega-width: 800px;
}
```

---

## Template Override (fallback)

Copy `tmpl/vertical_megamenu_override.php` to:
```
/templates/YOUR_TEMPLATE/html/mod_menu/vertical_megamenu.php
```
Then set the **Layout** of any standard `mod_menu` to `vertical_megamenu`.

---

## Browser Support

Chrome, Firefox, Safari, Edge (last 2 versions).

---

## Joomla 5 Web Asset Manager

The module registers its CSS and JS via the **Web Asset Manager** (`getWebAssetManager()`).  
No inline `<script>` or `<link>` tags are injected manually.

---

## License

GNU General Public License v2 or later.
