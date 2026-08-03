---
slug: styling-webberzone-link-warnings
title: "Styling WebberZone Link Warnings"
products: [link-warnings]
sections: [02-wlw-advanced]
tags: [link-warnings,styles]
status: publish
order: 0
---

[kbtoc]

<a href="https://webberzone.com/plugins/webberzone-link-warnings/" data-type="page" data-id="9512">WebberZone Link Warnings</a> uses CSS custom properties (variables) for all colors, spacing, and visual tokens. You can override these in your theme stylesheet without modifying plugin files.

This guide covers the CSS class reference, custom property reference, and common customization recipes — including replacing the default external link icon.

## CSS classes added to links

The plugin adds the following classes to processed `<a>` tags:

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Class</th>
<th>Applied when</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>wzlw-processed</code></td>
<td>Always added to every link the plugin processes.</td>
</tr>
<tr>
<td><code>wzlw-external</code></td>
<td>Added when the link is classified as external.</td>
</tr>
</tbody>
</table>
</figure>

These classes are useful for writing targeted CSS rules.

## Inline indicator markup

When an inline warning method is active (`inline`, `inline_modal`, or `inline_redirect`), the plugin appends one or more `<span>` elements inside the link, just before the closing `</a>` tag.

### Icon indicator

The icon is rendered via a CSS `::before` pseudo-element using a CSS variable. The HTML is an empty span:

```html
<span class="wzlw-icon" aria-hidden="true"></span>
```

The displayed character is controlled by the `--wzlw-icon-content` CSS variable, which the plugin sets as an inline style based on the Icon Style setting.

### Text indicator

```html
<span class="wzlw-text" aria-hidden="true">(opens in new window)</span>
```

### Screen reader text

Always added to processed links, regardless of the visual indicator setting:

```html
<span class="screen-reader-text">Opens in a new window</span>
```

### Suppressing the icon on specific links

Add the `wzlw-no-icon` class to any link to suppress its icon:

```html
<a href="https://example.com" class="wzlw-no-icon">Example</a>
```

## CSS custom properties

The plugin defines all visual tokens as CSS custom properties on `:root`. Override them in your theme to change colors and spacing globally.

### Frontend (indicators and modal)

Defined in `includes/assets/css/frontend.css`:

```css
:root {
    --wzlw-color-text: #1a1a1a;
    --wzlw-color-text-muted: #4a4a4a;
    --wzlw-color-indicator: #595959;
    --wzlw-color-surface: #fff;
    --wzlw-color-on-primary: #fff;
    --wzlw-color-surface-muted: #f5f5f5;
    --wzlw-color-border: #c3c4c7;
    --wzlw-color-border-strong: #8c8f94;
    --wzlw-color-button-muted: #f0f0f0;
    --wzlw-color-button-muted-hover: #d5d5d5;
    --wzlw-color-link: #2271b1;
    --wzlw-color-link-hover: #135e96;
    --wzlw-color-focus: #2271b1;
    --wzlw-modal-overlay-bg: rgba(0, 0, 0, 0.7);
    --wzlw-shadow-modal: 0 4px 20px rgba(0, 0, 0, 0.3);
    --wzlw-radius-sm: 4px;
    --wzlw-radius-md: 8px;
    --wzlw-transition: all 0.2s ease;
}
```

### Redirect screen

Defined in `includes/assets/css/redirect.css`:

```css
:root {
    --wzlw-color-text: #3a3a3a;
    --wzlw-color-text-muted: #666;
    --wzlw-color-text-subtle: #757575;
    --wzlw-color-surface: #fff;
    --wzlw-color-page-bg: #fafbfc;
    --wzlw-color-surface-muted: #f9fafb;
    --wzlw-color-border: #e5e7eb;
    --wzlw-color-border-accent: #0274be;
    --wzlw-color-link: #0274be;
    --wzlw-color-link-hover: #024a7e;
    --wzlw-color-on-primary: #fff;
    --wzlw-color-back-hover: #f3f4f6;
    --wzlw-radius-lg: 8px;
    --wzlw-radius-md: 6px;
    --wzlw-shadow-card: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    --wzlw-shadow-primary-hover: 0 4px 12px rgba(2, 116, 190, 0.2);
    --wzlw-transition: all 0.2s ease;
}
```

## Overriding custom properties

Add overrides in your theme’s stylesheet (or via the Customizer’s Additional CSS). Your rules load after the plugin stylesheet, so they take precedence without `!important`.

```css
:root {
    --wzlw-color-link: #0073aa;
    --wzlw-color-link-hover: #005177;
    --wzlw-color-indicator: #333;
    --wzlw-radius-md: 12px;
}
```

This changes the modal button color, indicator text color, and border radius across all plugin elements.

## Replacing the default icon

The default icon is the ↗ character, rendered via the `--wzlw-icon-content` CSS variable on the `::before` pseudo-element of `.wzlw-icon`. The Icon Style and Icon Color settings in the admin control the defaults. You can go further with CSS.

### Use a background image

