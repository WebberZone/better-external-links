<?php
/**
 * Register Settings.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Link_Warnings
 */

namespace WebberZone\Link_Warnings\Admin;

use WebberZone\Link_Warnings\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the settings.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Settings API.
	 *
	 * @since 1.0.0
	 *
	 * @var object Settings API.
	 */
	public $settings_api;

	/**
	 * Settings Page in Admin area.
	 *
	 * @since 1.0.0
	 *
	 * @var string Settings Page.
	 */
	public $settings_page;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * Initialised at declaration rather than only in the constructor: the static methods on
	 * this class are reachable on the frontend where the Settings object is never
	 * instantiated, and a null prefix there fires `_settings_defaults` instead of
	 * `wzlw_settings_defaults`.
	 *
	 * @since 1.0.0
	 *
	 * @var string Prefix.
	 */
	public static $prefix = 'wzlw';

	/**
	 * Settings Key.
	 *
	 * @since 1.0.0
	 *
	 * @var string Settings Key.
	 */
	public $settings_key;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @since 1.0.0
	 *
	 * @var string Menu slug.
	 */
	public $menu_slug;

	/**
	 * Main constructor class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->settings_key = 'wzlw_settings';
		self::$prefix       = 'wzlw';
		$this->menu_slug    = 'wzlw-settings';

		Hook_Registry::add_action( 'admin_menu', array( $this, 'initialise_settings' ) );
		Hook_Registry::add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 11, 2 );
		Hook_Registry::add_filter( 'plugin_action_links_' . plugin_basename( WZLW_PLUGIN_FILE ), array( $this, 'plugin_actions_links' ) );

		Hook_Registry::add_filter( self::$prefix . '_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
	}

	/**
	 * Initialise the settings API.
	 *
	 * @since 1.0.0
	 */
	public function initialise_settings() {
		$props = array(
			'default_tab'       => 'general',
			'help_sidebar'      => $this->get_help_sidebar(),
			'help_tabs'         => $this->get_help_tabs(),
			'admin_footer_text' => $this->get_admin_footer_text(),
			'menus'             => $this->get_menus(),
		);

		$args = array(
			'props'               => $props,
			'translation_strings' => $this->get_translation_strings(),
			'settings_sections'   => $this->get_settings_sections(),
			'registered_settings' => $this->get_registered_settings(),
			'upgraded_settings'   => array(),
		);

		$this->settings_api = new Settings\Settings_API( $this->settings_key, self::$prefix, $args );
	}

	/**
	 * Get settings defaults.
	 *
	 * @since 1.0.0
	 *
	 * @return array Default settings.
	 */
	public static function settings_defaults() {
		$defaults = array();

		$settings      = self::get_registered_settings();
		$default_types = array(
			'color',
			'css',
			'csv',
			'file',
			'html',
			'multicheck',
			'number',
			'numbercsv',
			'password',
			'postids',
			'posttypes',
			'radio',
			'radiodesc',
			'repeater',
			'select',
			'sensitive',
			'taxonomies',
			'text',
			'textarea',
			'thumbsizes',
			'url',
			'wysiwyg',
		);

		foreach ( $settings as $section_settings ) {
			foreach ( $section_settings as $setting ) {
				if ( ! isset( $setting['id'] ) ) {
					continue;
				}

				$setting_id    = $setting['id'];
				$setting_type  = $setting['type'] ?? '';
				$default_value = '';

				if ( 'checkbox' === $setting_type ) {
					$default_value = isset( $setting['default'] ) ? (int) (bool) $setting['default'] : 0;
				} elseif ( isset( $setting['default'] ) && in_array( $setting_type, $default_types, true ) ) {
					$default_value = $setting['default'];
				}

				$defaults[ $setting_id ] = $default_value;
			}
		}

		return apply_filters( self::$prefix . '_settings_defaults', $defaults ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Array containing the translation strings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Translation strings.
	 */
	public function get_translation_strings() {
		$strings = array(
			'page_title'           => esc_html__( 'WebberZone Link Warnings Settings', 'webberzone-link-warnings' ),
			'menu_title'           => esc_html__( 'Settings', 'webberzone-link-warnings' ),
			'page_header'          => esc_html__( 'WebberZone Link Warnings Settings', 'webberzone-link-warnings' ),
			'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'webberzone-link-warnings' ),
			'success_message'      => esc_html__( 'Settings updated.', 'webberzone-link-warnings' ),
			'save_changes'         => esc_html__( 'Save Changes', 'webberzone-link-warnings' ),
			'reset_settings'       => esc_html__( 'Reset all settings', 'webberzone-link-warnings' ),
			'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'webberzone-link-warnings' ),
			'modified_field'       => esc_html__( 'Modified from default setting', 'webberzone-link-warnings' ),
			'modified_legend'      => esc_html__( 'Setting modified from its default value', 'webberzone-link-warnings' ),
			'default_label'        => esc_html__( 'Default', 'webberzone-link-warnings' ),
			'default_none'         => esc_html__( 'None', 'webberzone-link-warnings' ),
			'button_label'         => esc_html__( 'Choose File', 'webberzone-link-warnings' ),
			'previous_saved'       => esc_html__( 'Previously saved', 'webberzone-link-warnings' ),
		);

		return apply_filters( self::$prefix . '_translation_strings', $strings ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the admin menus.
	 *
	 * @return array Admin menus.
	 */
	public function get_menus() {
		$menus = array();

		$menus[] = array(
			'settings_page' => true,
			'type'          => 'submenu',
			'parent_slug'   => 'options-general.php',
			'page_title'    => esc_html__( 'WebberZone Link Warnings Settings', 'webberzone-link-warnings' ),
			'menu_title'    => esc_html__( 'Link Warnings', 'webberzone-link-warnings' ),
			'menu_slug'     => $this->menu_slug,
		);

		return apply_filters( self::$prefix . '_settings_menus', $menus ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Raw default values for every setting, keyed by option ID.
	 *
	 * Deliberately contains no translation calls, so it is safe to invoke before `init`.
	 * Several display-text fields (`indicator_text`, `screen_reader_text`, `modal_title`,
	 * `modal_message`, `download_modal_title`, `download_modal_message`,
	 * `modal_continue_text`, `modal_cancel_text`, `modal_dismiss_text`,
	 * `redirect_message`) normally default to a translated string that can't be computed
	 * here, so their raw value is '' instead — every consumer already falls back to its own
	 * translated default, so this is never the value a visitor sees. Values are
	 * pre-normalised (checkboxes as 1/0) and the array is intentionally unfiltered —
	 * consumers apply the `wzlw_settings_defaults` filter themselves.
	 *
	 * @since 1.6.0
	 *
	 * @return array Raw default values keyed by option ID.
	 */
	public static function get_defaults() {
		return array(
			// General.
			'warning_method'               => 'inline_modal',
			'scope'                        => 'external',
			'enabled_post_types'           => 'post,page',

			// Display — Inline Indicators.
			'visual_indicator'             => 'icon',
			'icon_style'                   => 'arrow_ne',
			'custom_icon'                  => '',
			'icon_color'                   => '#595959',
			'icon_background'              => '',
			'indicator_text'               => '',
			'screen_reader_text'           => '',

			// Display — Modal Dialog.
			'modal_title'                  => '',
			'modal_message'                => '',
			'download_modal_title'         => '',
			'download_modal_message'       => '',
			'modal_continue_text'          => '',
			'modal_cancel_text'            => '',
			'modal_frequency'              => 'always',
			'modal_frequency_days'         => 30,
			'modal_frequency_scope'        => 'domain',
			'modal_dismiss_text'           => '',

			// Display — Redirect Screen.
			'redirect_message'             => '',
			'redirect_countdown'           => 5,

			// Advanced — Link Attributes.
			'link_attributes_external'     => array(),
			'link_attributes_affiliate'    => array(),
			'affiliate_class'              => 'wzlw-affiliate',
			'affiliate_wrapper_class'      => 'wzlw-affiliate-wrapper',

			// Advanced — Exclusions and Classes.
			'download_extensions'          => 'pdf, zip, doc, docx, xls, xlsx, exe, dmg',
			'excluded_domains'             => '',
			'no_icon_class'                => 'wzlw-no-icon',
			'no_icon_wrapper_class'        => 'wzlw-no-icon-wrapper',
			'force_external_class'         => 'wzlw-force-external',
			'force_external_wrapper_class' => 'wzlw-force-external-wrapper',
		);
	}

	/**
	 * Array containing the settings' sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings sections.
	 */
	public static function get_settings_sections() {
		$settings_sections = array(
			'general'  => esc_html__( 'General', 'webberzone-link-warnings' ),
			'display'  => esc_html__( 'Display', 'webberzone-link-warnings' ),
			'advanced' => esc_html__( 'Advanced', 'webberzone-link-warnings' ),
		);

		return apply_filters( self::$prefix . '_settings_sections', $settings_sections ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Array containing the settings' fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings fields.
	 */
	public static function get_registered_settings() {
		$settings = array(
			'general'  => self::settings_general(),
			'display'  => self::settings_display(),
			'advanced' => self::settings_advanced(),
		);

		return apply_filters( self::$prefix . '_registered_settings', $settings ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * General settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array General settings.
	 */
	public static function settings_general() {
		$defaults = self::get_defaults();
		$settings = array(
			'warning_method'     => array(
				'id'      => 'warning_method',
				'name'    => esc_html__( 'Warning Method', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Choose how to warn users about external links. Modal and redirect warnings trigger on click; inline indicators appear next to the link.', 'webberzone-link-warnings' ),
				'type'    => 'radio',
				'default' => $defaults['warning_method'],
				'options' => array(
					'inline'          => esc_html__( 'Inline indicators only', 'webberzone-link-warnings' ),
					'modal'           => esc_html__( 'Modal dialog', 'webberzone-link-warnings' ),
					'redirect'        => esc_html__( 'Redirect screen', 'webberzone-link-warnings' ),
					'inline_modal'    => esc_html__( 'Inline indicators + Modal dialog', 'webberzone-link-warnings' ),
					'inline_redirect' => esc_html__( 'Inline indicators + Redirect screen', 'webberzone-link-warnings' ),
				),
			),
			'scope'              => array(
				'id'      => 'scope',
				'name'    => esc_html__( 'Inline Indicator Scope', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Choose which links show inline indicators. Modal and redirect warnings always apply to external links only.', 'webberzone-link-warnings' ),
				'type'    => 'radio',
				'default' => $defaults['scope'],
				'options' => array(
					'external' => esc_html__( 'External links only', 'webberzone-link-warnings' ),
					'both'     => esc_html__( 'External links and internal links opening in a new tab', 'webberzone-link-warnings' ),
				),
			),
			'enabled_post_types' => array(
				'id'      => 'enabled_post_types',
				'name'    => esc_html__( 'Enabled Post Types', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Select post types where link warnings should be enabled.', 'webberzone-link-warnings' ),
				'type'    => 'posttypes',
				'default' => $defaults['enabled_post_types'],
				'options' => 'public',
			),
		);

		return apply_filters( self::$prefix . '_settings_general', $settings ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Display settings (inline indicators, modal dialog, redirect screen).
	 *
	 * @since 1.0.0
	 *
	 * @return array Display settings.
	 */
	public static function settings_display() {
		$defaults = self::get_defaults();
		$settings = array(
			// Inline Indicators section.
			'inline_header'          => array(
				'id'   => 'inline_header',
				'name' => '<h3>' . esc_html__( 'Inline Indicators', 'webberzone-link-warnings' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'visual_indicator'       => array(
				'id'      => 'visual_indicator',
				'name'    => esc_html__( 'Visual Indicator', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Choose what visual indicator to display.', 'webberzone-link-warnings' ),
				'type'    => 'radio',
				'default' => $defaults['visual_indicator'],
				'options' => array(
					'icon' => esc_html__( 'Icon (↗)', 'webberzone-link-warnings' ),
					'text' => esc_html__( 'Text', 'webberzone-link-warnings' ),
					'both' => esc_html__( 'Icon + text', 'webberzone-link-warnings' ),
					'none' => esc_html__( 'None (screen reader only)', 'webberzone-link-warnings' ),
				),
			),
			'icon_style'             => array(
				'id'      => 'icon_style',
				'name'    => esc_html__( 'Icon Style', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Choose which icon to display next to external links.', 'webberzone-link-warnings' ),
				'type'    => 'select',
				'default' => $defaults['icon_style'],
				'options' => \WebberZone\Link_Warnings\Util\Icon_Helper::get_icon_options(),
			),
			'custom_icon'            => array(
				'id'      => 'custom_icon',
				'name'    => esc_html__( 'Custom Icon', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Enter your custom icon (only used when "Custom" is selected above). You can use Unicode symbols or emojis.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => $defaults['custom_icon'],
				'size'    => 'small',
			),
			'icon_color'             => array(
				'id'      => 'icon_color',
				'name'    => esc_html__( 'Icon Color', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Choose the color for the icon.', 'webberzone-link-warnings' ),
				'type'    => 'color',
				'default' => $defaults['icon_color'],
			),
			'icon_background'        => array(
				'id'      => 'icon_background',
				'name'    => esc_html__( 'Icon Background Color', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Choose the background color for the icon. Leave empty for transparent.', 'webberzone-link-warnings' ),
				'type'    => 'color',
				'default' => $defaults['icon_background'],
			),
			'indicator_text'         => array(
				'id'      => 'indicator_text',
				'name'    => esc_html__( 'Indicator Text', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Text displayed next to links (when text indicator is enabled).', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => __( '(opens in new window)', 'webberzone-link-warnings' ),
			),
			'screen_reader_text'     => array(
				'id'      => 'screen_reader_text',
				'name'    => esc_html__( 'Screen Reader Text', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Hidden text for screen readers.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => __( 'Opens in a new window', 'webberzone-link-warnings' ),
			),

			// Modal Dialog section.
			'modal_header'           => array(
				'id'   => 'modal_header',
				'name' => '<h3>' . esc_html__( 'Modal Dialog', 'webberzone-link-warnings' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'modal_title'            => array(
				'id'      => 'modal_title',
				'name'    => esc_html__( 'Modal Title', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Title shown in the modal dialog.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => __( 'You are leaving this site', 'webberzone-link-warnings' ),
			),
			'modal_message'          => array(
				'id'      => 'modal_message',
				'name'    => esc_html__( 'Modal Message', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Message shown in the modal dialog.', 'webberzone-link-warnings' ),
				'type'    => 'textarea',
				'default' => __( 'You are about to visit an external website. Continue?', 'webberzone-link-warnings' ),
			),
			'download_modal_title'   => array(
				'id'      => 'download_modal_title',
				'name'    => esc_html__( 'Download Modal Title', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Title shown when a visitor follows a link to a configured downloadable file type.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => __( 'You are about to download a file', 'webberzone-link-warnings' ),
			),
			'download_modal_message' => array(
				'id'      => 'download_modal_message',
				'name'    => esc_html__( 'Download Modal Message', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Message shown when a visitor follows a link to a configured downloadable file type.', 'webberzone-link-warnings' ),
				'type'    => 'textarea',
				'default' => __( 'This link will download a file. Continue?', 'webberzone-link-warnings' ),
			),
			'modal_continue_text'    => array(
				'id'      => 'modal_continue_text',
				'name'    => esc_html__( 'Continue Button Text', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Text for the continue button.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => __( 'Continue', 'webberzone-link-warnings' ),
			),
			'modal_cancel_text'      => array(
				'id'      => 'modal_cancel_text',
				'name'    => esc_html__( 'Cancel Button Text', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Text for the cancel button.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => __( 'Cancel', 'webberzone-link-warnings' ),
			),
			'modal_frequency'        => array(
				'id'      => 'modal_frequency',
				'name'    => esc_html__( 'Modal Frequency', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'How often the modal is shown to the same visitor. Choosing anything other than "Always" adds a "Don\'t show again" checkbox to the modal, which is remembered in the visitor\'s browser.', 'webberzone-link-warnings' ),
				'type'    => 'select',
				'default' => $defaults['modal_frequency'],
				'options' => array(
					'always'  => esc_html__( 'Always show the modal', 'webberzone-link-warnings' ),
					'session' => esc_html__( 'Once per browser session', 'webberzone-link-warnings' ),
					'days'    => esc_html__( 'Once every N days', 'webberzone-link-warnings' ),
				),
			),
			'modal_frequency_days'   => array(
				'id'      => 'modal_frequency_days',
				'name'    => esc_html__( 'Remember Dismissal For', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Number of days a dismissal is remembered. Only used when the frequency is set to "Once every N days".', 'webberzone-link-warnings' ),
				'type'    => 'number',
				'default' => $defaults['modal_frequency_days'],
				'min'     => 1,
				'max'     => 365,
				'step'    => 1,
			),
			'modal_frequency_scope'  => array(
				'id'      => 'modal_frequency_scope',
				'name'    => esc_html__( 'Dismissal Scope', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Whether a dismissal applies only to the destination domain the visitor dismissed, or to every external link on the site.', 'webberzone-link-warnings' ),
				'type'    => 'select',
				'default' => $defaults['modal_frequency_scope'],
				'options' => array(
					'domain' => esc_html__( 'Per destination domain', 'webberzone-link-warnings' ),
					'global' => esc_html__( 'All external links', 'webberzone-link-warnings' ),
				),
			),
			'modal_dismiss_text'     => array(
				'id'      => 'modal_dismiss_text',
				'name'    => esc_html__( 'Don\'t Show Again Label', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Label for the "Don\'t show again" checkbox in the modal.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => __( 'Don\'t show this warning again', 'webberzone-link-warnings' ),
			),

			// Redirect Screen section.
			'redirect_header'        => array(
				'id'   => 'redirect_header',
				'name' => '<h3>' . esc_html__( 'Redirect Screen', 'webberzone-link-warnings' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'redirect_message'       => array(
				'id'      => 'redirect_message',
				'name'    => esc_html__( 'Redirect Message', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Message shown on the redirect page.', 'webberzone-link-warnings' ),
				'type'    => 'textarea',
				'default' => __( 'You are being redirected to an external site.', 'webberzone-link-warnings' ),
			),
			'redirect_countdown'     => array(
				'id'      => 'redirect_countdown',
				'name'    => esc_html__( 'Redirect Countdown', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Number of seconds before the automatic redirect takes place. Set to 0 to disable the timed redirect and require the user to click the Continue button on the redirect screen.', 'webberzone-link-warnings' ),
				'type'    => 'number',
				'default' => $defaults['redirect_countdown'],
				'min'     => 0,
				'max'     => 60,
				'step'    => 1,
			),
		);

		/**
		 * Filter the display settings.
		 *
		 * @since 1.0.0
		 *
		 * @param array $settings Display settings.
		 */
		return apply_filters( self::$prefix . '_settings_display', $settings ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Advanced settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Advanced settings.
	 */
	public static function settings_advanced() {
		$defaults = self::get_defaults();
		$settings = array(
			'link_attributes_header'       => array(
				'id'   => 'link_attributes_header',
				'name' => '<h3>' . esc_html__( 'Link Attributes', 'webberzone-link-warnings' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'link_attributes_external'     => array(
				'id'      => 'link_attributes_external',
				'name'    => esc_html__( 'External Links', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Attributes to add to external links. Existing rel values are preserved.', 'webberzone-link-warnings' ),
				'type'    => 'multicheck',
				'default' => $defaults['link_attributes_external'],
				'options' => self::get_link_attribute_options(),
			),
			'link_attributes_affiliate'    => array(
				'id'      => 'link_attributes_affiliate',
				'name'    => esc_html__( 'Affiliate Links', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Attributes to add to links marked with the Affiliate Link Class or inside an Affiliate Link Wrapper Class. Existing rel values are preserved.', 'webberzone-link-warnings' ),
				'type'    => 'multicheck',
				'default' => $defaults['link_attributes_affiliate'],
				'options' => self::get_link_attribute_options(),
			),
			'affiliate_class'              => array(
				'id'      => 'affiliate_class',
				'name'    => esc_html__( 'Affiliate Link Class', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'CSS class that marks a specific link as an affiliate link. Add this class directly to an &lt;a&gt; tag. Separate multiple classes with commas.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => $defaults['affiliate_class'],
				'size'    => 'large',
			),
			'affiliate_wrapper_class'      => array(
				'id'      => 'affiliate_wrapper_class',
				'name'    => esc_html__( 'Affiliate Link Wrapper Class', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'CSS class that marks every link inside a wrapper element as an affiliate link. Separate multiple classes with commas.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => $defaults['affiliate_wrapper_class'],
				'size'    => 'large',
			),
			'download_header'              => array(
				'id'   => 'download_header',
				'name' => '<h3>' . esc_html__( 'Download Links', 'webberzone-link-warnings' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'download_extensions'          => array(
				'id'      => 'download_extensions',
				'name'    => esc_html__( 'Downloadable File Extensions', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Comma-separated file extensions that should receive a download warning. Do not include the leading dot. For example: pdf, zip, docx.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => $defaults['download_extensions'],
				'size'    => 'large',
			),
			'exclusions_header'            => array(
				'id'   => 'exclusions_header',
				'name' => '<h3>' . esc_html__( 'Exclusions and Classes', 'webberzone-link-warnings' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'excluded_domains'             => array(
				'id'      => 'excluded_domains',
				'name'    => esc_html__( 'Excluded Domains', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'Enter one domain per line. These domains will be treated as internal (no warning shown). Plain entries (e.g. example.com) match that exact domain only. Use a wildcard entry (e.g. *.example.com) to also exclude subdomains. Add both to exclude everything under a domain.', 'webberzone-link-warnings' ),
				'type'    => 'textarea',
				'default' => $defaults['excluded_domains'],
			),
			'no_icon_class'                => array(
				'id'      => 'no_icon_class',
				'name'    => esc_html__( 'Suppress Icon Class', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'CSS class that suppresses the visual indicator on a specific link. Add this class directly to an &lt;a&gt; tag. Separate multiple classes with commas.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => $defaults['no_icon_class'],
				'size'    => 'large',
			),
			'no_icon_wrapper_class'        => array(
				'id'      => 'no_icon_wrapper_class',
				'name'    => esc_html__( 'Suppress Icon Wrapper Class', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'CSS class that suppresses visual indicators on all links inside a wrapper element. Add this class to any containing element. Separate multiple classes with commas.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => $defaults['no_icon_wrapper_class'],
				'size'    => 'large',
			),
			'force_external_class'         => array(
				'id'      => 'force_external_class',
				'name'    => esc_html__( 'Force External Class', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'CSS class that forces a specific link to be treated as external. Add this class directly to an &lt;a&gt; tag. Separate multiple classes with commas.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => $defaults['force_external_class'],
				'size'    => 'large',
			),
			'force_external_wrapper_class' => array(
				'id'      => 'force_external_wrapper_class',
				'name'    => esc_html__( 'Force External Wrapper Class', 'webberzone-link-warnings' ),
				'desc'    => esc_html__( 'CSS class that forces all links inside a wrapper element to be treated as external. Add this class to any containing element. Separate multiple classes with commas.', 'webberzone-link-warnings' ),
				'type'    => 'text',
				'default' => $defaults['force_external_wrapper_class'],
				'size'    => 'large',
			),
		);

		return apply_filters( self::$prefix . '_settings_advanced', $settings ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the available automatic link attributes.
	 *
	 * @since 1.5.0
	 * @return array<string, string> Link attribute options.
	 */
	private static function get_link_attribute_options() {
		return array(
			'nofollow'     => esc_html__( 'Add rel="nofollow"', 'webberzone-link-warnings' ),
			'sponsored'    => esc_html__( 'Add rel="sponsored"', 'webberzone-link-warnings' ),
			'ugc'          => esc_html__( 'Add rel="ugc"', 'webberzone-link-warnings' ),
			'target_blank' => esc_html__( 'Open in a new tab (target="_blank")', 'webberzone-link-warnings' ),
			'noopener'     => esc_html__( 'Add rel="noopener" for new-tab links', 'webberzone-link-warnings' ),
			'noreferrer'   => esc_html__( 'Add rel="noreferrer" for new-tab links. This also stops the referrer being sent, which can break referrer-based affiliate attribution.', 'webberzone-link-warnings' ),
		);
	}

	/**
	 * Modify settings on save.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings Settings array.
	 * @return array Modified settings array.
	 */
	public function change_settings_on_save( $settings ) {
		// Flush rewrite rules so the redirect endpoint is registered.
		flush_rewrite_rules();

		return $settings;
	}

	/**
	 * Get the help sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @return string Help sidebar content.
	 */
	public function get_help_sidebar() {
		$help_sidebar =
			'<p><strong>' . esc_html__( 'For more information:', 'webberzone-link-warnings' ) . '</strong></p>' .
			'<p><a href="https://webberzone.com/plugins/webberzone-link-warnings/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Plugin Homepage', 'webberzone-link-warnings' ) . '</a></p>' .
			'<p><a href="https://webberzone.com/support/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support', 'webberzone-link-warnings' ) . '</a></p>';

		return apply_filters( self::$prefix . '_settings_help_sidebar', $help_sidebar ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the help tabs.
	 *
	 * @since 1.0.0
	 *
	 * @return array Help tabs.
	 */
	public function get_help_tabs() {
		$help_tabs = array(
			array(
				'id'      => 'wzlw-settings-general',
				'title'   => esc_html__( 'General', 'webberzone-link-warnings' ),
				'content' =>
					'<p>' . esc_html__( 'Configure the general behavior of the plugin.', 'webberzone-link-warnings' ) . '</p>' .
					'<p>' . esc_html__( 'Choose your preferred warning method and which links should be processed.', 'webberzone-link-warnings' ) . '</p>',
			),
		);

		return apply_filters( self::$prefix . '_settings_help_tabs', $help_tabs ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Get the admin footer text.
	 *
	 * @since 1.0.0
	 *
	 * @return string Admin footer text.
	 */
	public function get_admin_footer_text() {
		$footer_text = sprintf(
			/* translators: 1: WebberZone Link Warnings link, 2: Plugin rating link */
			__( 'Thank you for using <a href="%1$s" target="_blank" rel="noopener noreferrer">WebberZone Link Warnings</a>! Please <a href="%2$s" target="_blank" rel="noopener noreferrer">rate us</a> on WordPress.org', 'webberzone-link-warnings' ),
			'https://webberzone.com/plugins/webberzone-link-warnings/',
			'https://wordpress.org/support/plugin/webberzone-link-warnings/reviews/#new-post'
		);

		return $footer_text;
	}

	/**
	 * Add plugin action links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Array of links.
	 * @return array Modified array of links.
	 */
	public function plugin_actions_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->menu_slug ) . '">' . esc_html__( 'Settings', 'webberzone-link-warnings' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Add plugin row meta.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $links Array of links.
	 * @param string $file  Plugin file.
	 * @return array Modified array of links.
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( false !== strpos( $file, 'webberzone-link-warnings.php' ) ) {
			$new_links = array(
				'support' => '<a href="https://webberzone.com/support/" target="_blank">' . esc_html__( 'Support', 'webberzone-link-warnings' ) . '</a>',
				'donate'  => '<a href="https://webberzone.com/donate/" target="_blank">' . esc_html__( 'Donate', 'webberzone-link-warnings' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}
}
