=== WebberZone Link Warnings ===
Contributors: webberzone, ajay
Tags: accessibility, external links, wcag, target blank, compliance
Donate link: https://ajaydsouza.com/donate/
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add accessible warnings for external links and `target="_blank"` links in WordPress — using icons, modals, or redirect screens.

== Description ==

WebberZone Link Warnings helps you warn users when links open in a new window or take them to external websites. It adds accessible indicators, confirmation dialogs, or redirect screens — helping you align with accessibility best practices without rewriting your content.

WebberZone Link Warnings uses a two-layer approach to process links across your entire site. For post content, it uses WordPress's native `WP_HTML_Tag_Processor` class to efficiently parse and modify links at render time. For everything else — navigation menus, footers, sidebars, widgets, and theme output — a lightweight JavaScript scan runs after the page loads and applies the same rules. It adds appropriate ARIA attributes, visual indicators, and optional JavaScript-based warnings based on your configuration. Your stored content remains untouched — the plugin only alters rendered output and does not interfere with REST API responses or admin editing screens.

### Why warn users about external links?

* `target="_blank"` can disorient screen reader users
* Sudden context changes impact usability
* Accessibility audits often recommend user warnings
* Agencies and site owners often need documented user warnings during accessibility reviews

### Key features

* __Multiple Warning Methods__: Choose from inline indicators, modal dialogs, or redirect screens — or combine them
* __Flexible Scope__: Target external links only, or external links plus all `target="_blank"` links
* __Customizable Indicators__: Configure visual icons, text, or screen reader-only warnings
* __Modal Dialog__: Show a confirmation dialog before users navigate to external sites with keyboard navigation and focus management
* __Dismissible Modals__: Let repeat visitors tick "Don't show again" so the modal is skipped for a session or for a set number of days, per destination domain or sitewide
* __Redirect Screen__: Display an intermediate page with a configurable countdown before external navigation
* __Force External__: Add a class to any link or wrapper element to force it to be treated as external — useful for tracking URLs or redirects that use internal paths
* __Automatic Link Attributes__: Add `nofollow`, `sponsored`, `ugc`, `noopener`, `noreferrer` and `target="_blank"` to external links, affiliate links, or both — no post edits needed
* __Affiliate Link Marking__: Flag a link or container with a class and give affiliate links their own attributes
* __Domain Exclusions__: Allow trusted domains to treat them as internal links
* __Sitewide Coverage__: Links in navigation menus, footers, sidebars, widgets, and theme output are processed alongside post content
* __Post Type Control__: PHP-side processing is scoped to configured post types; JS scanning covers the full page regardless
* __Built to support accessibility best practices for external link behaviour in WordPress__: Adds screen reader text, ARIA attributes, and keyboard-friendly modal confirmations
* __Setup Wizard__: Get started quickly with a guided setup wizard on first activation
* __Template Override__: Override the redirect screen template in your theme for full design control
* __RTL Support__: Full right-to-left language support for all frontend and admin styles
* __Multisite Compatible__: Network activate and configure per-site settings
* __Privacy Focused__: Does not collect personal data or send link data to third-party services
* __Performance Optimized__: Uses WordPress's native `WP_HTML_Tag_Processor` class to process links at display time
* __Developer-Friendly__: Filters and actions allow developers to customize behavior, exclude domains, and output

> This plugin assists with user awareness of external navigation. It does not automatically make your website fully accessible or legally compliant.

### How it works

After activation, the setup wizard guides you through the initial configuration. You can also configure the plugin at __Settings > WebberZone Link Warnings__.

__Warning Methods:__

* Inline indicators only (visual and/or screen reader text)
* Modal dialog (JavaScript-based confirmation)
* Redirect screen (intermediate page with countdown)
* Combined approach (inline + modal)
* Combined approach (inline + redirect)

__Link Scope:__

* External links only
* External links and all `target="_blank"` links

__Visual Indicators:__

* Icon only (↗)
* Text only (customizable)
* Icon + text
* None (screen reader only)

__Advanced Settings:__

