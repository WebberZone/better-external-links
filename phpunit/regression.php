<?php

namespace WebberZone\Link_Warnings\Admin {
	function flush_rewrite_rules() {}
	function set_transient( ...$args ) {}
}

namespace {
	$core = $argv[1] ?? getenv( 'WP_CORE_DIR' );
	if ( ! $core || ! is_file( rtrim( $core, '/\\' ) . '/wp-includes/functions.php' ) ) {
		fwrite( STDERR, "Usage: php phpunit/regression.php /path/to/wordpress\n" );
		exit( 1 );
	}

	define( 'ABSPATH', rtrim( $core, '/\\' ) . '/' );
	define( 'WPINC', 'wp-includes' );
	define( 'HOUR_IN_SECONDS', 3600 );
	foreach ( array( 'compat', 'plugin', 'functions', 'formatting', 'http', 'kses', 'shortcodes', 'version' ) as $file ) {
		require ABSPATH . WPINC . '/' . $file . '.php';
	}
	if ( is_file( ABSPATH . WPINC . '/utf8.php' ) ) {
		require ABSPATH . WPINC . '/utf8.php';
	}
	// Attribute values containing "&" force named-character-reference decoding.
	require ABSPATH . WPINC . '/class-wp-token-map.php';
	require ABSPATH . WPINC . '/html-api/html5-named-character-references.php';
	spl_autoload_register(
		function ( $class ) {
			if ( 0 === strpos( $class, 'WP_HTML_' ) ) {
				require ABSPATH . WPINC . '/html-api/class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
			}
		}
	);

	function home_url( $path = '' ) {
		return 'https://site.test/' . ltrim( $path, '/' );
	}
	function wp_salt( $scheme = 'auth' ) {
		return 'link-warnings-regression-key';
	}
	function __( $text, $domain = '' ) {
		return $text;
	}
	function is_multisite() {
		return false;
	}
	function is_singular() {
		return true;
	}
	function get_post_type() {
		return 'post';
	}

	require dirname( __DIR__ ) . '/includes/autoloader.php';
	require dirname( __DIR__ ) . '/includes/options-api.php';

	use WebberZone\Link_Warnings\Admin\Activator;
	use WebberZone\Link_Warnings\Content_Processor;
	use WebberZone\Link_Warnings\Options_API;
	use WebberZone\Link_Warnings\Redirect_Handler;

	$GLOBALS['wzlw_test_settings'] = array();
	$GLOBALS['wzlw_test_updates']  = array();
	$GLOBALS['wp_rewrite']         = new class() {
		public function init() {}
	};
	add_filter(
		'pre_option_blog_charset',
		static function () {
			return 'UTF-8';
		}
	);
	add_filter(
		'pre_option_wzlw_settings',
		static function () {
			return $GLOBALS['wzlw_test_settings'];
		}
	);
	add_filter(
		'pre_update_option_wzlw_settings',
		static function ( $value, $old ) {
			$GLOBALS['wzlw_test_updates'][] = $value;
			return $old;
		},
		10,
		2
	);
	new Content_Processor();
	add_filter( 'the_content', 'wpautop', 10 );
	add_filter( 'the_content', 'do_shortcode', 11 );
	add_shortcode(
		'wzlw_test_form',
		static function () {
			return '<textarea>text</textarea>';
		}
	);

