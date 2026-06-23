# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased] — 3.0.0

### Changed

- Drop TYPO3 v13 support, require TYPO3 `^14.3`
- Raise PHP minimum to `>=8.4`
- Migrate Fluid namespace registration from `ext_localconf.php` to `Configuration/Fluid/Namespaces.php`

---

## [2.1.0] — 2026-06-23

### Added

- SiteSet setting `otIcons.availableIconStyles` for per-element icon style selection
- `styleDirectories` key in mapping files for configurable style-to-directory mapping
- `IconStyleItemsProcFunc` for TCA select item generation from SiteSet settings
- `IconStyleDisplayCondition` UserFunc for conditional field visibility

### Changed

- Icon model resolves directories via `styleDirectories` mapping before short-alias fallback
- IconService passes `styleDirectories` from mapping data to Icon model
- Custom mapping files can now override `styleDirectories` in addition to `config` and `map`

---

## [2.0.0] — 2026-04-26

### Added

- TYPO3 v14.3 support (`^13.4||^14.3`)

### Changed

- Raise PHP minimum constraint to `>=8.3`
- Drop TYPO3 v12 support and PHP 8.2 support

---

## [1.0.1] — 2026-02-01

### Added

- PHP version requirement (`^8.2`)

---

## [1.0.0] — 2025-08-01

### Added

- Initial release
- `<i:icon>` Fluid ViewHelper with inline SVG rendering
- Mapping-based icon resolution (`FontAwesome_7`, `BootstrapIcons`)
- Accessible SVG output (`aria-hidden`, `aria-label`, `role`)
- Per-site configuration via SiteSet settings

[Unreleased]: https://github.com/oliverthiele/ot-icons/compare/v2.1.0...HEAD
[2.1.0]: https://github.com/oliverthiele/ot-icons/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/oliverthiele/ot-icons/compare/v1.0.1...v2.0.0
[1.0.1]: https://github.com/oliverthiele/ot-icons/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/oliverthiele/ot-icons/releases/tag/v1.0.0