* Custom modal messages and button text
* Modal frequency, dismissal scope and "Don't show again" label
* Custom redirect page content and countdown duration
* Link attributes for external and affiliate links (`nofollow`, `sponsored`, `ugc`, open in a new tab, `noopener`, `noreferrer`)
* Affiliate link class and wrapper class (defaults: `wzlw-affiliate`, `wzlw-affiliate-wrapper`)
* Domain exclusion list
* Post type selection
* Force-external class name (default: `wzlw-force-external`)

### GDPR

WebberZone Link Warnings doesn't collect personal data or send information to external services — making it GDPR-friendly by default.

You remain responsible for your site's overall GDPR compliance.

### Contribute

WebberZone Link Warnings is also available on [Github](https://github.com/WebberZone/webberzone-link-warnings).
So, if you've got a cool feature you'd like to implement in the plugin or a bug you've fixed, consider forking the project and sending me a pull request.

Bug reports are [welcomed on GitHub](https://github.com/WebberZone/webberzone-link-warnings/issues). Please note that GitHub is _not_ a support forum, and issues that aren't suitably qualified as bugs will be closed.

### Translations

WebberZone Link Warnings is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/webberzone-link-warnings). Check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/plugin-theme-authors-guide/) to contribute.

### Other Plugins by WebberZone

