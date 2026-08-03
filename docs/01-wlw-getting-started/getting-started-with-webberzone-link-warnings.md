---
slug: getting-started-with-webberzone-link-warnings
title: "Getting Started with WebberZone Link Warnings"
products: [link-warnings]
sections: [01-wlw-getting-started]
tags: [installation,link-warnings]
status: publish
order: 0
---

WebberZone Link Warnings adds accessible warnings to external links and `target="_blank"` links in your WordPress content. It supports inline visual indicators, modal confirmation dialogs, and redirect interstitial screens — or combinations of these.

## Requirements

- The latest three versions of WordPress
- PHP 7.4 or later

## Installation

### From the WordPress dashboard

1. Navigate to **Plugins \> Add New**.
2. Search for “WebberZone Link Warnings”.
3. Click **Install Now**, then **Activate**.

### Manual upload

1. Download the plugin archive from [GitHub releases](https://github.com/WebberZone/webberzone-link-warnings/releases) or [WordPress.org](https://wordpress.org/plugins/webberzone-link-warnings/).
2. Upload the `webberzone-link-warnings` folder to `/wp-content/plugins/`.
3. Activate the plugin from the **Plugins** screen.

## First-run wizard

On first activation, an admin notice appears offering a guided setup wizard. It runs through five sequential steps:

1. **Welcome** — introduction and the option to skip the wizard.
2. **General Settings** — warning method and link scope.
3. **Visual Indicators** — icon, text, both, or screen-reader-only, plus screen reader text.
4. **Modal Dialog** — title, message, and button text (shown when the warning method includes a modal component).
5. **Redirect Screen** — message and countdown duration (shown when the warning method includes a redirect component).

You can dismiss the wizard at any time and configure settings manually.

## Manual configuration

All settings are available at **Settings \> Link Warnings**. The settings page is organized into three tabs:

- **General** — warning method, link scope, and enabled post types.
- **Display** — inline indicator options, modal dialog text, and redirect screen text.
- **Advanced** — excluded domains.

See the <a href="https://webberzone.com/support/knowledgebase/webberzone-link-warnings-settings-reference/" data-type="wz_knowledgebase" data-id="9735">Settings Reference</a> for a full description of every option.

## How content processing works

WebberZone Link Warnings does not modify your stored content. It filters rendered output using the `the_content` and `the_excerpt` hooks at priority 999, which means it runs after most other content filters.

For each `<a>` tag in the output, the plugin:

1.  Checks whether the current post type is enabled in settings.
2.  Determines whether the link is external by comparing its host against the site host and the excluded domains list.
3.  Applies scope rules — external links only, or external links plus internal `target="_blank"` links.
4.  Adds CSS classes (`wzlw-processed`, `wzlw-external`) for styling.
5.  Appends the configured screen reader text to any existing `aria-label` attribute.
6.  For modal and redirect methods, adds `data-wzlw-external`, `data-wzlw-url`, and `data-wzlw-redirect-url` attributes used by the frontend JavaScript.
7.  For inline methods, appends visual indicator markup (icon, text, or both) inside the link.

The plugin uses WordPress’s native `WP_HTML_Tag_Processor` class for HTML parsing, which avoids regex-based content manipulation for the structural changes.

## Warning methods at a glance

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Method</th>
<th>Behavior</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Inline</strong></td>
<td>Adds a visual indicator (icon, text, or both) and screen reader text inside the link. No click interception.</td>
</tr>
<tr>
<td><strong>Modal</strong></td>
<td>Intercepts clicks on external links and shows a confirmation dialog. The user can continue (opens in a new window) or cancel.</td>
</tr>
<tr>
<td><strong>Redirect</strong></td>
<td>Intercepts clicks and navigates to an interstitial page with a countdown timer before redirecting to the external URL.</td>
</tr>
<tr>
<td><strong>Inline + Modal</strong></td>
<td>Combines inline indicators with the modal dialog on click.</td>
</tr>
<tr>
<td><strong>Inline + Redirect</strong></td>
<td>Combines inline indicators with the redirect interstitial on click.</td>
</tr>
</tbody>
</table>
</figure>

## What happens on deactivation

Deactivating the plugin removes all transients, flushes the rewrite rules cache, and clears the object cache. Your content is unaffected — links return to their original rendered state because the plugin never modifies stored content.
