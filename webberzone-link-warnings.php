<?php
/**
 * WebberZone Link Warnings.
 *
 * Enhances accessibility by warning users when links open in new windows or navigate to external sites.
 *
 * @package   WebberZone\Link_Warnings
 * @author    Ajay D'Souza
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2026 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: WebberZone Link Warnings
 * Plugin URI: https://webberzone.com/plugins/webberzone-link-warnings/
 * Description: Enhances accessibility by warning users when links open in new windows or navigate to external sites.
 * Version: 1.5.0
 * Author: WebberZone
 * Author URI: https://webberzone.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: webberzone-link-warnings
 * Domain Path: /languages
 */

namespace WebberZone\Link_Warnings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WZLW_VERSION', '1.5.0' );
define( 'WZLW_PLUGIN_FILE', __FILE__ );
define( 'WZLW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WZLW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WZLW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load the autoloader.
require_once WZLW_PLUGIN_DIR . 'includes/autoloader.php';

/**
 * Main plugin instance.
 *
 * @since 1.0.0
 * @return \WebberZone\Link_Warnings\Main
 */
function wzlw() {
	return \WebberZone\Link_Warnings\Main::get_instance();
}

// Initialize the plugin.
wzlw();

// Register the activation hook.
register_activation_hook( WZLW_PLUGIN_FILE, __NAMESPACE__ . '\Admin\Activator::activate' );

// Register the deactivation hook.
register_deactivation_hook( WZLW_PLUGIN_FILE, __NAMESPACE__ . '\Admin\Deactivator::deactivate' );