```css
.wzlw-icon::before {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    background: url('/path/to/your-icon.svg') no-repeat center / contain;
    vertical-align: middle;
}
```

### Use a different Unicode character

```css
.wzlw-icon::before {
    content: '\1F517'; /* Link emoji, or any Unicode code point. */
    font-size: 0.875em;
}
```

### Use a web font icon (e.g. Dashicons or Font Awesome)

If your theme already loads an icon font, you can reference a glyph:

```css
.wzlw-icon::before {
    font-family: 'dashicons';
    content: '\f504'; /* Dashicons external link glyph. */
    font-size: 14px;
    vertical-align: middle;
}
```

Replace the `font-family` and `content` values for your icon font.

## Styling the modal dialog

Key classes for the modal:

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Class</th>
<th>Element</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>wzlw-modal-overlay</code></td>
<td>Semi-transparent backdrop.</td>
</tr>
<tr>
<td><code>wzlw-modal-container</code></td>
<td>The dialog box itself.</td>
</tr>
<tr>
<td><code>wzlw-modal-close-btn</code></td>
<td>The close (×) button.</td>
</tr>
<tr>
<td><code>wzlw-modal-title</code></td>
<td>The <code>&lt;h2&gt;</code> heading.</td>
</tr>
<tr>
<td><code>wzlw-modal-message</code></td>
<td>The body text.</td>
</tr>
<tr>
<td><code>wzlw-modal-url</code></td>
<td>The destination URL display.</td>
</tr>
<tr>
<td><code>wzlw-modal-actions</code></td>
<td>The button row.</td>
</tr>
<tr>
<td><code>wzlw-modal-cancel</code></td>
<td>The cancel button.</td>
</tr>
<tr>
<td><code>wzlw-modal-continue</code></td>
<td>The continue button.</td>
</tr>
</tbody>
</table>
</figure>

Example — wider modal with a darker overlay:

```css
.wzlw-modal-container {
    width: 640px;
}

:root {
    --wzlw-modal-overlay-bg: rgba(0, 0, 0, 0.85);
}
```

## Styling the redirect screen

The redirect page uses a centered card layout. Key classes:

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Class</th>
<th>Element</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>wzlw-redirect-container</code></td>
<td>Full-page wrapper.</td>
</tr>
<tr>
<td><code>wzlw-redirect-content</code></td>
<td>The card.</td>
</tr>
<tr>
<td><code>wzlw-redirect-icon</code></td>
<td>The SVG icon area.</td>
</tr>
<tr>
<td><code>wzlw-redirect-title</code></td>
<td>The <code>&lt;h1&gt;</code> heading.</td>
</tr>
<tr>
<td><code>wzlw-redirect-message</code></td>
<td>The message paragraph.</td>
</tr>
<tr>
<td><code>wzlw-redirect-url-container</code></td>
<td>The URL display block.</td>
</tr>
<tr>
<td><code>wzlw-redirect-url-label</code></td>
<td>The “Destination:” label.</td>
</tr>
<tr>
<td><code>wzlw-redirect-url</code></td>
<td>The destination domain.</td>
</tr>
<tr>
<td><code>wzlw-redirect-url-full</code></td>
<td>The full destination URL (monospace).</td>
</tr>
<tr>
<td><code>wzlw-redirect-actions</code></td>
<td>The button row.</td>
</tr>
<tr>
<td><code>wzlw-redirect-continue</code></td>
<td>The “Continue to site” button.</td>
</tr>
<tr>
<td><code>wzlw-redirect-back</code></td>
<td>The “Go back” button.</td>
</tr>
<tr>
<td><code>wzlw-redirect-countdown</code></td>
<td>The countdown text.</td>
</tr>
<tr>
<td><code>wzlw-countdown-number</code></td>
<td>The countdown number.</td>
</tr>
</tbody>
</table>
</figure>

Example — dark redirect page:

```css
:root {
    --wzlw-color-page-bg: #1a1a2e;
    --wzlw-color-surface: #1e1e2f;
    --wzlw-color-text: #e0e0e0;
    --wzlw-color-text-muted: #b0b0b0;
}
```

## Reduced motion support

The plugin respects `prefers-reduced-motion: reduce`. When this media query matches, the modal slide-up animation and redirect fade-in animation are disabled, and button transitions are removed. No additional configuration is needed.

## RTL support

The plugin ships with RTL-specific stylesheets (`frontend-rtl.css`, `redirect-rtl.css`). These are loaded automatically when `is_rtl()` returns `true`. If you are adding custom CSS, test with both LTR and RTL layouts.

## Enqueue order

The plugin stylesheet is enqueued with the handle `wzlw-frontend`. If you need to ensure your overrides load after the plugin, declare a dependency:

```php
wp_enqueue_style(
    'my-theme-wzlw-overrides',
    get_stylesheet_directory_uri() . '/css/wzlw-overrides.css',
    array( 'wzlw-frontend' ),
    '1.0.0'
);
```

This guarantees your stylesheet loads after the plugin’s frontend CSS.
