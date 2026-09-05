<?php
/**
 * Frontend Handler class.
 *
 * Handles frontend JavaScript and modal functionality.
 *
 * @package WebberZone\Link_Warnings
 * @since 1.0.0
 */

namespace WebberZone\Link_Warnings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WebberZone\Link_Warnings\Util\Hook_Registry;

/**
 * Frontend Handler class.
 *
 * @since 1.0.0
 */
class Frontend_Handler {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		Hook_Registry::add_action( 'wp_footer', array( $this, 'render_modal' ) );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		$settings = wzlw_get_settings();
		$method   = isset( $settings['warning_method'] ) ? $settings['warning_method'] : 'inline_modal';

		$rtl_suffix = is_rtl() ? '-rtl' : '';
		$min_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// Enqueue styles for all methods.
		wp_enqueue_style(
			'wzlw-frontend',
			WZLW_PLUGIN_URL . 'includes/assets/css/frontend' . $rtl_suffix . $min_suffix . '.css',
			array(),
			WZLW_VERSION
		);

		// Add inline CSS with icon variable.
		$this->add_icon_inline_style();

		// Always enqueue JS — needed for DOM scanning of non-post-content links.
		wp_enqueue_script(
			'wzlw-modal',
			WZLW_PLUGIN_URL . 'includes/admin/js/modal' . $min_suffix . '.js',
			array(),
			WZLW_VERSION,
			true
		);

		$site_url_parts = wp_parse_url( home_url() );
		$site_host      = strtolower( rtrim( (string) ( $site_url_parts['host'] ?? '' ), '.' ) );

		$excluded_domains_raw = $settings['excluded_domains'] ?? '';
		if ( is_string( $excluded_domains_raw ) ) {
			$excluded_domains_raw = array_filter( array_map( 'trim', explode( "\n", $excluded_domains_raw ) ) );
		}
		$excluded_domains_js = array_values(
			array_filter(
				array_map(
					function ( $domain ) {
						$parsed = wp_parse_url( $domain );
						if ( ! empty( $parsed['host'] ) ) {
							return strtolower( $parsed['host'] );
						}
						return strtolower( strtok( rtrim( $domain, '/' ), '/' ) );
					},
					$excluded_domains_raw
				)
			)
		);

		$download_extensions_raw = $settings['download_extensions'] ?? 'pdf, zip, doc, docx, xls, xlsx, exe, dmg';
		$download_extensions     = is_string( $download_extensions_raw ) ? preg_split( '/[\s,]+/', strtolower( $download_extensions_raw ), -1, PREG_SPLIT_NO_EMPTY ) : array();
		$download_extensions     = is_array( $download_extensions ) ? array_values(
			array_unique(
				array_filter(
					array_map(
						static function ( $extension ) {
							return ltrim( trim( $extension ), '.' );
						},
						$download_extensions
					),
					static function ( $extension ) {
						return (bool) preg_match( '/^[a-z0-9]+$/', $extension );
					}
				)
			)
		) : array();

