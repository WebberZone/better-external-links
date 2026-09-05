# AGENTS.md

This file provides guidance to AI coding agents working with code in this repository.

## Response Rules

- Return only the changed function or section, not the full file
- No explanation unless asked
- No out-of-scope suggestions
- Skip preamble and trailing summaries

## Links

- GitHub: <https://github.com/WebberZone/webberzone-link-warnings>
- WordPress.org: <https://wordpress.org/plugins/webberzone-link-warnings/>
- Documentation: <https://webberzone.com/support/product/link-warnings/>
- webberzone.com: <https://webberzone.com/plugins/webberzone-link-warnings/>

## Plugin Overview

**WebberZone Link Warnings** (v1.5.2) adds configurable warnings to external/affiliate links or any URL pattern, showing a modal countdown or inline icon before the visitor leaves. Namespace: `WebberZone\Link_Warnings`. Constants: `WZLW_VERSION`, `WZLW_PLUGIN_FILE`, `WZLW_PLUGIN_DIR`, `WZLW_PLUGIN_URL`, `WZLW_PLUGIN_BASENAME`. Requires WordPress 6.6+, PHP 7.4+. No Freemius.

Settings prefix/key: `wzlw` / `wzlw_settings` (wp_options). Access via `wzlw_get_option($key)` / `wzlw_get_settings()`.

## Build Commands

```bash
# Update JS dependencies to latest and reinstall
ncu -u && pnpm install

# Minify JS/CSS and generate RTL versions
pnpm run build:assets

# Install production PHP dependencies
composer build:vendor

# Run all quality checks (phpcs, phpcompat, phpstan)
composer test

# Individual checks
composer phpcs       # PHP CodeSniffer
composer phpcbf      # Auto-fix PHP code style
composer phpstan     # Static analysis
composer phpcompat   # PHP compatibility check

# Create distribution zip
pnpm run zip
```

Build requires Node.js with `clean-css-cli`, `terser`, `rtlcss`, and `@wordpress/scripts` (installed via `pnpm install`).

## Architecture

**Namespace:** `WebberZone\Link_Warnings`
**Autoloading:** PSR-4 via `includes/autoloader.php` — class `Foo_Bar` maps to `includes/class-foo-bar.php`

**Entry point:** `webberzone-link-warnings.php` defines constants (`WZLW_VERSION`, `WZLW_PLUGIN_DIR`, etc.) and boots the plugin via `wzlw()` singleton.

**Core classes:**

| Class | File | Purpose |
|-------|------|---------|
| `Main` | `includes/class-main.php` | Bootstrap, singleton, registers all hooks |
| `Content_Processor` | `includes/class-content-processor.php` | Hooks into `the_content`/`the_excerpt` (priority 999); parses and modifies links using `WP_HTML_Tag_Processor` |
| `Frontend_Handler` | `includes/class-frontend-handler.php` | Enqueues frontend assets, renders modal HTML |
| `Redirect_Handler` | `includes/class-redirect-handler.php` | Handles redirect endpoint and countdown |
| `Options_API` | `includes/class-options-api.php` | Settings storage wrapper |
| `Util\Hook_Registry` | `includes/util/class-hook-registry.php` | Centralized hook registration with deduplication |
| `Util\Icon_Helper` | `includes/util/class-icon-helper.php` | Icon selection utilities |
| `Admin\Admin` | `includes/admin/class-admin.php` | Admin bootstrap (only loaded on admin pages) |
| `Admin\Settings` | `includes/admin/class-settings.php` | Settings pages and forms |

**Settings helpers** (procedural, in `includes/options-api.php`): `wzlw_get_settings()`, `wzlw_get_option()`, `wzlw_update_option()`

## Asset Pipeline

`build-assets.js` (run via `pnpm run build:assets`) recursively finds all `.css`/`.js` files, excluding directories `node_modules`, `vendor`, `freemius`, `build`, `.git` and file patterns `*-rtl.css`, `build-assets.js` (`.min` files are intentionally NOT excluded, so they get re-minified), then:

1. Minifies CSS → `*.min.css`
2. Minifies JS → `*.min.js`
3. Generates RTL CSS → `*-rtl.css` and `*-rtl.min.css`
4. Formats RTL files with wp-scripts prettier

After adding/significantly editing a `.css` or `.js` file, run `pnpm run build:assets` to regenerate minified and RTL versions.

## Key Conventions

- Link exclusion: links with the no-icon class (default `wzlw-no-icon`, setting `no_icon_class`) suppress visual indicators; links inside the wrapper class (default `wzlw-no-icon-wrapper`, setting `no_icon_wrapper_class`) too. All four class settings accept comma-separated values (e.g. `wzlw-no-icon, my-no-icon`) — PHP parses via `Content_Processor::parse_class_setting()`, JS gets them as arrays via `wzlwSettings`.
- Force external: an `<a>` tag with the force-external class (default `wzlw-force-external`, setting `force_external_class`) is treated as external; the wrapper class (default `wzlw-force-external-wrapper`, setting `force_external_wrapper_class`) on a container forces all descendant links external.
- Both exclusion and force-external track nesting across the `WP_HTML_Tag_Processor` token stream via depth counters (`$skip_depth`, `$force_external_depth`) in `Content_Processor::process_content()`.
- Excluded domains: plain entries (e.g. `example.com`) match only that exact domain; wildcard entries (e.g. `*.example.com`) match only subdomains, not the base — add both to exclude both. Same logic in PHP (`Content_Processor::is_external_link`) and JS (`isExternalHref` in `modal.js`); passed to JS via `wzlwSettings.excludedDomains`.
- Content processing uses WordPress's native `WP_HTML_Tag_Processor` (WordPress 6.6+, PHP 7.4+).
- All admin components load conditionally (admin pages only) within `Admin\Admin`.
- The redirect endpoint uses WordPress rewrite rules managed by `Redirect_Handler`.

## Settings API Field Widths

- Full-width text input: `'size' => 'large'` → renders as `input.large-text` (WordPress admin CSS: `width: 99%`)
- Do NOT use `'field_class' => 'widefat'` for text inputs — the appended `-text` suffix class overrides it

## Shared framework files: `@since` convention

The Settings API (`includes/admin/settings/*.php`) and Admin Banner (`includes/admin/class-admin-banner.php`) are copy-pasted shared framework files canonically sourced from the `Settings_API` repo. To keep `@since` tags meaningful and stable across syncs, they follow special rules:

- Each file carries **exactly one** `@since` tag, on its **class docblock**, set to the version that class was **first introduced into this plugin** — per-file (wizard, metabox and banner classes were generally added later than core Settings API classes).
- **Do not** add `@since` to methods, functions or properties in these files.
- When syncing from another plugin or the canonical `Settings_API` repo, **do not overwrite the class-level `@since`** — plugin-specific; re-apply the values below after any sync.

| File | `@since` |
|---|---|
| `includes/admin/settings/class-settings-api.php` | 1.0.0 |
| `includes/admin/settings/class-settings-form.php` | 1.0.0 |
| `includes/admin/settings/class-settings-sanitize.php` | 1.0.0 |
| `includes/admin/settings/class-settings-wizard-api.php` | 1.0.0 |
| `includes/admin/settings/class-metabox-api.php` | 1.0.0 |
| `includes/admin/class-admin-banner.php` | 1.0.0 |
