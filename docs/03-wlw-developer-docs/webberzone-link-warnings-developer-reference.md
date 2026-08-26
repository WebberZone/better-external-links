---
slug: webberzone-link-warnings-developer-reference
title: "WebberZone Link Warnings Developer Reference"
products: [link-warnings]
sections: ["03-wlw-developer-docs"]
tags: [developer, link-warnings]
status: publish
order: 0
---

This article covers the filters, actions, PHP functions, and integration points for [WebberZone Link Warnings](https://webberzone.com/plugins/webberzone-link-warnings/). All hooks use the `wzlw` prefix.

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
    $settings[['warning_method']] = 'modal';
    return $settings;
} );
```

**Parameters:**

- `$settings` *(array)* — The merged settings array.
