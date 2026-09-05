<?php
/**
 * Content Processor class.
 *
 * Processes content to add accessibility features to links.
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
use WebberZone\Link_Warnings\Util\Icon_Helper;

/**
 * Content Processor class.
 *
 * @since 1.0.0
 */
class Content_Processor {

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $settings;

	/**
	 * Current site hostname.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $site_host;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->site_host = strtolower( rtrim( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.' ) );

		Hook_Registry::add_filter( 'the_content', array( $this, 'process_content' ), 999 );
		Hook_Registry::add_filter( 'the_excerpt', array( $this, 'process_content' ), 999 );
	}

	/**
	 * Process content to add accessibility features.
	 *
	 * @since 1.0.0
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public function process_content( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		// Check if current post type is enabled.
		if ( ! $this->is_post_type_enabled() ) {
			return $content;
		}

		// Load fresh settings on each content processing.
		$this->settings = wzlw_get_settings();

		// Use WP_HTML_Tag_Processor to parse links.
		$processor            = new \WP_HTML_Tag_Processor( $content );
		$skip_depth           = 0;
		$force_external_depth = 0;
		$affiliate_depth      = 0;

		while ( $processor->next_tag( array( 'tag_closers' => 'visit' ) ) ) {
			if ( $skip_depth > 0 ) {
				$skip_depth += $this->get_skip_depth_delta( $processor );

				if ( 0 >= $skip_depth ) {
					$skip_depth = 0;
				}
			} elseif ( $this->is_skip_wrapper_tag( $processor ) ) {
				if ( 1 === $this->get_skip_depth_delta( $processor ) ) {
					$skip_depth = 1;
				}
			}

			if ( $force_external_depth > 0 ) {
				$force_external_depth += $this->get_skip_depth_delta( $processor );

				if ( 0 >= $force_external_depth ) {
					$force_external_depth = 0;
				}
			} elseif ( $this->is_force_external_wrapper_tag( $processor ) ) {
				if ( 1 === $this->get_skip_depth_delta( $processor ) ) {
					$force_external_depth = 1;
				}
			}

			if ( $affiliate_depth > 0 ) {
				$affiliate_depth += $this->get_skip_depth_delta( $processor );

				if ( 0 >= $affiliate_depth ) {
					$affiliate_depth = 0;
				}
			} elseif ( $this->is_affiliate_wrapper_tag( $processor ) ) {
				if ( 1 === $this->get_skip_depth_delta( $processor ) ) {
					$affiliate_depth = 1;
				}
			}

			if ( 'A' !== $processor->get_tag() ) {
				continue;
			}

			// Skip closing </a> tags.
			if ( $processor->is_tag_closer() ) {
				continue;
			}

			$href = $processor->get_attribute( 'href' );

			// Skip if no href.
			if ( empty( $href ) ) {
				continue;
			}

			// Determine if link should be processed.
			$is_affiliate = $affiliate_depth > 0 || $this->link_has_affiliate_class( $processor );
			$is_forced    = $is_affiliate || $force_external_depth > 0 || $this->link_has_force_external_class( $processor );
			$is_excluded  = ! $is_forced && $this->is_excluded_domain( $href );
			$is_external  = $is_forced || ( ! $is_excluded && $this->is_external_link( $href ) );

			if ( $is_excluded ) {
				$processor->set_attribute( 'data-wzlw-excluded', 'true' );
				foreach ( array( 'data-wzlw-url', 'data-wzlw-external', 'data-wzlw-blank', 'data-wzlw-redirect-url' ) as $attribute ) {
					$processor->remove_attribute( $attribute );
				}
			} else {
				$processor->remove_attribute( 'data-wzlw-excluded' );
			}
			$this->apply_link_attributes( $processor, $is_external, $is_affiliate );
			$has_target     = '_blank' === $processor->get_attribute( 'target' );
			$should_process = $this->should_process_link( $is_external, $has_target );

			// Inside a skip wrapper, target="_blank" links still need ARIA for accessibility
			// even when the visual icon/modal is suppressed.
			if ( ! $should_process ) {
				if ( $skip_depth > 0 && $has_target ) {
					$aria_label = $this->get_aria_label( $processor->get_attribute( 'aria-label' ) );
					if ( $aria_label ) {
						$processor->set_attribute( 'aria-label', $aria_label );
					}
				}
				continue;
			}

			// Excluded-domain links with target=_blank (scope=both): ARIA only, no icon, no modal.
			if ( $is_excluded && $has_target ) {
				$aria_label = $this->get_aria_label( $processor->get_attribute( 'aria-label' ) );
				if ( $aria_label ) {
					$processor->set_attribute( 'aria-label', $aria_label );
				}
				continue;
			}

			// Add data attributes for JavaScript handling.
			$warning_method = $this->settings['warning_method'] ?? 'none';
			if ( in_array( $warning_method, array( 'modal', 'inline_modal', 'redirect', 'inline_redirect' ), true ) ) {
				$processor->set_attribute( 'data-wzlw-url', $href );
				if ( $is_external ) {
					$processor->set_attribute( 'data-wzlw-external', 'true' );
				} else {
					$processor->set_attribute( 'data-wzlw-blank', 'true' );
				}
				if ( in_array( $warning_method, array( 'redirect', 'inline_redirect' ), true ) ) {
					$processor->set_attribute( 'data-wzlw-redirect-url', Redirect_Handler::get_redirect_url( $href ) );
				}
			}

			// Add ARIA attributes for accessibility.
			$aria_label = $this->get_aria_label( $processor->get_attribute( 'aria-label' ) );
			if ( $aria_label ) {
				$processor->set_attribute( 'aria-label', $aria_label );
			}

			// Add class for styling.
			$existing_class = $processor->get_attribute( 'class' );
			$new_class      = trim( $existing_class . ' wzlw-processed' );
			if ( $is_external ) {
				$new_class .= ' wzlw-external';
			}
			if ( $skip_depth > 0 ) {
				$no_icon_classes = $this->parse_class_setting( 'no_icon_class', 'wzlw-no-icon' );
				if ( ! empty( $no_icon_classes ) ) {
					$new_class .= ' ' . implode( ' ', $no_icon_classes );
				}
			}
			$processor->set_attribute( 'class', $new_class );
		}

		$processed_content = $processor->get_updated_html();

		// Add visual indicators if inline method is used.
		if ( in_array( $this->settings['warning_method'] ?? 'none', array( 'inline', 'inline_modal', 'inline_redirect' ), true ) ) {
			$processed_content = $this->add_visual_indicators( $processed_content );
		}

		return $processed_content;
	}

	/**
	 * Add visual indicators to processed links.
	 *
	 * @since 1.0.0
	 * @param string $content Processed content.
	 * @return string Content with visual indicators.
	 */
	private function add_visual_indicators( $content ) {
		// Use regex to find processed links and add indicators before closing tag.
		$pattern = '/<a\s+[^>]*class="[^"]*wzlw-processed[^"]*"[^>]*>(.*?)<\/a>/is';

		$content = preg_replace_callback(
			$pattern,
			array( $this, 'add_indicator_to_link' ),
			$content
		);

		return $content;
	}

