<?php
/**
 * Icon Helper class.
 *
 * Provides centralized icon management and options.
 *
 * @package WebberZone\Link_Warnings
 * @since 1.0.0
 */

namespace WebberZone\Link_Warnings\Util;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Icon Helper class.
 *
 * @since 1.0.0
 */
class Icon_Helper {

	/**
	 * Get available icon options.
	 *
	 * @since 1.0.0
	 * @return array Icon options with key => label pairs.
	 */
	public static function get_icon_options() {
		$options = array(
			'arrow_ne'    => __( 'Arrow (North-East) ↗', 'webberzone-link-warnings' ),
			'arrow_right' => __( 'Arrow (Right) →', 'webberzone-link-warnings' ),
			'arrow_up'    => __( 'Arrow (Up-Right) ⬈', 'webberzone-link-warnings' ),
			'external'    => __( 'External Link Symbol ⧉', 'webberzone-link-warnings' ),
			'link_emoji'  => __( 'Link Emoji 🔗', 'webberzone-link-warnings' ),
			'globe_emoji' => __( 'Globe Emoji 🌐', 'webberzone-link-warnings' ),
			'asterisk'    => __( 'Asterisk *', 'webberzone-link-warnings' ),
			'custom'      => __( 'Custom (enter below)', 'webberzone-link-warnings' ),
		);

		/**
		 * Filter icon options.
		 *
		 * @since 1.0.0
		 *
		 * @param array $options Icon options.
		 */
		return apply_filters( 'wzlw_icon_options', $options );
	}

	/**
	 * Get icon map (icon keys to actual icon values).
	 *
	 * @since 1.0.0
	 * @return array Icon map.
	 */
	public static function get_icon_map() {
		$map = array(
			'arrow_ne'    => '↗',
			'arrow_right' => '→',
			'arrow_up'    => '⬈',
			'external'    => '⧉',
			'link_emoji'  => '🔗',
			'globe_emoji' => '🌐',
			'asterisk'    => '*',
			'download'    => '⇩',
		);

		/**
		 * Filter icon map.
		 *
		 * @since 1.0.0
		 *
		 * @param array $map Icon map.
		 */
		return apply_filters( 'wzlw_icon_map', $map );
	}

	/**
	 * Get icon for display.
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon_style Icon style key.
	 * @param string $custom_icon Custom icon value.
	 * @return string Icon text.
	 */
	public static function get_icon( $icon_style, $custom_icon = '' ) {
		$icon_map = self::get_icon_map();

		// Handle custom icons.
		if ( 'custom' === $icon_style && ! empty( $custom_icon ) ) {
			return $custom_icon;
		}

		// Return preset icon or default.
		return $icon_map[ $icon_style ] ?? '↗';
	}

	/**
	 * Get the icon used for downloadable file links.
	 *
	 * @since 1.6.0
	 * @return string Download icon text.
	 */
	public static function get_download_icon() {
		return self::get_icon( 'download' );
	}
}
