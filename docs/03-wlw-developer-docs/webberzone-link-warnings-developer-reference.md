---
slug: webberzone-link-warnings-developer-reference
title: "WebberZone Link Warnings Developer Reference"
products: [link-warnings]
sections: [03-wlw-developer-docs]
tags: [developer,link-warnings]
status: publish
order: 0
---

This article covers the filters, actions, PHP functions, and integration points for <a href="https://webberzone.com/plugins/webberzone-link-warnings/" data-type="page" data-id="9512">WebberZone Link Warnings</a>. All hooks use the `wzlw` prefix.

## PHP wrapper functions

These global functions are defined in `includes/options-api.php` and are available after the plugin loads.

### `wzlw_get_settings()`

Returns the full settings array, merged with defaults.

```php
$settings = wzlw_get_settings();
```

**Returns:** `array`

### `wzlw_get_option( $key, $default_value )`

Returns a single setting value. Falls back to the registered default if the key is not set. If `$default_value` is provided, it takes priority over the registered default.

```php
$method = wzlw_get_option( 'warning_method', 'inline' );
```

**Parameters:**

- `$key` *(string)* — The setting key.
- `$default_value` *(mixed, optional)* — Fallback value. Default `null`.

**Returns:** `mixed`

### `wzlw_update_option( $key, $value )`

Updates a single setting in the database and the in-memory cache.

```php
wzlw_update_option( 'warning_method', 'modal' );
```

**Parameters:**

- `$key` *(string)* — The setting key.
- `$value` *(mixed)* — The new value.

**Returns:** `bool` — `true` if the option was updated.

### `wzlw_delete_option( $key )`

Removes a single key from the settings array.

```php
wzlw_delete_option( 'excluded_domains' );
```

**Returns:** `bool`

### `wzlw_update_settings( $settings, $merge, $autoload )`

Replaces or merges the entire settings array.

```php
// Merge new values into existing settings.
wzlw_update_settings( array( 'warning_method' => 'redirect' ) );

// Replace all settings (no merge).
wzlw_update_settings( $new_settings, false );
```

**Parameters:**

- `$settings` *(array)* — Settings to save.
- `$merge` *(bool, optional)* — Whether to merge with existing settings. Default `true`.
- `$autoload` *(bool, optional)* — Whether to autoload the option. Default `true`.

**Returns:** `bool`

### `wzlw_settings_defaults()`

Returns the default settings array as derived from the registered settings fields.

```php
$defaults = wzlw_settings_defaults();
```

**Returns:** `array`

### `wzlw_get_default_option( $key )`

Returns the default value for a specific setting key.

```php
$default_method = wzlw_get_default_option( 'warning_method' ); // 'inline_modal'
```

**Returns:** `mixed` — The default value, or `false` if the key does not exist.

### `wzlw_settings_reset()`

Resets all settings to their defaults.

```php
wzlw_settings_reset();
```

**Returns:** `bool`

## Filter hooks

### `wzlw_get_settings`

Filters the full settings array after it is retrieved and merged with defaults.

```php
add_filter( 'wzlw_get_settings', function ( array $settings ): array {
    // Force modal method on all sites.
    $settings['warning_method'] = 'modal';
    return $settings;
} );
```

**Parameters:**

- `$settings` *(array)* — The merged settings array.

### `wzlw_get_option`

Filters the value of any individual setting when retrieved via `wzlw_get_option()`.

```php
add_filter( 'wzlw_get_option', function ( $value, string $key, $default ) {
    if ( 'redirect_countdown' === $key ) {
        return 10; // Override countdown to 10 seconds.
    }
    return $value;
}, 10, 3 );
```

**Parameters:**

- `$value` *(mixed)* — The setting value.
- `$key` *(string)* — The setting key.
- `$default_value` *(mixed)* — The default value.

### `wzlw_get_option_{$key}`

Key-specific variant of the above filter. Fires only for the named key.

```php
add_filter( 'wzlw_get_option_warning_method', function ( $value ) {
    if ( wp_is_mobile() ) {
        return 'inline'; // Use inline-only on mobile devices.
    }
    return $value;
} );
```

### `wzlw_update_option`

Filters a setting value before saving it to the database.