	function settings( array $settings = array() ) {
		$GLOBALS['wzlw_test_settings'] = array_merge(
			array(
				'warning_method'     => 'inline_modal',
				'scope'              => 'external',
				'enabled_post_types' => 'post,page',
			),
			$settings
		);
		$GLOBALS['wzlw_test_updates']  = array();
		Options_API::flush_cache();
	}
	function is_valid_url( $url ) {
		static $method = null;
		if ( null === $method ) {
			$method = new \ReflectionMethod( Redirect_Handler::class, 'is_valid_url' );
			$method->setAccessible( true );
		}
		return $method->invoke( ( new \ReflectionClass( Redirect_Handler::class ) )->newInstanceWithoutConstructor(), $url );
	}
	function expect( $condition, $message ) {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}
	function process( $content ) {
		return apply_filters( 'the_content', $content );
	}
	function link_attributes( $content, $href ) {
		$processor = new \WP_HTML_Tag_Processor( $content );
		while ( $processor->next_tag( 'A' ) ) {
			if ( $href === $processor->get_attribute( 'href' ) ) {
				$result = array();
				foreach ( array( 'class', 'rel', 'aria-label', 'data-wzlw-url', 'data-wzlw-external', 'data-wzlw-blank', 'data-wzlw-download', 'data-wzlw-excluded' ) as $name ) {
					$result[ $name ] = $processor->get_attribute( $name );
				}
				return $result;
			}
		}
		throw new \RuntimeException( 'Expected link not found: ' . $href );
	}

