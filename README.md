# WebberZone Link Warnings

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/webberzone-link-warnings.svg?style=flat-square)](https://wordpress.org/plugins/webberzone-link-warnings/)
[![License](https://img.shields.io/badge/license-GPL_v2%2B-orange.svg?style=flat-square)](https://opensource.org/licenses/GPL-2.0)
[![WordPress Tested](https://img.shields.io/wordpress/v/webberzone-link-warnings.svg?style=flat-square)](https://wordpress.org/plugins/webberzone-link-warnings/)
[![Required PHP](https://img.shields.io/wordpress/plugin/required-php/webberzone-link-warnings?style=flat-square)](https://wordpress.org/plugins/webberzone-link-warnings/)
[![Active installs](https://img.shields.io/wordpress/plugin/installs/webberzone-link-warnings?style=flat-square)](https://wordpress.org/plugins/webberzone-link-warnings/)

__Requires at least:__ 6.6

__Tested up to:__ 7.0

__Requires PHP:__ 7.4

__License:__ [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [WebberZone Link Warnings](https://webberzone.com/plugins/webberzone-link-warnings/) | [WordPress.org listing](https://wordpress.org/plugins/webberzone-link-warnings/)

Add accessible warnings for external links and `target="_blank"` links in WordPress — using icons, modals, or redirect screens.

## Description

WebberZone Link Warnings helps you warn users when links open in a new window or take them to external websites. It adds accessible indicators, confirmation dialogs, or redirect screens — helping you align with accessibility best practices without rewriting your content.

WebberZone Link Warnings uses a two-layer approach to process links across your entire site. For post content, it uses WordPress's native `WP_HTML_Tag_Processor` class to efficiently parse and modify links at render time. For everything else — navigation menus, footers, sidebars, widgets, and theme output — a lightweight JavaScript scan runs after the page loads and applies the same rules. It adds appropriate ARIA attributes, visual indicators, and optional JavaScript-based warnings based on your configuration. Your stored content remains untouched — the plugin only alters rendered output and does not interfere with REST API responses or admin editing screens.

### Why warn users about external links?

- `target="_blank"` can disorient screen reader users
- Sudden context changes impact usability
- Accessibility audits often recommend user warnings
- Agencies and site owners often need documented user warnings during accessibility reviews

## Key features

- __Multiple Warning Methods__: Choose from inline indicators, modal dialogs, or redirect screens — or combine them
- __Flexible Scope__: Target external links only, or external links plus all `target="_blank"` links
- __Customizable Indicators__: Configure visual icons, text, or screen reader-only warnings
- __Modal Dialog__: Show a confirmation dialog before users navigate to external sites with keyboard navigation and focus management
- __Dismissible Modals__: Let repeat visitors tick "Don't show again" so the modal is skipped for a session or for a set number of days, per destination domain or sitewide
- __Redirect Screen__: Display an intermediate page with a configurable countdown before external navigation
- __Force External__: Add a class to any link or wrapper element to force it to be treated as external — useful for tracking URLs or redirects that use internal paths
- __Automatic Link Attributes__: Add `nofollow`, `sponsored`, `ugc`, `noopener`, `noreferrer` and `target="_blank"` to external links, affiliate links, or both — no post edits needed
- __Affiliate Link Marking__: Flag a link or container with a class and give affiliate links their own attributes
- __Domain Exclusions__: Allow trusted domains to treat them as internal links
- __Sitewide Coverage__: Links in navigation menus, footers, sidebars, widgets, and theme output are processed alongside post content
- __Post Type Control__: PHP-side processing is scoped to configured post types; JS scanning covers the full page regardless
- __Built to support accessibility best practices for external link behaviour in WordPress__: Adds screen reader text, ARIA attributes, and keyboard-friendly modal confirmations
- __Setup Wizard__: Get started quickly with a guided setup wizard on first activation
- __Template Override__: Override the redirect screen template in your theme for full design control
- __RTL Support__: Full right-to-left language support for all frontend and admin styles
- __Multisite Compatible__: Network activate and configure per-site settings
- __Privacy Focused__: Does not collect personal data or send link data to third-party services
- __Performance Optimized__: Uses WordPress's native `WP_HTML_Tag_Processor` class to process links at display time
- __Developer-Friendly__: Filters and actions allow developers to customize behavior, exclude domains, and output

> This plugin assists with user awareness of external navigation. It does not automatically make your website fully accessible or legally compliant.

## Installation

### WordPress install (the easy way)

1. Navigate to __Plugins > Add New__ in your WordPress dashboard
2. Search for "WebberZone Link Warnings"
3. Click __Install Now__ and then __Activate__

### Manual install

1. Download the latest release from the [releases page](https://github.com/WebberZone/webberzone-link-warnings/releases)
2. Upload the `webberzone-link-warnings` folder to `/wp-content/plugins/`
3. Activate the plugin through the __Plugins__ menu in WordPress
4. Go to __Settings > WebberZone Link Warnings__ to configure

## Configuration

After activation, the setup wizard guides you through the initial configuration. You can also configure the plugin at __Settings > WebberZone Link Warnings__:

1. __Choose Warning Method__
   - Inline indicators (visual and screen reader text)
   - Modal dialog (JavaScript confirmation)
   - Redirect screen (intermediate page)
   - Inline indicators + modal dialog
   - Inline indicators + redirect screen

2. __Set Link Scope__
   - External links only
   - External links and all `target="_blank"` links

3. __Customize Indicators__
   - Visual indicator type (icon, text, both, none)
   - Custom text for indicators
   - Screen reader text

4. __Configure Modal/Redirect__ (if applicable)
   - Modal title and message
   - Button text
   - Modal frequency, dismissal scope and "Don't show again" label
   - Redirect page content and countdown duration

5. __Advanced Settings__
   - Link attributes for external and affiliate links (`nofollow`, `sponsored`, `ugc`, open in a new tab, `noopener`, `noreferrer`)
   - Affiliate link class and wrapper class (defaults: `wzlw-affiliate`, `wzlw-affiliate-wrapper`)
   - Excluded domains
   - Enabled post types
   - Force-external class name (default: `wzlw-force-external`)

## Letting Visitors Dismiss the Modal

Confirming every external link gets tiring for regular readers. Set __Modal Frequency__ under __Settings > WebberZone Link Warnings > Display__ to one of the following:

- __Always show the modal__ (default): the modal appears on every click, as in earlier versions
- __Once per browser session__: the dismissal is kept in `sessionStorage` and cleared when the browser tab closes
- __Once every N days__: the dismissal is kept in `localStorage` and expires after the number of days set in __Remember Dismissal For__

Choosing either of the last two adds a "Don't show again" checkbox to the modal. When a visitor ticks it and clicks Continue, the dismissal is recorded and later clicks follow the link directly, keeping `target="_blank"` behaviour intact.

__Dismissal Scope__ controls the reach of a dismissal:

- __Per destination domain__: dismissing a warning for `example.com` affects that domain only
- __All external links__: one dismissal suppresses the modal for every external link on the site

Everything is stored in the visitor's browser under the `wzlwDismissed` key. No cookies are set, nothing is written to the database, and no dismissal is tied to a user account, so the state is per browser rather than per person.

The checkbox label comes from the __Don't Show Again Label__ setting and is registered in `wpml-config.xml` for string translation.

## Excluding Icons on Specific Links

To prevent icons from appearing on a specific link, add the class `wzlw-no-icon` to the link:

```html
<a href="https://example.com" class="wzlw-no-icon">Link without icon</a>
```

To exclude all links inside a wrapper element (e.g. a navigation block or card), add the class `wzlw-no-icon-wrapper` to the containing element:

```html
<div class="wzlw-no-icon-wrapper">
  <a href="https://example.com">Link without icon</a>
</div>
```

Both approaches suppress the visual indicator (icon and/or text). The modal or redirect warning still applies if your warning method includes one. Screen reader text and ARIA attributes are still added.

## Forcing Links to Be Treated as External

To force an internal link to be treated as external, add the class `wzlw-force-external` directly to the `<a>` tag:

```html
<a href="/go/partner/" class="wzlw-force-external">Partner link</a>
```

To force all links inside a container, add `wzlw-force-external-wrapper` to the wrapper element:

```html
<div class="wzlw-force-external-wrapper">
  <a href="/go/product-a/">Product A</a>
  <a href="/go/product-b/">Product B</a>
</div>
```

Both class names are configurable under __Settings > WebberZone Link Warnings > Advanced__.

## Adding rel and target Attributes Automatically

The __Link Attributes__ section under __Settings > WebberZone Link Warnings > Advanced__ adds attributes to your links at render time. Tick any combination of the following, separately for __External Links__ and __Affiliate Links__:

- `rel="nofollow"`
- `rel="sponsored"`
- `rel="ugc"`
- Open in a new tab (`target="_blank"`)
- `rel="noopener"` for new-tab links
- `rel="noreferrer"` for new-tab links

Everything is off by default, so nothing changes until you opt in.

Attributes already present on a link are kept rather than overwritten, and matching ignores case:

```html
<!-- source -->
<a href="https://example.com/" rel="me author">Example</a>

<!-- rendered with nofollow enabled -->
<a href="https://example.com/" rel="me author nofollow">Example</a>
```

A link that already carries `rel="NoFollow"` is not given a duplicate.

`noopener` and `noreferrer` are only applied to links that actually open in a new tab — either the link already has `target="_blank"`, or you enabled the new-tab option.

They are separate options on purpose. Current browsers already imply `noopener` for `target="_blank"`, so ticking it mainly satisfies security scanners and older browsers and costs you nothing. `noreferrer` is the one with a behavioural cost — it also suppresses the referrer, so leave it off for affiliate links if your merchant relies on the referrer for attribution.

## Marking Affiliate Links

To mark a single link as an affiliate link, add the class `wzlw-affiliate` directly to the `<a>` tag:

```html
<a href="https://merchant.example.com/product" class="wzlw-affiliate">Buy now</a>
```

To mark every link inside a container, add `wzlw-affiliate-wrapper` to the wrapper element:

```html
<div class="wzlw-affiliate-wrapper">
  <a href="https://merchant.example.com/a">Product A</a>
  <a href="https://merchant.example.com/b">Product B</a>
</div>
```

Affiliate links receive both the __External Links__ and __Affiliate Links__ attribute sets, so external `nofollow` plus affiliate `sponsored` produces `rel="nofollow sponsored"`. Both class names are configurable and accept comma-separated values.

> Marking a link as an affiliate link also treats it as external for warning purposes, exactly like the force-external class. An internal cloaked URL such as `/go/product/` will show your configured modal, redirect screen or indicator, and a domain on your exclusion list will still show a warning if you give the link an affiliate class.

## Customizing Icons

You can choose from several preset icons or enter your own:

1. Go to __Settings > WebberZone Link Warnings > Display tab__
2. Find the __"Icon Style"__ dropdown
3. Select from available options:
   - ↗ Arrow (North-East) - default
   - → Arrow (Right)
   - ⬈ Arrow (Up-Right)
   - ⧉ External Link Symbol
   - 🔗 Link Emoji
   - 🌐 Globe Emoji
   - \* Asterisk
   - Custom (enter your own)

### Using Custom Icons

To use a custom icon:

1. Select "Custom" from the Icon Style dropdown
2. In the "Custom Icon" field, enter any Unicode symbol or emoji
3. Examples: `→`, `⇗`, `🔗`, `🌐`, `*`, `+`

## Frequently Asked Questions

### Does this plugin affect SEO?

Out of the box, no. WebberZone Link Warnings only modifies how links are displayed to users and never alters the `href` attribute or link structure.

If you enable the __Link Attributes__ settings under __Settings > WebberZone Link Warnings > Advanced__, the plugin adds `rel` and `target` attributes to matching links, which is a deliberate SEO signal that you control. Attributes on links inside post content are written server-side and are present in the HTML search engines download. Attributes on links elsewhere on the page — navigation menus, widgets, footers and other theme output — are applied by the JavaScript scan after the page loads, so they are only visible to crawlers that execute JavaScript.

### Is it accessible?

Yes. The plugin adds screen reader text, ARIA attributes, and (for modal mode) keyboard navigation and focus management.

### Does it work with multilingual sites?

Yes. WebberZone Link Warnings is translation-ready. It uses standard WordPress translation functions and works with popular multilingual plugins such as WPML and Polylang.

### Does this work with page builders?

Yes. Post content is processed via standard WordPress filters (`the_content` and `the_excerpt`). In addition, a JavaScript scan runs after the page loads and processes any links not already handled by PHP — including output generated by page builders, theme templates, navigation menus, and widgets.

### Can I customize the redirect screen template?

Yes. Copy the template file to `your-theme/webberzone-link-warnings/redirect-screen.php` to override the default redirect screen with your own design.

### Can visitors stop the modal from showing every time?

Yes. Set __Modal Frequency__ to "Once per browser session" or "Once every N days" and the modal gains a "Don't show again" checkbox. See [Letting Visitors Dismiss the Modal](#letting-visitors-dismiss-the-modal) for the details.

### Does this modify my database content?

No. The plugin only alters rendered output. Your stored content remains unchanged.

### What happens if I deactivate the plugin?

Your links return to their original state. The plugin doesn't modify your content in the database — it only changes how links are displayed.

### Where do I report security bugs found in this plugin?

Please report security bugs found in the source code of the WebberZone Link Warnings plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/a72edb1c-fea7-41b5-a0a1-fbc9308e3a80). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

## Screenshots

![WebberZone Link Warnings Settings](https://github.com/WebberZone/webberzone-link-warnings/blob/main/wporg-assets/screenshot-2.png)
*WebberZone Link Warnings Settings Page*

For more screenshots, visit the [WordPress plugin page](https://wordpress.org/plugins/webberzone-link-warnings/screenshots/).

## Other plugins by WebberZone

WebberZone Link Warnings is one of the many plugins developed by WebberZone. Check out our other plugins:

- [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
- [Top 10](https://wordpress.org/plugins/top-10/) - Track daily and total visits to your blog posts and display the popular and trending posts
- [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - The ultimate snippet manager for WordPress to create and manage custom HTML, CSS or JS code snippets
- [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
- [Better Search](https://wordpress.org/plugins/better-search/) - Enhance the default WordPress search with contextual results sorted by relevance
- [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions
- [Popular Authors](https://wordpress.org/plugins/popular-authors/) - Display popular authors in your WordPress widget
- [Followed Posts](https://wordpress.org/plugins/where-did-they-go-from-here/) - Show a list of related posts based on what your users have read

## Contribute

WebberZone Link Warnings is also available on [GitHub](https://github.com/WebberZone/webberzone-link-warnings).
So, if you've got a cool feature you'd like to implement in the plugin or a bug you've fixed, consider forking the project and sending me a pull request.

Bug reports are [welcomed on GitHub](https://github.com/WebberZone/webberzone-link-warnings/issues). Please note that GitHub is *not* a support forum, and issues that aren't suitably qualified as bugs will be closed.

## Support

- __Documentation__: [webberzone.com/support/webberzone-link-warnings/](https://webberzone.com/support/webberzone-link-warnings/)
- __WordPress Support Forum__: [wordpress.org/support/plugin/webberzone-link-warnings/](https://wordpress.org/support/plugin/webberzone-link-warnings/)
- __GitHub Issues__: [github.com/WebberZone/webberzone-link-warnings/issues](https://github.com/WebberZone/webberzone-link-warnings/issues)

## Translations

WebberZone Link Warnings is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/webberzone-link-warnings). Check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/plugin-theme-authors-guide/) to contribute.

## License

This plugin is licensed under the GPL-2.0+ license.

## Credits

WebberZone Link Warnings is developed and maintained by [WebberZone](https://webberzone.com/).

## About this repository

This GitHub repository always holds the latest development version of the plugin. If you're looking for an official WordPress release, you can find this on the [WordPress.org repository](https://wordpress.org/plugins/webberzone-link-warnings/). In addition to stable releases, latest beta versions are made available under [releases](https://github.com/WebberZone/webberzone-link-warnings/releases).
