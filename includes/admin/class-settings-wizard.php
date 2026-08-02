<?php
/**
 * Settings Wizard for WebberZone Link Warnings.
 *
 * Provides a guided setup experience for new users.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Link_Warnings
 */

namespace WebberZone\Link_Warnings\Admin;

use WebberZone\Link_Warnings\Util\Hook_Registry;
use WebberZone\Link_Warnings\Admin\Settings\Settings_Wizard_API;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings Wizard class for WebberZone Link Warnings.
 *
 * @since 1.0.0
 */
class Settings_Wizard extends Settings_Wizard_API {

	/**
	 * Settings page URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $settings_page_url;

	/**
	 * Main constructor class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$settings_key = 'wzlw_settings';
		$prefix       = 'wzlw';

		$this->settings_page_url = admin_url( 'options-general.php?page=wzlw-settings' );

		$args = array(
			'steps'               => $this->get_wizard_steps(),
			'translation_strings' => $this->get_translation_strings(),
			'page_slug'           => 'wzlw_wizard',
			'hide_when_completed' => true,
			'show_in_menu'        => false,
			'menu_args'           => array(
				'parent'     => 'options-general.php',
				'capability' => 'manage_options',
			),
		);

		parent::__construct( $settings_key, $prefix, $args );

		$this->additional_hooks();
	}

	/**
	 * Additional hooks specific to WebberZone Link Warnings.
	 *
	 * @since 1.0.0
	 */
	protected function additional_hooks() {
		Hook_Registry::add_action( 'wzlw_activate', array( $this, 'trigger_wizard_on_activation' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'register_wizard_notice' ) );
	}

	/**
	 * Get the skip wizard link URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string Skip wizard link URL.
	 */
	protected function get_skip_link_url() {
		return $this->settings_page_url;
	}