```php
add_filter( 'wzlw_update_option', function ( $value, string $key ) {
    if ( 'redirect_countdown' === $key ) {
        return max( 3, (int) $value ); // Enforce minimum 3 seconds.
    }
    return $value;
}, 10, 2 );
```

**Parameters:**

- `$value` *(mixed)* — The value being saved.
- `$key` *(string)* — The setting key.

### `wzlw_excluded_domains`

Filters the list of excluded domains before the external link check runs. Use this to add domains programmatically without modifying the settings.

```php
add_filter( 'wzlw_excluded_domains', function ( array $domains, string $link_host ): array {
    $domains[] = 'cdn.example.com';   // Exact match — cdn.example.com only.
    $domains[] = '*.example.com';     // Wildcard — all subdomains of example.com.
    return $domains;
}, 10, 2 );
```

**Parameters:**

- `$domains` *(array)* — Array of excluded domain strings.
- `$link_host` *(string)* — The host of the link being evaluated.

### `wzlw_settings_defaults`

Filters the default settings array. Useful for changing defaults in a must-use plugin or theme.

```php
add_filter( 'wzlw_settings_defaults', function ( array $defaults ): array {
    $defaults['warning_method'] = 'redirect';
    $defaults['redirect_countdown'] = 10;
    return $defaults;
} );
```

### `wzlw_registered_settings`

Filters the registered settings array. Use this to add, remove, or modify settings fields on the admin page.

```php
add_filter( 'wzlw_registered_settings', function ( array $settings ): array {
    // Remove the redirect countdown field.
    unset( $settings['display']['redirect_countdown'] );
    return $settings;
} );
```

### `wzlw_settings_sections`

Filters the settings page tab definitions.

```php
add_filter( 'wzlw_settings_sections', function ( array $sections ): array {
    $sections['custom'] = esc_html__( 'Custom', 'my-plugin' );
    return $sections;
} );
```

### Section-specific filters

Each settings section has its own filter, fired when the section’s fields are defined:

- `wzlw_settings_general` — General tab fields.
- `wzlw_settings_display` — Display tab fields.
- `wzlw_settings_advanced` — Advanced tab fields.

```php
add_filter( 'wzlw_settings_advanced', function ( array $settings ): array {
    $settings['my_custom_field'] = array(
        'id'      => 'my_custom_field',
        'name'    => 'My Custom Field',
        'desc'    => 'Description of the field.',
        'type'    => 'text',
        'default' => '',
    );
    return $settings;
} );
```

### `wzlw_settings_sanitize`

Filters the settings array immediately before it is saved. Runs on every settings save.

```php
add_filter( 'wzlw_settings_sanitize', function ( array $settings ): array {
    // Ensure countdown is never below 3.
    if ( isset( $settings['redirect_countdown'] ) ) {
        $settings['redirect_countdown'] = max( 3, (int) $settings['redirect_countdown'] );
    }
    return $settings;
} );
```

## Accessing the plugin instance

The main plugin singleton is accessible via the `wzlw()` function:

```php
$plugin = wzlw();

// Access sub-components.
$plugin->content_processor;
$plugin->frontend_handler;
$plugin->redirect_handler;
$plugin->admin; // Only available in admin context.
```

## Content processing hooks

The plugin filters content on two standard WordPress hooks:

- `the_content` at priority 999
- `the_excerpt` at priority 999

The high priority ensures the plugin runs after most other content filters. If you need to run after the plugin, use a priority above 999.

To prevent the plugin from processing specific content, you can remove the filter temporarily:

```php
remove_filter( 'the_content', array( wzlw()->content_processor, 'process_content' ), 999 );
echo apply_filters( 'the_content', $my_content );
add_filter( 'the_content', array( wzlw()->content_processor, 'process_content' ), 999 );
```

## JavaScript objects

The plugin exposes two JavaScript objects on the frontend, depending on the active warning method.

### `wzlwSettings`

Available when the warning method includes a modal or redirect component. Localized via `wp_localize_script()` on the `wzlw-modal` handle.