WebberZone Link Warnings is one of the many plugins developed by WebberZone. Check out our other plugins:

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
* [Top 10](https://wordpress.org/plugins/top-10/) - Track daily and total visits to your blog posts and display the popular and trending posts
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - The ultimate snippet manager for WordPress to create and manage custom HTML, CSS or JS code snippets
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
* [Better Search](https://wordpress.org/plugins/better-search/) - Enhance the default WordPress search with contextual results sorted by relevance
* [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions
* [Popular Authors](https://wordpress.org/plugins/popular-authors/) - Display popular authors in your WordPress widget
* [Followed Posts](https://wordpress.org/plugins/where-did-they-go-from-here/) - Show a list of related posts based on what your users have read

== Installation ==

= WordPress install (the easy way) =

1. Navigate to Plugins > Add New
2. Search for "WebberZone Link Warnings"
3. Click "Install Now" and then "Activate"

= Manual install =

1. Upload the `webberzone-link-warnings` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > WebberZone Link Warnings to configure

== Frequently Asked Questions ==

= Does this plugin affect SEO? =

Out of the box, no. WebberZone Link Warnings only modifies how links are displayed to users and never alters the `href` attribute or link structure.

If you enable the __Link Attributes__ settings under Settings > WebberZone Link Warnings > Advanced, the plugin will add `rel` and `target` attributes to matching links, which is a deliberate SEO signal that you control. Attributes on links inside post content are written server-side and are present in the HTML that search engines download. Attributes on links elsewhere on the page — navigation menus, widgets, footers and other theme output — are applied by the JavaScript scan after the page loads, so they are only visible to crawlers that execute JavaScript.

= Is it accessible? =

Yes. The plugin adds screen reader text, ARIA attributes, and (for modal mode) keyboard navigation and focus management.

= Does it work with multilingual sites? =

Yes. WebberZone Link Warnings is translation-ready. It uses standard WordPress translation functions and works with popular multilingual plugins such as WPML and Polylang.

= Does this work with page builders? =

Yes. Post content is processed via standard WordPress filters (`the_content` and `the_excerpt`). In addition, a JavaScript scan runs after the page loads and processes any links not already handled by PHP — including output generated by page builders, theme templates, navigation menus, and widgets.

= Can I customize the redirect screen template? =

Yes. Copy the template file to `your-theme/webberzone-link-warnings/redirect-screen.php` to override the default redirect screen with your own design.

= How can I force an internal link to be treated as external? =

Add the class `wzlw-force-external` directly to the `<a>` tag:

`<a href="/go/partner/" class="wzlw-force-external">Partner link</a>`

To force all links inside a container, add `wzlw-force-external-wrapper` to the wrapper element instead:

`<div class="wzlw-force-external-wrapper"><a href="/go/product-a/">Product A</a><a href="/go/product-b/">Product B</a></div>`

Both class names are configurable under Settings > WebberZone Link Warnings > Advanced.

= How do I add rel="nofollow" or rel="sponsored" automatically? =

Go to Settings > WebberZone Link Warnings > Advanced and use the __Link Attributes__ section. Tick any combination of `rel="nofollow"`, `rel="sponsored"`, `rel="ugc"`, "Open in a new tab", `rel="noopener"` and `rel="noreferrer"` under __External Links__, __Affiliate Links__, or both.

Attributes already on your links are kept. A link that carries `rel="me author"` becomes `rel="me author nofollow"` rather than losing its original values, and a link that already has `rel="NoFollow"` is not given a second copy — matching ignores case.

`noopener` and `noreferrer` are only added to links that actually open in a new tab, either because the link already has `target="_blank"` or because you enabled the new-tab option. Every option is off by default and nothing is added unless you tick it — the plugin has never rewritten `rel` on your links before this version.

They are deliberately separate options. Current browsers already imply `noopener` for `target="_blank"`, so ticking it mainly satisfies security scanners and older browsers and costs you nothing. `noreferrer` is the one with a behavioural cost: it also stops the referrer being sent, so leave it unticked for affiliate links if your merchant relies on the referrer for attribution.

= How do I mark affiliate links? =

Add the class `wzlw-affiliate` directly to the `<a>` tag:

`<a href="https://merchant.example.com/product" class="wzlw-affiliate">Buy now</a>`

To mark every link inside a container, add `wzlw-affiliate-wrapper` to the wrapper element instead:

`<div class="wzlw-affiliate-wrapper"><a href="https://merchant.example.com/a">Product A</a><a href="https://merchant.example.com/b">Product B</a></div>`

Affiliate links receive both the __External Links__ and __Affiliate Links__ attribute sets. Both class names are configurable and accept comma-separated values under Settings > WebberZone Link Warnings > Advanced.

Note that marking a link as an affiliate link also treats it as external for warning purposes, exactly like the force-external class. An internal cloaked URL such as `/go/product/` will show your configured modal, redirect screen or indicator, and a domain on your exclusion list will show a warning if you also give the link an affiliate class.

= How can I prevent icons from appearing on specific links? =

Add the class `wzlw-no-icon` to any link where you don't want an icon to appear. For example:

`<a href="https://example.com" class="wzlw-no-icon">Link without icon</a>`

This suppresses the visual indicator (icon and/or text). The modal or redirect warning still applies if your warning method includes one. Screen reader text and ARIA attributes are still added.

To exclude all links inside a wrapper element (e.g. a navigation block or card), add the class `wzlw-no-icon-wrapper` to the containing element:

`<div class="wzlw-no-icon-wrapper"><a href="https://example.com">Link without icon</a></div>`

All links inside that wrapper will have their visual indicators suppressed.

= Can I customize the icon that appears? =

Yes. Go to Settings > WebberZone Link Warnings > Display tab and find the "Icon Style" dropdown. You can choose from several preset icons:

- ↗ Arrow (North-East) - default
- → Arrow (Right)
- ⬈ Arrow (Up-Right)
- ⧉ External Link Symbol
- 🔗 Link Emoji
- 🌐 Globe Emoji
- * Asterisk
- Custom (enter your own)

**Using Custom Icons:** Select "Custom" and enter any Unicode symbol or emoji in the "Custom Icon" field. Examples: →, ⇗, 🔗, 🌐, *, +

= Can visitors stop the modal from showing every time? =

Yes. Go to Settings > WebberZone Link Warnings > Display and set "Modal Frequency" to either "Once per browser session" or "Once every N days". The modal then shows a "Don't show again" checkbox. When a visitor ticks it and clicks Continue, the dismissal is remembered and the link behaves like a normal link on later clicks.

"Dismissal Scope" controls how far that goes. "Per destination domain" suppresses the modal for the domain the visitor dismissed and leaves every other external link alone. "All external links" suppresses it everywhere on the site.

Dismissals live in the visitor's browser, in `sessionStorage` for the session option and `localStorage` for the N days option. No cookies are set and nothing is stored against a user account, so the setting is per browser rather than per person. Clearing site data resets it.

The default is "Always show the modal", which is the behaviour from earlier versions.

= Does this modify my database content? =

No. The plugin only alters rendered output. Your stored content remains unchanged.

= What happens if I deactivate the plugin? =

Your links return to their original state. The plugin doesn't modify your content in the database — it only changes how links are displayed.

= Where do I report security bugs found in this plugin? =

Please report security bugs found in the source code of the WebberZone Link Warnings plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/a72edb1c-fea7-41b5-a0a1-fbc9308e3a80). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

== Screenshots ==

1. External link with icon indicator
2. Modal dialog warning before navigation

== Changelog ==

= 1.5.0 (4 August 2026) =

[Version 1.5.0 Release Post](https://webberzone.com/announcements/link-warnings-v1-5-0/)

**New Features**

* New **Link Attributes** section under Settings > WebberZone Link Warnings > Advanced adds `rel` and `target` attributes to your links automatically. Pick any combination of `rel="nofollow"`, `rel="sponsored"`, `rel="ugc"`, "Open in a new tab" (`target="_blank"`), `rel="noopener"` and `rel="noreferrer"`, configured separately for external links and for affiliate links.
* Existing `rel` values are preserved rather than overwritten, and matching ignores case. A link with `rel="me author"` becomes `rel="me author nofollow"`, and a link that already has `rel="NoFollow"` is not given a duplicate.
* `noopener` and `noreferrer` are separate options and are only added to links that actually open in a new tab — either because the link already carries `target="_blank"` or because you enabled the new-tab option. They are kept apart because `noreferrer` also stops the referrer being sent, which can break referrer-based affiliate attribution, while `noopener` is a pure security hint.
* New **Affiliate Link Class** (default `wzlw-affiliate`) and **Affiliate Link Wrapper Class** (default `wzlw-affiliate-wrapper`) settings mark a single link, or every link inside a container, as an affiliate link. Both accept comma-separated values, and affiliate links receive both the external and the affiliate attribute sets.
* Links marked as affiliate links are also treated as external for warning purposes, so an internal cloaked URL such as `/go/product/` shows the same modal, redirect screen or indicator as a genuine outbound link. This mirrors how the force-external class already behaves and takes precedence over the excluded domains list.
* Attributes are applied by both processing layers — `WP_HTML_Tag_Processor` for post content, and the sitewide JavaScript scan for navigation menus, footers, sidebars, widgets and other theme output.
* Repeat visitors can now dismiss the modal instead of confirming every external link. Set **Modal Frequency** under Settings > WebberZone Link Warnings > Display to "Once per browser session" or "Once every N days" and the modal gains a "Don't show again" checkbox. Tick it, click Continue, and the modal is skipped on later clicks. The default remains "Always show the modal", so existing sites behave exactly as before.
* New **Dismissal Scope** setting decides whether a dismissal applies only to the destination domain the visitor dismissed, or to every external link on the site.
* New **Remember Dismissal For** setting sets how many days a dismissal lasts, from 1 to 365.
* The checkbox label is configurable via **Don't Show Again Label** and is registered for WPML string translation.
* Dismissals are stored in the visitor's own browser using `sessionStorage` or `localStorage`. No cookies are set and nothing is written to your database.

**Improvements**

* The Advanced tab is now split under two headings, **Link Attributes** and **Exclusions and Classes**, so the domain exclusion and class settings are no longer grouped under the attribute options.

**Bug Fixes**

* **Internal `target="_blank"` links inside post content never triggered a warning.** Under the "External links and internal links opening in a new tab" scope, PHP marked these links as processed but wrote no data attributes, so the JavaScript scan skipped them as well. The identical link in a navigation menu or widget did get a warning. PHP now mirrors the JavaScript logic and emits `data-wzlw-blank` for internal new-tab links.
* The signed redirect URL is no longer added to every processed link when the warning method is modal only. It is emitted for the redirect and inline redirect methods alone, which drops an unused attribute from the rendered HTML and skips a per-link HMAC on modal pages.

= 1.4.0 (23 May 2026) =

[Version 1.4.0 Release Post](https://webberzone.com/announcements/link-warnings-v1-4-0/)

**New Features**

* Excluded domains now support wildcard entries: `*.example.com` matches any subdomain of `example.com` but not the base domain itself. Plain entries (e.g. `example.com`) match that exact domain only. Add both to exclude a domain and all its subdomains.
* Excluded domains are now honoured by the sitewide JavaScript scan, not just PHP content processing. Previously, links excluded in settings could still be flagged as external by the JS scan on navigation menus, footers, and widgets.
* All four class settings (Suppress Icon Class, Suppress Icon Wrapper Class, Force External Class, Force External Wrapper Class) now accept comma-separated values, allowing multiple class names per setting.

**Bug Fixes**

* Excluded domains with `target="_blank"` in scope=both mode no longer incorrectly show a modal or redirect warning when processed by the JavaScript scanner. ARIA attributes are still applied for screen reader accessibility.

= 1.3.0 (1 May 2026) =

[Version 1.3.0 Release Post](https://webberzone.com/announcements/link-warnings-v1-3-0/)

**New Features**

* Sitewide link processing: JavaScript now scans the full rendered page on load and applies warnings to links in navigation menus, footers, sidebars, widgets, and any other theme output — not just post content. All four CSS class rules (`wzlw-force-external`, `wzlw-force-external-wrapper`, `wzlw-no-icon`, `wzlw-no-icon-wrapper`) work everywhere on the page.

**Bug Fixes**

* Links inside `wzlw-no-icon-wrapper` now correctly receive `data-wzlw-*` attributes so the redirect/modal warning still fires; only the visual icon is suppressed.

= 1.2.0 (14 April 2026) =

[Version 1.2.0 Release Post](https://webberzone.com/announcements/link-warnings-v1-2-0/)

**New Features**

* Add `wzlw-force-external` / `wzlw-force-external-wrapper` class support to force links to be treated as external regardless of automatic detection. Both class names are configurable under Settings > Advanced.
* The `wzlw-no-icon` and `wzlw-no-icon-wrapper` class names are now configurable under Settings > Advanced.

**Security**

* Redirect endpoint now requires an HMAC signature on every URL. Unsigned or tampered URLs are rejected, preventing open-redirect abuse.

**Bug Fixes**

* Redirect URLs with HMAC signatures were broken due to double-encoding of the `&` separator in HTML output.
* Redundant URL encoding in `get_redirect_url()` caused malformed redirect URLs.
* Same-host check now normalises hostnames before comparison, so variants like `EXAMPLE.COM` or `example.com.` are treated as internal.
* Excluded domains now match correctly when entered with a scheme or trailing path.

= 1.1.0 (14 March 2026) =

[Version 1.1.0 Release Post](https://webberzone.com/announcements/link-warnings-v1-1-0/)

**New Features**

* Add `wzlw-no-icon-wrapper` class support — add it to any wrapper element to exclude all links inside it from visual indicators.

**Improvements**

* Enhanced modal accessibility: background content is now hidden from screen readers when the modal is open, URL display includes a screen reader label, buttons have fallback accessible names, and the Continue button announces "opens in a new window" for `target="_blank"` links.

= 1.0.0 (7 March 2026) =

[Version 1.0.0 Release Post](https://webberzone.com/announcements/link-warnings-v1-0-0/)

* Initial release.

== Upgrade Notice ==

= 1.5.0 =
New Link Attributes settings under Advanced add `rel="nofollow"`, `sponsored`, `ugc`, `target="_blank"`, `noopener` and `noreferrer` to external and affiliate links automatically, keeping any `rel` values your links already have. New `wzlw-affiliate` and `wzlw-affiliate-wrapper` classes mark affiliate links; note that affiliate-marked links are also treated as external for warnings, which overrides the excluded domains list for those links. Repeat visitors can also now dismiss the modal — set Modal Frequency to "Once per browser session" or "Once every N days" to add a "Don't show again" checkbox; the default keeps the modal showing every time. Also fixes internal `target="_blank"` links in post content not triggering a warning under the "external links and internal new tab links" scope.

= 1.4.0 =
Excluded domains now support wildcard subdomains (`*.example.com`) and are honoured by the sitewide JS scan. Plain entries now match the exact domain only — add `*.example.com` alongside `example.com` if you also want subdomains excluded. All four CSS class settings now accept comma-separated values.
