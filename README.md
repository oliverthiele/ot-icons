# ot-icons — Accessible SVG Icon Rendering for TYPO3

## 🧩 Overview

**ot-icons** is a TYPO3 extension that provides a modern, accessible and
update-safe way to render SVG icons in Fluid templates.
Instead of hardcoding paths or class names, icons are mapped via configuration
files to ensure maximum flexibility and maintainability.

### ✨ Key Features

- **Mapping-based icon resolution** — update or change icon sets (e.g.,
  Font Awesome → Material Icons) without modifying templates.
- **Fully accessible SVG output** — automatic handling of `role`, `aria-hidden`,
  `aria-label`, and `<title>` / `<desc>` tags.
- **Multiple output modes** — inline SVG, sprite-based, or Base64 (future).
- **Default styles per icon set** — each mapping file defines its default
  subdirectory (e.g., `solid/`).
- **Cache-optimized** — internal request cache plus TYPO3 caching with
  version-aware keys.

---

## 🚀 Motivation

In many TYPO3 projects, icons are still integrated via **hard-coded FontAwesome or Material classes** like
`<i class="fa fa-user"></i>`.
This approach works — until an icon set changes its naming, folder structure, or style conventions.
Upgrading FontAwesome from 5 → 6 → 7 often means **manually fixing dozens of templates**.

**ot-icons** eliminates that problem.

By using *mapping files* and a clean Fluid API, you can **switch or upgrade entire icon sets**
without touching a single Fluid template.
Old identifiers are automatically translated to their new equivalents,
keeping your templates clean and update-safe.

At the same time, **ot-icons** ensures that all SVGs are rendered
with proper **accessibility semantics** — something most icon packs ignore:

- Decorative icons are automatically hidden from assistive technologies (`aria-hidden="true"`).
- Semantic icons receive the correct `role="img"` and accessible names via `<title>` or `aria-label`.
- Optional `aria-description` tags allow extended screen-reader support.

Unlike many existing icon packs for TYPO3, **ot-icons** focuses on:
- **Frontend and Fluid integration**, not backend icons.
- **Inline SVG rendering** instead of external webfonts.
- **Extensibility** — custom mappings, per-site configuration, and planned sprite/localStorage support.

The result:
**Accessible, maintainable, and future-proof icon rendering for modern TYPO3 sites.**


---

## ⚙️ Installation

Require the package via Composer:

```bash
composer require oliverthiele/ot-icons
```

The global Fluid namespace is automatically registered when the extension is
loaded.
You can use <i:icon> in your templates immediately — no manual registration
needed.

---

## 🛠 Integration into your build system (e.g. Webpack)

To make your icon sets available to TYPO3, copy the SVG files from your preferred
library (e.g. Font Awesome, Material Icons) into your public asset directory.

A typical Webpack setup using `copy-webpack-plugin` might look like this:

```js
new CopyWebpackPlugin({
  patterns: [
    {
      from: './node_modules/@fortawesome/fontawesome-pro/svgs',
      to: 'Website/SVG'
    },
    {
      from: '../Default/Resources/Assets/'
    }
  ],
})
```

This step ensures that all SVG icons are copied into your project's public
build directory — for example, under:
```
EXT:ot_febuild/Resources/Public/Assets/Website/SVG/
```

---

## 🧠 Configuration

Once your assets are available, register the icon directory in your TYPO3 site
configuration (config/sites/{site}/config.yaml):

```yaml
settings:
    otIcons:
        defaultIconSet: 'FontAwesome_7'
        defaultIconStyle: 'solid'
        iconDirectory: 'EXT:ot_febuild/Resources/Public/Assets/Website/SVG/'
        customMappingDirectory: 'EXT:my_sitepackage/Configuration/Mappings/'
```

- **defaultIconSet** — defines which mapping file to use (e.g. FontAwesome_7).
- **defaultIconStyle** — fallback style for icons without explicit style.
- **iconDirectory** — absolute path (using EXT:) to your compiled SVG files.
- **customMappingDirectory** — optional directory for your own mapping overrides.

> 🧹 **Note:**
> When adding or modifying mapping files in a custom directory,
> make sure to **clear the TYPO3 caches** to reload the updated mappings.

Each icon set defines its mapping file under:

```
Configuration/Mappings/{IconSetName}.php
```

Example `FontAwesome_7.php`:

Font Awesome version 6 and later renamed several icons (for example,
`square-chevron-up` instead of `chevron-up-square`).

Only identifiers that differ between versions need to be mapped here.
The mapping file below demonstrates how older identifiers are translated
to their new counterparts.

Missing mappings can be contributed via pull request.

```php
return [
    'config' => [
        'prefix' => 'fa-',
        'version' => '7.0.0',
        'defaultSubdirectory' => 'solid/',
    ],
    'map' => [
        'chevron-up-square'  => 'square-chevron-up',
        'chevron-left-square'  => 'square-chevron-left',
        'chevron-right-square'  => 'square-chevron-right',
        'chevron-down-square'  => 'square-chevron-down',
    ],
];
```

---

## 🔄 Custom mapping overrides

Integrators can also provide their own mapping overrides without modifying
the `ot-icons` extension.

Simply create a file at:

```
EXT:my_sitepackage/Configuration/Mappings/{IconSetName}.php
```