```js
wzlwSettings.siteHost                  // Lowercase site hostname (e.g. "example.com").
wzlwSettings.excludedDomains           // Array of normalised excluded domain strings.
wzlwSettings.scope                     // "external" or "both".
wzlwSettings.warningMethod             // Active warning method string.
wzlwSettings.noIconClass               // Array of class names that suppress the indicator.
wzlwSettings.noIconWrapperClass        // Array of class names that suppress indicators inside an element.
wzlwSettings.forceExternalClass        // Array of class names that force a link to be treated as external.
wzlwSettings.forceExternalWrapperClass // Array of class names that force all links inside an element to be treated as external.
wzlwSettings.visualIndicator           // "icon", "text", "both", or "none".
wzlwSettings.indicatorText             // Visible text appended to the link when text indicator is enabled.
wzlwSettings.screenReaderText          // Hidden text added for assistive technology.
wzlwSettings.modalTitle                // Modal heading text.
wzlwSettings.modalMessage              // Modal body text.
wzlwSettings.continueText              // Continue button label.
wzlwSettings.cancelText                // Cancel button label.
wzlwSettings.ajaxUrl                   // WordPress admin-ajax.php URL.
wzlwSettings.nonce                     // Nonce for the wzlw_sign_urls AJAX action.
```

Entries in `excludedDomains` follow the same format as the admin setting: plain strings (e.g. `"example.com"`) match that exact host; strings prefixed with `*.` (e.g. `"*.example.com"`) match subdomains only.

The class arrays are derived from the comma-separated settings `no_icon_class`, `no_icon_wrapper_class`, `force_external_class`, and `force_external_wrapper_class`, normalised to lowercase arrays by the plugin.

### `wzlwRedirect`

Available on the redirect interstitial page only. Localised on the `wzlw-redirect` handle.

```js
wzlwRedirect.destination // The external URL.
wzlwRedirect.countdown   // Countdown duration in seconds.
```

## Data attributes

The plugin adds the following `data-` attributes to processed external links when a modal or redirect method is active:

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Attribute</th>
<th>Value</th>
<th>Added by</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>data-wzlw-external</code></td>
<td><code>"true"</code> — marks the link as an external link handled by the plugin.</td>
<td>PHP and JS</td>
</tr>
<tr>
<td><code>data-wzlw-blank</code></td>
<td><code>"true"</code> — marks the link as an internal link that opens in a new tab (only added when scope is <code>both</code>).</td>
<td>JS only</td>
</tr>
<tr>
<td><code>data-wzlw-url</code></td>
<td>The escaped external URL.</td>
<td>PHP and JS</td>
</tr>
<tr>
<td><code>data-wzlw-redirect-url</code></td>
<td>The full redirect interstitial URL for this destination.</td>
<td>PHP and JS</td>
</tr>
</tbody>
</table>
</figure>

The frontend JavaScript uses `a[data-wzlw-external], a[data-wzlw-blank]` as the selector for click delegation.

## Modal markup hooks

The frontend JavaScript binds behavior to data attributes inside the rendered modal HTML. If you are customizing the modal markup, preserve these attributes:

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Attribute</th>
<th>Element</th>
<th>Purpose</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>data-wzlw-continue</code></td>
<td>The continue button.</td>
<td>Opens the destination URL when clicked.</td>
</tr>
<tr>
<td><code>data-wzlw-close</code></td>
<td>Any close element.</td>
<td>Closes the modal without navigating.</td>
</tr>
</tbody>
</table>
</figure>

The continue button is also targeted by the JavaScript as `#wzlw-modal-title`, `#wzlw-modal-message`, `.wzlw-modal-url-value`, and `.wzlw-modal-cancel` so its content can be updated from `wzlwSettings`.

## CSS handles

Use these handles when declaring stylesheet dependencies:

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Handle</th>
<th>File</th>
<th>Loaded on</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>wzlw-frontend</code></td>
<td><code>includes/assets/css/frontend.css</code></td>
<td>All frontend pages.</td>
</tr>
<tr>
<td><code>wzlw-redirect</code></td>
<td><code>includes/assets/css/redirect.css</code></td>
<td>Redirect interstitial page only.</td>
</tr>
</tbody>
</table>
</figure>

## Script handles

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Handle</th>
<th>File</th>
<th>Loaded on</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>wzlw-modal</code></td>
<td><code>includes/admin/js/modal.js</code></td>
<td>Frontend, when method includes modal or redirect.</td>
</tr>
<tr>
<td><code>wzlw-redirect</code></td>
<td><code>includes/assets/js/redirect.js</code></td>
<td>Redirect interstitial page only.</td>
</tr>
</tbody>
</table>
</figure>