		wp_localize_script(
			'wzlw-modal',
			'wzlwSettings',
			array(
				'siteHost'                  => $site_host,
				'excludedDomains'           => $excluded_domains_js,
				'downloadExtensions'        => $download_extensions,
				'scope'                     => $settings['scope'] ?? 'external',
				'warningMethod'             => $method,
				'noIconClass'               => array_values( array_filter( array_map( 'trim', explode( ',', isset( $settings['no_icon_class'] ) ? $settings['no_icon_class'] : 'wzlw-no-icon' ) ) ) ),
				'noIconWrapperClass'        => array_values( array_filter( array_map( 'trim', explode( ',', isset( $settings['no_icon_wrapper_class'] ) ? $settings['no_icon_wrapper_class'] : 'wzlw-no-icon-wrapper' ) ) ) ),
				'forceExternalClass'        => array_values( array_filter( array_map( 'trim', explode( ',', isset( $settings['force_external_class'] ) ? $settings['force_external_class'] : 'wzlw-force-external' ) ) ) ),
				'forceExternalWrapperClass' => array_values( array_filter( array_map( 'trim', explode( ',', isset( $settings['force_external_wrapper_class'] ) ? $settings['force_external_wrapper_class'] : 'wzlw-force-external-wrapper' ) ) ) ),
				'affiliateClass'            => array_values( array_filter( array_map( 'trim', explode( ',', isset( $settings['affiliate_class'] ) ? $settings['affiliate_class'] : 'wzlw-affiliate' ) ) ) ),
				'affiliateWrapperClass'     => array_values( array_filter( array_map( 'trim', explode( ',', isset( $settings['affiliate_wrapper_class'] ) ? $settings['affiliate_wrapper_class'] : 'wzlw-affiliate-wrapper' ) ) ) ),
				'linkAttributesExternal'    => wp_parse_list( $settings['link_attributes_external'] ?? array() ),
				'linkAttributesAffiliate'   => wp_parse_list( $settings['link_attributes_affiliate'] ?? array() ),
				'visualIndicator'           => $settings['visual_indicator'] ?? 'icon',
				'indicatorText'             => $settings['indicator_text'] ?? __( '(opens in new window)', 'webberzone-link-warnings' ),
				'screenReaderText'          => $settings['screen_reader_text'] ?? __( 'Opens in a new window', 'webberzone-link-warnings' ),
				'modalTitle'                => $settings['modal_title'] ?? __( 'You are leaving this site', 'webberzone-link-warnings' ),
				'modalMessage'              => $settings['modal_message'] ?? __( 'You are about to visit an external website. Continue?', 'webberzone-link-warnings' ),
				'downloadModalTitle'        => $settings['download_modal_title'] ?? __( 'You are about to download a file', 'webberzone-link-warnings' ),
				'downloadModalMessage'      => $settings['download_modal_message'] ?? __( 'This link will download a file. Continue?', 'webberzone-link-warnings' ),
				'continueText'              => $settings['modal_continue_text'] ?? __( 'Continue', 'webberzone-link-warnings' ),
				'cancelText'                => $settings['modal_cancel_text'] ?? __( 'Cancel', 'webberzone-link-warnings' ),
				'modalFrequency'            => $settings['modal_frequency'] ?? 'always',
				'modalFrequencyDays'        => max( 1, (int) ( $settings['modal_frequency_days'] ?? 30 ) ),
				'modalFrequencyScope'       => $settings['modal_frequency_scope'] ?? 'domain',
				'redirectBaseUrl'           => home_url( 'external-redirect/' ),
				'ajaxUrl'                   => admin_url( 'admin-ajax.php' ),
				'nonce'                     => wp_create_nonce( 'wzlw_sign_urls' ),
			)
		);
	}

	/**
	 * Render modal HTML.
	 *
	 * @since 1.0.0
	 */
	public function render_modal() {
		$settings = wzlw_get_settings();
		$method   = isset( $settings['warning_method'] ) ? $settings['warning_method'] : 'inline_modal';

		if ( ! in_array( $method, array( 'modal', 'inline_modal' ), true ) ) {
			return;
		}

		$show_dismiss = 'always' !== ( $settings['modal_frequency'] ?? 'always' );
		$dismiss_text = $settings['modal_dismiss_text'] ?? __( 'Don\'t show this warning again', 'webberzone-link-warnings' );

		?>
		<div id="wzlw-modal" class="wzlw-modal" role="dialog" aria-modal="true" aria-labelledby="wzlw-modal-title" aria-describedby="wzlw-modal-message" hidden>
			<div class="wzlw-modal-overlay" data-wzlw-close role="presentation"></div>
			<div class="wzlw-modal-container">
				<button type="button" class="wzlw-modal-close-btn" data-wzlw-close aria-label="<?php esc_attr_e( 'Close dialog', 'webberzone-link-warnings' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
				<div class="wzlw-modal-content">
					<h2 id="wzlw-modal-title" class="wzlw-modal-title"></h2>
					<div id="wzlw-modal-message" class="wzlw-modal-message"></div>
					<div class="wzlw-modal-url"><span class="screen-reader-text"><?php esc_html_e( 'External URL:', 'webberzone-link-warnings' ); ?></span><span class="wzlw-modal-url-value"></span></div>
					<?php if ( $show_dismiss && '' !== $dismiss_text ) : ?>
						<div class="wzlw-modal-dismiss">
							<input type="checkbox" id="wzlw-modal-dismiss" class="wzlw-modal-dismiss-input" data-wzlw-dismiss>
							<label for="wzlw-modal-dismiss" class="wzlw-modal-dismiss-label"><?php echo esc_html( $dismiss_text ); ?></label>
						</div>
					<?php endif; ?>
					<div class="wzlw-modal-actions">
						<button type="button" class="wzlw-modal-button wzlw-modal-cancel" data-wzlw-close><?php esc_html_e( 'Cancel', 'webberzone-link-warnings' ); ?></button>
						<button type="button" class="wzlw-modal-button wzlw-modal-continue" data-wzlw-continue><?php esc_html_e( 'Continue', 'webberzone-link-warnings' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Add inline CSS with icon variable.
	 *
	 * @since 1.0.0
	 */
	private function add_icon_inline_style() {
		$settings    = wzlw_get_settings();
		$icon_style  = $settings['icon_style'] ?? 'arrow_ne';
		$custom_icon = $settings['custom_icon'] ?? '';
		$icon_color  = $settings['icon_color'] ?? '#595959';
		$icon_bg     = $settings['icon_background'] ?? '';

		$icon          = \WebberZone\Link_Warnings\Util\Icon_Helper::get_icon( $icon_style, $custom_icon );
		$download_icon = \WebberZone\Link_Warnings\Util\Icon_Helper::get_download_icon();

		// Escape for CSS content property.
		$icon_escaped          = addcslashes( $icon, '"\\' );
		$download_icon_escaped = addcslashes( $download_icon, '"\\' );

		// Build inline CSS with variables.
		// Sanitize colors and only add if valid.
		$sanitized_icon_color = sanitize_hex_color( $icon_color );
		$sanitized_icon_bg    = sanitize_hex_color( $icon_bg );

		// Build inline CSS with variables.
		$inline_css  = ':root {';
		$inline_css .= ' --wzlw-icon-content: "' . $icon_escaped . '";';
		$inline_css .= ' --wzlw-download-icon-content: "' . $download_icon_escaped . '";';

		if ( $sanitized_icon_color ) {
			$inline_css .= ' --wzlw-icon-color: ' . $sanitized_icon_color . ';';
		}

		if ( $sanitized_icon_bg ) {
			$inline_css .= ' --wzlw-icon-background: ' . $sanitized_icon_bg . ';';
		}

		$inline_css .= ' }';

		wp_add_inline_style( 'wzlw-frontend', $inline_css );
	}
}