	/**
	 * Check if the current tag starts a wrapper that should skip processing.
	 *
	 * @since 1.1.0
	 * @param \WP_HTML_Tag_Processor $processor HTML tag processor instance.
	 * @return bool True if the tag is a skip wrapper.
	 */
	private function is_skip_wrapper_tag( \WP_HTML_Tag_Processor $processor ) {
		if ( $processor->is_tag_closer() ) {
			return false;
		}

		$class_name = $processor->get_attribute( 'class' );

		if ( ! is_string( $class_name ) || '' === $class_name ) {
			return false;
		}

		return $this->has_skip_wrapper_class( $class_name );
	}

	/**
	 * Check if a class attribute contains the skip wrapper class.
	 *
	 * @since 1.1.0
	 * @param string $class_name Class attribute value.
	 * @return bool True if the class is present.
	 */
	private function has_skip_wrapper_class( $class_name ) {
		$wrapper_classes = $this->parse_class_setting( 'no_icon_wrapper_class', 'wzlw-no-icon-wrapper' );

		return $this->class_attribute_has_any( $class_name, $wrapper_classes );
	}

	/**
	 * Check if the current tag starts a wrapper that should force links to be treated as external.
	 *
	 * @since 1.2.0
	 * @param \WP_HTML_Tag_Processor $processor HTML tag processor instance.
	 * @return bool True if the tag is a force-external wrapper.
	 */
	private function is_force_external_wrapper_tag( \WP_HTML_Tag_Processor $processor ) {
		if ( $processor->is_tag_closer() ) {
			return false;
		}

		$class_name = $processor->get_attribute( 'class' );

		if ( ! is_string( $class_name ) || '' === $class_name ) {
			return false;
		}

		return $this->has_force_external_wrapper_class( $class_name );
	}

