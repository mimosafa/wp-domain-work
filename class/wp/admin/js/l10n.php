<?php

namespace WP_Domain_Work\WP\admin\js;

class L10n {
	use \WP_Domain_Work\Utility\Singleton;

	/**
	 * @var arrat
	 * @see https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L441
	 */
	private static $postL10n = [];

	protected function __construct() {
		$this->init();
	}

	private function init() {
		add_action( 'admin_footer-post.php', [ $this, 'js_postL10n' ], 99 );
		add_action( 'admin_footer-post-new.php', [ $this, 'js_postL10n' ], 99 );
	}

	public static function set( $context, $key, $text ) {
		if ( ! is_string( $key ) || ! $key ) {
			return false;
		}
		$_ = self::getInstance();
		if ( ! property_exists( $_, $context ) ) {
			return false;
		}
		$_::${$context}[$key] = wp_json_encode( $text );
	}

	public function js_postL10n() {
		if ( ! self::$postL10n ) {
			return;
		}
		echo "<script type='text/javascript'>\n";
		foreach ( self::$postL10n as $before => $after ) {
			echo "  postL10n.{$before} = {$after};\n";
		}
		echo "</script>\n";
	}
}