	$tests = array();
	$tests['reactivation leaves existing settings untouched']       = static function () {
		settings(
			array(
				'warning_method'     => 'redirect',
				'excluded_domains'   => 'trusted.test',
				'enabled_post_types' => 'page',
				'redirect_countdown' => 12,
			)
		);
		Activator::activate( false );
		expect( array() === $GLOBALS['wzlw_test_updates'], 'Activation attempted to update existing settings.' );
	};
	$tests['ordinary merged updates still replace supplied values'] = static function () {
		settings( array( 'excluded_domains' => 'trusted.test' ) );
		Options_API::update_settings( array( 'warning_method' => 'redirect' ), true );
		$written = $GLOBALS['wzlw_test_updates'][0];
		expect( 'redirect' === $written['warning_method'] && 'trusted.test' === $written['excluded_domains'], 'Normal update semantics changed.' );
	};
	foreach ( array( 'https://outside.test/page', 'https://outside.test/?a=1&b=2', 'https://outside.test/page#section', 'https://outside.test/?q=a+b', 'https://outside.test/path%20name', 'https://outside.test/?next=%2Faccount', '/go/product/?a=1&b=2#details', '//outside.test/page', '//outside.test/?a=1&b=2' ) as $destination ) {
		$tests[ 'signed URL round trip: ' . $destination ] = static function () use ( $destination ) {
			$url = Redirect_Handler::get_redirect_url( $destination );
			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
			$received = esc_url_raw( $query['url'] ?? '' );
			expect( $destination === $received, 'Destination changed during request decoding.' );
			expect( hash_equals( hash_hmac( 'sha256', $received, wp_salt( 'auth' ) ), $query['wzlw_sig'] ?? '' ), 'Signature no longer matches.' );
			expect( is_valid_url( $received ), 'Redirect endpoint rejects a URL it signed itself.' );
		};
	}
	$tests['protocol-relative links use their hostname'] = static function () {
		settings();
		$html = process( '<a href="//outside.test/">external</a><a href="//site.test/">internal</a><a href="/go/">relative</a>' );
		expect( 'true' === link_attributes( $html, '//outside.test/' )['data-wzlw-external'], 'External network-path reference was ignored.' );
		expect( null === link_attributes( $html, '//site.test/' )['data-wzlw-external'], 'Same-site URL marked external.' );
		expect( null === link_attributes( $html, '/go/' )['data-wzlw-external'], 'Root-relative URL marked external.' );
	};
	$tests['configured download links are processed regardless of host'] = static function () {
		settings();
		$html = process(
			'<a href="/files/report.pdf">internal PDF</a>'
			. '<a href="https://outside.test/archive.ZIP?download=1#top">external ZIP</a>'
			. '<a href="/download?file=report.pdf">not a file path</a>'
		);
		$internal = link_attributes( $html, '/files/report.pdf' );
		$external = link_attributes( $html, 'https://outside.test/archive.ZIP?download=1#top' );
		$other    = link_attributes( $html, '/download?file=report.pdf' );
		expect( 'true' === $internal['data-wzlw-download'] && null === $internal['data-wzlw-external'], 'Internal download link was not classified as a download.' );
		expect( 'true' === $external['data-wzlw-download'] && 'true' === $external['data-wzlw-external'], 'External download link was not classified as both external and downloadable.' );
		expect( null === $other['data-wzlw-download'] && null === $other['data-wzlw-url'], 'A file extension in a query string was treated as a download.' );
	};
	$tests['download extension settings are normalized and respected'] = static function () {
		settings( array( 'download_extensions' => ' .PDF, csv ' ) );
		$html = process( '<a href="/files/report.pdf">PDF</a><a href="/files/data.CSV">CSV</a><a href="/files/archive.zip">ZIP</a>' );
		expect( 'true' === link_attributes( $html, '/files/report.pdf' )['data-wzlw-download'], 'Configured PDF extension was not matched.' );
		expect( 'true' === link_attributes( $html, '/files/data.CSV' )['data-wzlw-download'], 'Configured CSV extension was not matched.' );
		expect( null === link_attributes( $html, '/files/archive.zip' )['data-wzlw-download'], 'Unconfigured extension was matched.' );
	};
	$tests['excluded domains still suppress configured downloads'] = static function () {
		settings( array( 'excluded_domains' => 'trusted.test' ) );
		$html = process( '<a href="https://trusted.test/report.pdf">PDF</a>' );
		$link = link_attributes( $html, 'https://trusted.test/report.pdf' );
		expect( 'true' === $link['data-wzlw-excluded'] && null === $link['data-wzlw-download'], 'Excluded download link was processed.' );
	};
	$tests['download indicators use a distinct icon class'] = static function () {
		settings();
		$html = process( '<a href="/files/report.pdf">PDF</a><a href="https://outside.test/page">External</a>' );
		expect( 1 === substr_count( $html, 'wzlw-download-icon' ), 'Download link did not receive its distinct icon.' );
		expect( 1 === substr_count( $html, 'class="wzlw-icon"' ), 'External link icon markup changed unexpectedly.' );
	};
	foreach ( array( 'external', 'both' ) as $scope ) {
		$tests[ 'filtered exclusions survive PHP processing: ' . $scope ] = static function () use ( $scope ) {
			settings( array( 'scope' => $scope ) );
			$filter = static function ( $domains, $host ) {
				return 'outside.test' === $host ? array( 'outside.test' ) : $domains;
			};
			add_filter( 'wzlw_excluded_domains', $filter, 10, 2 );
			try {
				$html = process( '<a href="https://outside.test/" target="_blank" aria-label="Example">test</a>' );
				$link = link_attributes( $html, 'https://outside.test/' );
				expect( 'true' === $link['data-wzlw-excluded'], 'PHP exclusion decision was not preserved.' );
				expect( null === $link['data-wzlw-url'] && null === $link['data-wzlw-blank'], 'Excluded link still has warning attributes.' );
			} finally {
				remove_filter( 'wzlw_excluded_domains', $filter, 10 );
			}
		};
	}
	$tests['wildcard exclusions and forced affiliate precedence'] = static function () {
		settings(
			array(
				'scope'                     => 'both',
				'excluded_domains'          => '*.outside.test',
				'link_attributes_affiliate' => array( 'sponsored' ),
			)
		);
		$html = process( '<a href="//sub.outside.test/" target="_blank">excluded</a><a href="//outside.test/">base</a><a class="wzlw-affiliate" href="https://sub.outside.test/">affiliate</a>' );
		expect( 'true' === link_attributes( $html, '//sub.outside.test/' )['data-wzlw-excluded'], 'Wildcard exclusion failed.' );
		expect( 'true' === link_attributes( $html, '//outside.test/' )['data-wzlw-external'], 'Wildcard unexpectedly excluded its base domain.' );
		$affiliate = link_attributes( $html, 'https://sub.outside.test/' );
		expect( 'true' === $affiliate['data-wzlw-external'] && 'sponsored' === $affiliate['rel'], 'Affiliate did not override exclusion.' );
	};
	foreach ( array( 'script', 'style', 'textarea', 'title', 'iframe', 'noembed', 'noframes', 'xmp' ) as $tag ) {
		foreach ( array( 'wzlw-no-icon-wrapper', 'wzlw-force-external-wrapper', 'wzlw-affiliate-wrapper' ) as $wrapper ) {
			$tests[ 'atomic element does not leak wrapper state: ' . $tag . '/' . $wrapper ] = static function () use ( $tag, $wrapper ) {
				settings( array( 'link_attributes_affiliate' => array( 'sponsored' ) ) );
				$html = process( '<div class="' . $wrapper . '"><' . $tag . '>text</' . $tag . '><p><a href="/inside/">inside</a></p></div><p><a href="/after/">after</a><a href="https://outside.test/">external</a></p>' );
				expect( null === link_attributes( $html, '/after/' )['data-wzlw-external'], 'Wrapper classification leaked to a following link.' );
				$outside = link_attributes( $html, 'https://outside.test/' );
				expect( false === strpos( (string) $outside['class'], 'wzlw-no-icon' ), 'Suppression leaked past the wrapper.' );
				expect( null === $outside['rel'], 'Affiliate attributes leaked past the wrapper.' );
				$atomic_wrapper = process( '<' . $tag . ' class="' . $wrapper . '">text</' . $tag . '><a href="https://outside.test/">external</a>' );
				$outside        = link_attributes( $atomic_wrapper, 'https://outside.test/' );
				expect( false === strpos( (string) $outside['class'], 'wzlw-no-icon' ) && null === $outside['rel'], 'An atomic wrapper affected later links.' );
			};
		}
	}
	$tests['nested ordinary wrappers still work']                              = static function () {
		settings();
		$html = process( '<div class="wzlw-no-icon-wrapper"><section><img src="image.png"><a href="https://inside.test/">inside</a></section></div><a href="https://outside.test/">outside</a>' );
		expect( false !== strpos( (string) link_attributes( $html, 'https://inside.test/' )['class'], 'wzlw-no-icon' ), 'Nested suppression stopped working.' );
		expect( false === strpos( (string) link_attributes( $html, 'https://outside.test/' )['class'], 'wzlw-no-icon' ), 'Nested suppression leaked.' );
	};
	$tests['shortcode-generated atomic content does not leak suppression']     = static function () {
		settings();
		$html = process( '<div class="wzlw-no-icon-wrapper">[wzlw_test_form]</div><a href="https://outside.test/">outside</a>' );
		expect( false === strpos( (string) link_attributes( $html, 'https://outside.test/' )['class'], 'wzlw-no-icon' ), 'Shortcode content leaked suppression.' );
	};
	$tests['redirect endpoint URL validation'] = static function () {
		$cases = array(
			'//outside.test/page'      => true,
			'//outside.test/'          => true,
			'//site.test/page'         => true,
			'//SITE.test/page'         => true,
			'/go/product/'             => true,
			'https://outside.test/'    => true,
			'https://site.test/'       => true,
			'http:evil.test'           => false,
			'javascript:alert(1)'      => false,
			'http://site.test/forced/' => true,
			'mailto:a@site.test'       => false,
		);
		foreach ( $cases as $url => $expected ) {
			expect( $expected === is_valid_url( $url ), 'Wrong validity for ' . $url . ' (expected ' . var_export( $expected, true ) . ').' );
		}
	};
	$tests['protocol-relative links survive the full redirect round trip'] = static function () {
		settings( array( 'warning_method' => 'redirect', 'scope' => 'both' ) );
		$html = process( '<a href="//outside.test/page?a=1&b=2">external</a>' );
		$link = link_attributes( $html, '//outside.test/page?a=1&b=2' );
		expect( 'true' === $link['data-wzlw-external'], 'Network-path reference was not marked external.' );
		$signed = Redirect_Handler::get_redirect_url( '//outside.test/page?a=1&b=2' );
		parse_str( (string) wp_parse_url( $signed, PHP_URL_QUERY ), $query );
		$received = esc_url_raw( $query['url'] ?? '' );
		expect( is_valid_url( $received ), 'Signed network-path destination was rejected by the endpoint.' );
		expect( hash_equals( hash_hmac( 'sha256', $received, wp_salt( 'auth' ) ), $query['wzlw_sig'] ?? '' ), 'Signature mismatch for network-path destination.' );
	};
	$tests['every signed link in redirect mode is accepted by the endpoint'] = static function () {
		settings( array( 'warning_method' => 'redirect', 'scope' => 'both' ) );
		$html = process(
			'<a class="wzlw-force-external" href="https://site.test/forced/">forced internal</a>'
			. '<div class="wzlw-force-external-wrapper"><a href="https://site.test/in-wrapper/">wrapped internal</a></div>'
			. '<a href="https://site.test/blank/" target="_blank">internal blank</a>'
			. '<a href="//outside.test/proto">proto external</a>'
			. '<a href="https://outside.test/?a=1&b=2">external query</a>'
		);
		$processor = new \WP_HTML_Tag_Processor( $html );
		$checked   = 0;
		while ( $processor->next_tag( 'A' ) ) {
			$signed = $processor->get_attribute( 'data-wzlw-redirect-url' );
			if ( null === $signed ) {
				continue;
			}
			++$checked;
			parse_str( (string) wp_parse_url( $signed, PHP_URL_QUERY ), $query );
			$received = esc_url_raw( $query['url'] ?? '' );
			expect( is_valid_url( $received ), 'Endpoint rejects a URL it signed: ' . $processor->get_attribute( 'href' ) );
			expect( hash_equals( hash_hmac( 'sha256', $received, wp_salt( 'auth' ) ), $query['wzlw_sig'] ?? '' ), 'Signature mismatch for ' . $processor->get_attribute( 'href' ) );
		}
		expect( 5 === $checked, 'Expected 5 signed links, saw ' . $checked . '.' );
	};
	$tests['uppercase closing tags receive indicators and screen-reader text'] = static function () {
		settings();
		$html = process( wp_kses_post( '<A href="https://outside.test/">test</A>' ) );
		expect( 1 === substr_count( $html, 'class="wzlw-icon"' ), 'Uppercase link has no icon.' );
		expect( 1 === substr_count( $html, 'class="screen-reader-text"' ), 'Uppercase link has no screen-reader text.' );
		$html = process( wp_kses_post( '<A class="wzlw-no-icon" href="https://outside.test/" target="_blank">test</A>' ) );
		expect( false === strpos( $html, 'class="wzlw-icon"' ) && 1 === substr_count( $html, 'class="screen-reader-text"' ), 'Suppressed uppercase link lost its screen-reader text.' );
	};
	$tests['no-icon target detection accepts single-quoted attributes'] = static function () {
		settings();
		$html = process( "<a class='wzlw-no-icon' href='https://outside.test/' target='_blank'>test</a>" );
		expect( false === strpos( $html, 'class="wzlw-icon"' ) && 1 === substr_count( $html, 'class="screen-reader-text"' ), 'Single-quoted no-icon link lost its screen-reader text.' );
	};

	$failed = 0;
	foreach ( $tests as $name => $test ) {
		try {
			$test();
			echo 'PASS ', $name, PHP_EOL;
		} catch ( \Throwable $error ) {
			++$failed;
			echo 'FAIL ', $name, ': ', $error->getMessage(), PHP_EOL;
		}
	}
	echo count( $tests ) - $failed, '/', count( $tests ), ' passed', PHP_EOL;
	exit( $failed ? 1 : 0 );
}