	/**
	 * Check if a class attribute contains the force-external wrapper class.
	 *
	 * @since 1.2.0
	 * @param string $class_name Class attribute value.
	 * @return bool True if the class is present.
	 */
	private function has_force_external_wrapper_class( $class_name ) {
		$wrapper_classes = $this->parse_class_setting( 'force_external_wrapper_class', 'wzlw-force-external-wrapper' );

		return $this->class_attribute_has_any( $class_name, $wrapper_classes );
	}

	/**
	 * Check if the current tag starts an affiliate wrapper.
	 *
	 * @since 1.5.0
	 * @param \WP_HTML_Tag_Processor $processor HTML tag processor instance.
	 * @return bool True if the tag is an affiliate wrapper.
	 */
	private function is_affiliate_wrapper_tag( \WP_HTML_Tag_Processor $processor ) {
		if ( $processor->is_tag_closer() ) {
			return false;
		}

		$class_name = $processor->get_attribute( 'class' );

		return is_string( $class_name ) && '' !== $class_name && $this->has_affiliate_wrapper_class( $class_name );
	}

	/**
	 * Check if a class attribute contains the affiliate wrapper class.
	 *
	 * @since 1.5.0
	 * @param string $class_name Class attribute value.
	 * @return bool True if the class is present.
	 */
	private function has_affiliate_wrapper_class( $class_name ) {
		$wrapper_classes = $this->parse_class_setting( 'affiliate_wrapper_class', 'wzlw-affiliate-wrapper' );

		return $this->class_attribute_has_any( $class_name, $wrapper_classes );
	}

	/**
	 * Check if an <a> tag has the force-external class directly applied.
	 *
	 * @since 1.2.0
	 * @param \WP_HTML_Tag_Processor $processor HTML tag processor instance.
	 * @return bool True if the class is present.
	 */
	private function link_has_force_external_class( \WP_HTML_Tag_Processor $processor ) {
		$force_classes = $this->parse_class_setting( 'force_external_class', 'wzlw-force-external' );

		if ( empty( $force_classes ) ) {
			return false;
		}

		return $this->class_attribute_has_any( $processor->get_attribute( 'class' ), $force_classes );
	}

	/**
	 * Check if an <a> tag has an affiliate class directly applied.
	 *
	 * @since 1.5.0
	 * @param \WP_HTML_Tag_Processor $processor HTML tag processor instance.
	 * @return bool True if the class is present.
	 */
	private function link_has_affiliate_class( \WP_HTML_Tag_Processor $processor ) {
		$affiliate_classes = $this->parse_class_setting( 'affiliate_class', 'wzlw-affiliate' );

		return $this->class_attribute_has_any( $processor->get_attribute( 'class' ), $affiliate_classes );
	}

	/**
	 * Check whether a class attribute contains any of the supplied classes.
	 *
	 * @since 1.5.0
	 * @param string|null $class_name Class attribute value.
	 * @param string[]    $classes    Classes to look for.
	 * @return bool True if a class is present.
	 */
	private function class_attribute_has_any( $class_name, array $classes ) {
		if ( empty( $classes ) || ! is_string( $class_name ) || '' === $class_name ) {
			return false;
		}

		$class_list = preg_split( '/\s+/', trim( $class_name ) );

		return is_array( $class_list ) && ! empty( array_intersect( $classes, $class_list ) );
	}

