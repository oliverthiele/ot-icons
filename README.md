# OT Icons — Accessible SVG Icon ViewHelper for TYPO3

ViewHelper for rendering accessible inline SVG icons in Fluid templates for TYPO3 v14.
Icons are resolved via mapping files — switch icon sets without touching templates.

[![TYPO3](https://img.shields.io/badge/TYPO3-14.3-orange.svg)](https://typo3.org/)
[![Packagist Version](https://img.shields.io/packagist/v/oliverthiele/ot-icons.svg)](https://packagist.org/packages/oliverthiele/ot-icons)
[![PHP](https://img.shields.io/packagist/dependency-v/oliverthiele/ot-icons/php.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/oliverthiele/ot-icons.svg)](LICENSE)
[![Changelog](https://img.shields.io/badge/Changelog-CHANGELOG.md-blue.svg)](CHANGELOG.md)

---

## Features

- **Mapping-based icon resolution** — update or change icon sets (e.g.,
  Font Awesome → Material Icons) without modifying templates
- **Fully accessible SVG output** — automatic handling of `role`, `aria-hidden`,
  `aria-label`, and `<title>` / `<desc>` tags
- **Multiple output modes** — inline SVG, sprite-based, or Base64 (planned)
- **Default styles per icon set** — each mapping file defines its default
  subdirectory (e.g., `solid/`)
- **Cache-optimized** — internal request cache plus TYPO3 caching with
  version-aware keys

---

## Requirements

| Requirement  | Version          |
|--------------|------------------|
| TYPO3        | `^14.3`          |
| PHP          | `>=8.4`          |

---

## Installation

```bash
composer require oliverthiele/ot-icons
```

The global Fluid namespace `i:` is automatically registered when the extension is
loaded. No manual namespace registration required.

---

## Configuration

Register the icon directory in your site configuration
(`config/sites/{site}/config.yaml`):

```yaml
settings:
    otIcons:
        defaultIconSet: 'FontAwesome_7'
        defaultIconStyle: 'solid'
        iconDirectory: 'EXT:ot_febuild/Resources/Public/Assets/Website/SVG/'
        customMappingDirectory: 'EXT:my_sitepackage/Configuration/Mappings/'
```

| Setting                 | Description                                           |
|-------------------------|-------------------------------------------------------|
| `defaultIconSet`        | Mapping file to use (e.g. `FontAwesome_7`)            |
| `defaultIconStyle`      | Fallback style for icons without explicit style       |
| `iconDirectory`         | Path (using `EXT:`) to your compiled SVG files        |
| `customMappingDirectory`| Optional directory for custom mapping overrides       |

### Build system integration (e.g. Webpack)

Copy SVG files from your icon library into the public asset directory:

```js
new CopyWebpackPlugin({
  patterns: [
    {
      from: './node_modules/@fortawesome/fontawesome-pro/svgs',
      to: 'Website/SVG'
    },
  ],
})
```

---

## Usage in Fluid

```html
<i:icon identifier="typo3" size="2x" aria-label="TYPO3 logo"/>
```

```html
<i:icon>download</i:icon>
```

### Available attributes

| Attribute           | Type   | Description                                                        |
|---------------------|--------|--------------------------------------------------------------------|
| `identifier`        | string | Internal icon key (mapped to file name)                            |
| `size`              | string | Icon size (`xs`, `sm`, `lg`, `1x`–`10x`)                          |
| `iconStyle`         | string | Style alias (`s`, `r`, `b`, `l`, etc.)                             |
| `returnAs`          | string | Output type (`inline`, `sprite`, `base64`) — currently only inline |
| `id`                | string | Optional HTML `id` attribute                                       |
| `additionalClasses` | string | Extra CSS classes                                                  |
| `aria-hidden`       | bool   | Hides the icon from screen readers                                 |
| `aria-label`        | string | Text alternative (rendered inside `<title>`)                       |
| `aria-description`  | string | Additional descriptive text                                        |
| `role`              | string | Accessibility role (`img`, `presentation`, `none`, etc.)           |
| `title`             | string | Tooltip text                                                       |

---

## Custom mapping overrides

Create a file at `EXT:my_sitepackage/Configuration/Mappings/{IconSetName}.php`
with only the identifiers that differ:

```php
<?php
return [
    'map' => [
        'users' => 'users-class',
    ],
];
```

---

## Accessibility

- `aria-hidden="true"` → decorative icon, hidden from assistive technologies
- `aria-label` provided → rendered inside `<title>`, `role="img"` set automatically
- No label → icon is fully hidden from screen readers

---

## Mapping files

Each icon set has a mapping file under `Configuration/Mappings/{IconSetName}.php`.
Example `FontAwesome_7.php`:

```php
return [
    'config' => [
        'prefix' => 'fa-',
        'version' => '7.0.0',
        'defaultSubdirectory' => 'solid/',
    ],
    'map' => [
        'chevron-up-square'    => 'square-chevron-up',
        'chevron-left-square'  => 'square-chevron-left',
        'chevron-right-square' => 'square-chevron-right',
        'chevron-down-square'  => 'square-chevron-down',
    ],
];
```

---

## License

GPL-2.0-or-later — see [LICENSE](LICENSE)

## Author

Oliver Thiele — [oliver-thiele.de](https://www.oliver-thiele.de)