It has the same structure as the default mapping file — only the differing
identifiers need to be listed. When present, this file is automatically
merged into the default mapping.

Example override:

```php
<?php
return [
    'map' => [
        'users' => 'users-class',
    ],
];
```

---

## 🎨 Optional: SCSS sizing utilities

The extension provides optional SCSS utilities to define icon sizes similar to
Font Awesome’s `fa-2x`, `fa-3x`, etc.

These classes allow consistent scaling of inline SVGs.

Located at: `Resources/Private/Scss/OtIcons.scss`

Include this file in your main SCSS entry point (e.g. via Webpack or your sitepackage).

> 💡 **Tip:**
> The `font-size` scaling affects both the height and width of the SVG through `em` units.
> The base `.ot-inline-icon` size is `1em`, so relative scaling works automatically.

---

## 🧩 Usage in Fluid

Render an icon using the ViewHelper:

```html
<i:icon identifier="typo3" size="2x" aria-label="TYPO3 logo"/>
```

or with content fallback:

```html
<i:icon>download</i:icon>
```

### Available attributes

| Attribute           | Type   | Description                                                          |
|---------------------|--------|----------------------------------------------------------------------|
| `identifier`        | string | Internal icon key (mapped to file name)                              |
| `size`              | string | Icon size (xs, sm, lg, 1x–10x)                                       |
| `iconStyle`         | string | Style alias (`s`, `r`, `b`, `l`, etc.)                               |
| `returnAs`          | string | Output type (`inline`, `sprite`, `base64`) - currently only inline   |
| `id`                | string | Optional HTML `id` attribute                                         |
| `additionalClasses` | string | Extra CSS classes                                                    |
| `aria-hidden`       | bool   | Hides the icon from screen readers                                   |
| `aria-label`        | string | Text alternative (used inside `<title>`)                             |
| `aria-description`  | string | Additional descriptive text                                          |
| `role`              | string | Accessibility role (`img`, `presentation`, `none`, `button`, `link`) |
| `title`             | string | Tooltip text (for decorative icons)                                  |

> 🧩 **Note on `returnAs`** (planned sprite & localStorage support)
>
> The `returnAs` attribute defines how icons are rendered.
> Currently, only the `inline` mode is active.
>
> In a future release, ot-icons will support sprite-based rendering with localStorage caching,
> inspired by the concept used in my earlier TYPO3 icon systems:
>
> **`localstorage`** — stores the generated sprite sheet in the browser’s localStorage and injects it once per session.
> This drastically reduces DOM size and improves frontend performance for icon-heavy pages.
>
> Additionally, a **`base64`** output mode is planned for embedding icons in CSP-restricted or email environments.

---

## ♿ Accessibility Guidelines

### Default behavior

- If `aria-hidden="true"` → the icon is **decorative** and not announced.
- If `aria-hidden` is `false` or omitted → the icon is **semantic**, using
  `role="img"`.
- If `aria-label` is provided → used as accessible name (rendered inside
  `<title>`).
- If both `aria-label` and `title` are empty → screen readers announce nothing.

### Supported `role` values

| Role                    | Purpose                          | Recommendation      |
|-------------------------|----------------------------------|---------------------|
| `img`                   | semantic image icon              | ✅ default          |
| `presentation` / `none` | purely decorative but visible    | optional            |
| `button` / `link`       | interactive SVGs                 | only if clickable   |
| `graphics-symbol`       | experimental ARIA role for icons | optional / fallback |

If `aria-hidden="true"`, the `role` attribute is automatically removed.

---

## 🧰 Developer Notes

- Cache keys include icon set and mapping version for safe cache invalidation.
- The extension supports custom icon sets — simply add a new mapping file.

### Planned Features

The `returnAs` attribute already defines a flexible output interface.
Currently, only `inline` rendering is supported, but additional modes are planned:

- `sprite` and `localstorage` for shared, cacheable SVGs.
  In this approach, sprites are generated and cached server-side by TYPO3,
  then loaded into the browser once per session via JavaScript and stored in `localStorage`.
  Subsequent page loads reference the cached sprite using `<use>` elements,
  minimizing DOM size and improving rendering performance for icon-heavy pages.

- `base64` for embedding SVGs in restricted environments (e.g. emails).

This ensures that future rendering optimizations can be introduced
without breaking existing templates.

---

## 🧪 Example Output

In this example, the `id` attribute is automatically generated so that the added
`<title>` and `<desc>` tags work with aria-label and aria-description.

```html
<svg id="icon-9064e7af" role="img"
      aria-labelledby="icon-9064e7af-title"
      aria-describedby="icon-9064e7af-desc"
      class="ot-inline-icon ot-icon-id-user ot-4x" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
    <title id="icon-9064e7af-title">Example Title</title>
    <desc id="icon-9064e7af-desc">Example Description</desc>
    …
</svg>
```

---

## 🧾 License

GPL-2.0-or-later
(c) 2025 [Oliver Thiele](https://oliver-thiele.de)

---

## 🧭 Changelog

**v1.0.0**

- Initial release with full Fluid ViewHelper, IconService, and accessible SVG
  rendering.
- Supports default icon sets, mapping-based resolution, per-site configuration,
  and custom mapping overrides.