	/**
	 * Get the nesting delta for skipped wrapper traversal.
	 *
	 * @since 1.1.0
	 * @param \WP_HTML_Tag_Processor $processor HTML tag processor instance.
	 * @return int Nesting delta.
	 */
	private function get_skip_depth_delta( \WP_HTML_Tag_Processor $processor ) {
		if ( $processor->is_tag_closer() ) {
			return -1;
		}

		if ( $this->tag_is_void( $processor->get_tag() ) || in_array( $processor->get_tag(), array( 'SCRIPT', 'STYLE', 'TEXTAREA', 'TITLE', 'IFRAME', 'NOEMBED', 'NOFRAMES', 'XMP' ), true ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * Check whether a tag is a void element.
	 *
	 * @since 1.1.0
	 * @param string|null $tag_name Tag name.
	 * @return bool True if the tag is a void element.
	 */
	private function tag_is_void( $tag_name ) {
		if ( ! is_string( $tag_name ) || '' === $tag_name ) {
			return false;
		}

		return in_array(
			strtoupper( $tag_name ),
			array( 'AREA', 'BASE', 'BR', 'COL', 'EMBED', 'HR', 'IMG', 'INPUT', 'LINK', 'META', 'SOURCE', 'TRACK', 'WBR' ),
			true
		);
	}

	/**
	 * Add indicator to a single link.
	 *
	 * @since 1.0.0
	 * @param array $matches Regex matches.
	 * @return string Modified link HTML.
	 */
	private function add_indicator_to_link( $matches ) {
		$link_html = $matches[0];

		// Check if link has the no-icon class — suppress visual indicator but
		// still add screen reader text for target="_blank" links.
		$no_icon_classes   = $this->parse_class_setting( 'no_icon_class', 'wzlw-no-icon' );
		$has_no_icon_class = false;
		if ( ! empty( $no_icon_classes ) && preg_match( '/class="([^"]*)"/', $link_html, $class_attr_match ) ) {
			$link_classes      = preg_split( '/\s+/', trim( $class_attr_match[1] ) );
			$has_no_icon_class = is_array( $link_classes ) && ! empty( array_intersect( $no_icon_classes, $link_classes ) );
		}
		if ( $has_no_icon_class ) {
			if ( preg_match( '/\btarget\s*=\s*(?:"_blank"|\'_blank\'|_blank)/i', $link_html ) ) {
				$link_html = str_ireplace( '</a>', $this->get_screen_reader_text() . '</a>', $link_html );
			}
			return $link_html;
		}

		$indicator = $this->get_visual_indicator();

		if ( empty( $indicator ) ) {
			return $link_html;
		}

		// Insert indicator before closing </a> tag.
		$link_html = str_ireplace( '</a>', $indicator . '</a>', $link_html );

		return $link_html;
	}

	/**
	 * Apply configured attributes to an external or affiliate link.
	 *
	 * @since 1.5.0
	 * @param \WP_HTML_Tag_Processor $processor    HTML tag processor instance.
	 * @param bool                   $is_external  Whether the link is external.
	 * @param bool                   $is_affiliate Whether the link is an affiliate link.
	 * @return void
	 */
	private function apply_link_attributes( \WP_HTML_Tag_Processor $processor, $is_external, $is_affiliate ) {
		$attributes = array();

		if ( $is_external ) {
			$attributes = array_merge( $attributes, $this->get_link_attributes( 'external' ) );
		}

		if ( $is_affiliate ) {
			$attributes = array_merge( $attributes, $this->get_link_attributes( 'affiliate' ) );
		}

		$attributes = array_unique( $attributes );

		if ( in_array( 'target_blank', $attributes, true ) ) {
			$processor->set_attribute( 'target', '_blank' );
		}

		$rel_values = array();
		foreach ( array( 'nofollow', 'sponsored', 'ugc' ) as $rel ) {
			if ( in_array( $rel, $attributes, true ) ) {
				$rel_values[] = $rel;
			}
		}

		if ( '_blank' === $processor->get_attribute( 'target' ) ) {
			foreach ( array( 'noopener', 'noreferrer' ) as $rel ) {
				if ( in_array( $rel, $attributes, true ) ) {
					$rel_values[] = $rel;
				}
			}
		}

		if ( ! empty( $rel_values ) ) {
			$existing_rel = (string) $processor->get_attribute( 'rel' );
			$existing_rel = preg_split( '/\s+/', trim( $existing_rel ) );
			if ( ! is_array( $existing_rel ) ) {
				$existing_rel = array();
			}
			$existing_rel = array_filter( $existing_rel );
			$known_rel    = array_map( 'strtolower', $existing_rel );
			foreach ( $rel_values as $rel_value ) {
				if ( ! in_array( strtolower( $rel_value ), $known_rel, true ) ) {
					$existing_rel[] = $rel_value;
					$known_rel[]    = strtolower( $rel_value );
				}
			}
			$processor->set_attribute( 'rel', implode( ' ', $existing_rel ) );
		}
	}

	/**
	 * Get the configured attributes for a link type.
	 *
	 * @since 1.5.0
	 * @param string $link_type External or affiliate.
	 * @return string[] Attribute identifiers.
	 */
	private function get_link_attributes( $link_type ) {
		$attributes = $this->settings[ 'link_attributes_' . $link_type ] ?? array();

		if ( ! is_array( $attributes ) ) {
			$attributes = wp_parse_list( $attributes );
		}

		return array_intersect( $attributes, array( 'nofollow', 'sponsored', 'ugc', 'target_blank', 'noopener', 'noreferrer' ) );
	}

	/**
	 * Get visual indicator HTML.
	 *
	 * @since 1.0.0
	 * @return string Indicator HTML.
	 */
	private function get_visual_indicator() {
		$visual = $this->settings['visual_indicator'] ?? 'icon';

		if ( 'none' === $visual ) {
			return $this->get_screen_reader_text();
		}

		$indicator = '';

		// Add screen reader text.
		$indicator .= $this->get_screen_reader_text();

		// Add visual elements.
		if ( 'icon' === $visual || 'both' === $visual ) {
			// Icon is added via CSS ::before pseudo-element using CSS variable.
			$indicator .= '<span class="wzlw-icon" aria-hidden="true"></span>';
		}

		if ( 'text' === $visual || 'both' === $visual ) {
			$text       = $this->settings['indicator_text'] ?? __( '(opens in new window)', 'webberzone-link-warnings' );
			$indicator .= '<span class="wzlw-text" aria-hidden="true">' . esc_html( $text ) . '</span>';
		}

		return $indicator;
	}

	/**
	 * Get screen reader text.
	 *
	 * @since 1.0.0
	 * @return string Screen reader HTML.
	 */
	private function get_screen_reader_text() {
		$text = $this->settings['screen_reader_text'] ?? __( 'Opens in a new window', 'webberzone-link-warnings' );
		return '<span class="screen-reader-text">' . esc_html( $text ) . '</span>';
	}

	/**
	 * Get ARIA label for link.
	 *
	 * @since 1.0.0
	 * @param string|null $existing_label Existing ARIA label.
	 * @return string|null ARIA label.
	 */
	private function get_aria_label( $existing_label ) {
		$screen_reader_text = $this->settings['screen_reader_text'] ?? __( 'Opens in a new window', 'webberzone-link-warnings' );

		if ( $existing_label ) {
			return $existing_label . ', ' . $screen_reader_text;
		}

		return null; // Let the screen reader text span handle it.
	}

	/**
	 * Check if link is external.
	 *
	 * @since 1.0.0
	 * @param string $url URL to check.
	 * @return bool True if external.
	 */
	private function is_external_link( $url ) {
		// Handle relative URLs.
		if ( ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) || 0 === strpos( $url, '#' ) || 0 === strpos( $url, '?' ) ) {
			return false;
		}

		// Parse URL.
		$parsed_url = wp_parse_url( $url );

		if ( ! isset( $parsed_url['host'] ) ) {
			return false;
		}

		$link_host = strtolower( rtrim( $parsed_url['host'], '.' ) );

		// Check if it's the same as site host.
		if ( $link_host === $this->site_host ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if a URL's host matches the excluded domains list.
	 *
	 * Unlike is_external_link(), this does not check the site host — it only
	 * tests whether the domain is explicitly excluded. Used to detect
	 * excluded-domain target=_blank links under scope=both.
	 *
	 * @since 1.5.0
	 * @param string $url URL to check.
	 * @return bool True if the host matches an excluded domain entry.
	 */
	private function is_excluded_domain( string $url ): bool {
		if ( ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) || 0 === strpos( $url, '#' ) || 0 === strpos( $url, '?' ) ) {
			return false;
		}

		$parsed_url = wp_parse_url( $url );
		if ( ! isset( $parsed_url['host'] ) ) {
			return false;
		}

		$link_host = strtolower( rtrim( $parsed_url['host'], '.' ) );

		// Check excluded domains.
		$excluded_domains = $this->settings['excluded_domains'] ?? '';

		if ( is_string( $excluded_domains ) ) {
			$excluded_domains = array_filter( array_map( 'trim', explode( "\n", $excluded_domains ) ) );
		}

		// Strip scheme and path — keep only the hostname for matching.
		$excluded_domains = array_filter(
			array_map(
				function ( $domain ) {
					$parsed = wp_parse_url( $domain );
					if ( ! empty( $parsed['host'] ) ) {
						return strtolower( $parsed['host'] );
					}
					// No scheme present; strip any trailing slashes/paths.
					return strtolower( strtok( rtrim( $domain, '/' ), '/' ) );
				},
				$excluded_domains
			)
		);

		/**
		 * Filter the excluded domains.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $excluded_domains Array of excluded domains.
		 * @param string $link_host        The link host being checked.
		 */
		$excluded_domains = apply_filters( 'wzlw_excluded_domains', $excluded_domains, $link_host ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		foreach ( $excluded_domains as $domain ) {
			if ( 0 === strpos( $domain, '*.' ) ) {
				// *.example.com — matches subdomains only, not the base domain itself.
				$base = substr( $domain, 2 );
				if ( $base && substr( $link_host, -( strlen( $base ) + 1 ) ) === '.' . $base ) {
					return true;
				}
			} elseif ( $link_host === $domain ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if link should be processed.
	 *
	 * @since 1.0.0
	 * @param bool $is_external Whether link is external.
	 * @param bool $has_target  Whether link has target="_blank".
	 * @return bool True if should be processed.
	 */
	private function should_process_link( $is_external, $has_target ) {
		$scope = isset( $this->settings['scope'] ) ? $this->settings['scope'] : 'external';

		switch ( $scope ) {
			case 'external':
				return $is_external;

			case 'both':
				return $is_external || $has_target;

			default:
				return $is_external;
		}
	}

	/**
	 * Parse a comma-separated class setting into a trimmed array of class names.
	 *
	 * @since 1.5.0
	 * @param string $setting_key Setting key.
	 * @param string $fallback    Fallback value if setting is not set.
	 * @return string[]
	 */
	private function parse_class_setting( string $setting_key, string $fallback ): array {
		$raw = isset( $this->settings[ $setting_key ] ) ? $this->settings[ $setting_key ] : $fallback;
		return array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ) ) );
	}

	/**
	 * Check if current post type is enabled.
	 *
	 * @since 1.0.0
	 * @return bool True if enabled.
	 */
	private function is_post_type_enabled() {
		if ( ! is_singular() ) {
			return false;
		}

		$settings = wzlw_get_settings();
		$enabled  = $settings['enabled_post_types'] ?? array( 'post', 'page' );

		if ( is_string( $enabled ) ) {
			$enabled = array_filter( array_map( 'trim', explode( ',', $enabled ) ) );
		}
		$current_type = get_post_type();

		return in_array( $current_type, $enabled, true );
	}
}