	/**
	 * Get wizard steps configuration.
	 *
	 * @since 1.0.0
	 *
	 * @return array Wizard steps.
	 */
	public function get_wizard_steps() {
		$all_settings_grouped = Settings::get_registered_settings();
		$all_settings         = array();
		foreach ( $all_settings_grouped as $section_settings ) {
			$all_settings = array_merge( $all_settings, $section_settings );
		}

		$general_keys = array(
			'warning_method',
			'scope',
			'enabled_post_types',
		);

		$modal_keys = array(
			'modal_title',
			'modal_message',
			'modal_continue_text',
			'modal_cancel_text',
		);

		$content_keys = array(
			'visual_indicator',
			'indicator_text',
			'screen_reader_text',
		);

		$redirect_keys = array(
			'redirect_message',
			'redirect_countdown',
		);

		$steps = array(
			'welcome'  => array(
				'title'       => __( 'Welcome to WebberZone Link Warnings', 'webberzone-link-warnings' ),
				'description' => __( 'Thank you for installing WebberZone Link Warnings! This wizard will help you configure the essential settings to make your external links accessible and user-friendly.', 'webberzone-link-warnings' ),
				'settings'    => array(),
			),
			'general'  => array(
				'title'       => __( 'General Settings', 'webberzone-link-warnings' ),
				'description' => __( 'Choose how to warn users about external links and which links should be processed.', 'webberzone-link-warnings' ),
				'settings'    => $this->build_step_settings( $general_keys, $all_settings ),
			),
			'content'  => array(
				'title'       => __( 'Visual Indicators', 'webberzone-link-warnings' ),
				'description' => __( 'Configure visual indicators and screen reader text for accessibility.', 'webberzone-link-warnings' ),
				'settings'    => $this->build_step_settings( $content_keys, $all_settings ),
			),
			'modal'    => array(
				'title'       => __( 'Modal Dialog', 'webberzone-link-warnings' ),
				'description' => __( 'Customize the modal dialog text and buttons.', 'webberzone-link-warnings' ),
				'settings'    => $this->build_step_settings( $modal_keys, $all_settings ),
			),
			'redirect' => array(
				'title'       => __( 'Redirect Screen', 'webberzone-link-warnings' ),
				'description' => __( 'Configure the redirect screen message.', 'webberzone-link-warnings' ),
				'settings'    => $this->build_step_settings( $redirect_keys, $all_settings ),
			),
		);

		/**
		 * Filter wizard steps.
		 *
		 * @param array $steps Wizard steps.
		 */
		return apply_filters( 'wzlw_wizard_steps', $steps ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	}

	/**
	 * Build settings array for a wizard step from keys.
	 *
	 * @since 1.0.0
	 *
	 * @param array $keys Setting keys for this step.
	 * @param array $all_settings All settings array.
	 * @return array
	 */
	protected function build_step_settings( $keys, $all_settings ) {
		$step_settings = array();

		foreach ( $keys as $key ) {
			if ( isset( $all_settings[ $key ] ) ) {
				$step_settings[ $key ] = $all_settings[ $key ];
			}
		}

		return $step_settings;
	}

	/**
	 * Get translation strings for the wizard.
	 *
	 * @since 1.0.0
	 *
	 * @return array Translation strings.
	 */
	public function get_translation_strings() {
		return array(
			'page_title'      => __( 'WebberZone Link Warnings Setup Wizard', 'webberzone-link-warnings' ),
			'menu_title'      => __( 'Setup Wizard', 'webberzone-link-warnings' ),
			'next_step'       => __( 'Next Step', 'webberzone-link-warnings' ),
			'previous_step'   => __( 'Previous Step', 'webberzone-link-warnings' ),
			'finish_setup'    => __( 'Finish Setup', 'webberzone-link-warnings' ),
			'skip_wizard'     => __( 'Skip Wizard', 'webberzone-link-warnings' ),
			/* translators: %1$d: Current step number, %2$d: Total number of steps */
			'step_of'         => __( 'Step %1$d of %2$d', 'webberzone-link-warnings' ),
			'wizard_complete' => __( 'Setup Complete!', 'webberzone-link-warnings' ),
			'setup_complete'  => __( 'Your WebberZone Link Warnings plugin has been configured successfully. Your external links are now accessible and user-friendly!', 'webberzone-link-warnings' ),
			'go_to_settings'  => __( 'Go to Settings', 'webberzone-link-warnings' ),
		);
	}

	/**
	 * Trigger wizard on plugin activation.
	 *
	 * @since 1.0.0
	 */
	public function trigger_wizard_on_activation() {
		// Set a transient that will trigger the wizard on first admin page visit.
		set_transient( 'wzlw_show_wizard_activation_redirect', true, HOUR_IN_SECONDS );

		// Also set an option for more persistent storage in multisite environments.
		update_option( 'wzlw_show_wizard', true );
	}

	/**
	 * Register the wizard notice.
	 *
	 * @since 1.0.0
	 */
	public function register_wizard_notice() {
		// Check if wizard should be shown.
		$show_wizard = get_transient( 'wzlw_show_wizard_activation_redirect' ) || get_option( 'wzlw_show_wizard', false );

		if ( ! $show_wizard || $this->is_wizard_completed() ) {
			return;
		}

		// Check if we're already on the wizard page.
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'wzlw_wizard' === $page ) {
			return;
		}

		// Add admin notice.
		Hook_Registry::add_action(
			'admin_notices',
			function () {
				?>
				<div class="notice notice-info is-dismissible">
					<p><?php esc_html_e( 'Welcome to WebberZone Link Warnings! Would you like to run the setup wizard to configure the plugin?', 'webberzone-link-warnings' ); ?></p>
					<p>
						<a href="<?php echo esc_url( admin_url( 'options-general.php?page=wzlw_wizard' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Run Setup Wizard', 'webberzone-link-warnings' ); ?></a>
						<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wzlw_dismiss_wizard', '1' ), 'wzlw_dismiss_wizard' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Skip Setup', 'webberzone-link-warnings' ); ?></a>
					</p>
				</div>
				<?php
			}
		);

		// Handle dismissal.
		if ( isset( $_GET['wzlw_dismiss_wizard'] ) && check_admin_referer( 'wzlw_dismiss_wizard' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			delete_transient( 'wzlw_show_wizard_activation_redirect' );
			delete_option( 'wzlw_show_wizard' );
			wp_safe_redirect( remove_query_arg( array( 'wzlw_dismiss_wizard', '_wpnonce' ) ) );
			exit;
		}
	}

	/**
	 * Get the URL to redirect to after wizard completion.
	 *
	 * @since 1.0.0
	 *
	 * @return string Redirect URL.
	 */
	protected function get_completion_redirect_url() {
		return $this->settings_page_url;
	}

	/**
	 * Get the version for cache busting.
	 *
	 * @since 1.4.1
	 *
	 * @return string Version number.
	 */
	protected function get_version() {
		return WZLW_VERSION;
	}
}
