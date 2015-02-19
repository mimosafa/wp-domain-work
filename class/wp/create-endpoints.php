<?php

namespace WP_Domain_Work\WP;

/**
 * @see http://firegoby.jp/archives/5309
 *
 * @todo  flush rewrite rules...
 */
class create_endpoints {
	use \WP_Domain_Work\Utility\Singleton;

	/**
	 *
	 */
	private static $endpoints = [];

	/**
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * @access public
	 */
	public function init() {
		add_action( 'init', [ $this, 'add_rewrite_endpoints' ], 1 );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
	}

	/**
	 * @access public
	 *
	 * @param  string $endpoint (required)
	 */
	public static function set( $endpoint ) {
		if ( ! $endpoint || ! is_string( $endpoint ) ) {
			return;
		}
		$_CE = self::getInstance();
		$_CE::$endpoints[] = $endpoint;
	}

	/**
	 *
	 */
	public function add_rewrite_endpoints() {
		if ( empty( self::$endpoints ) ) {
			return;
		}

		/**
		 * rewrite rulesを取得
		 */
		global $wp_rewrite;
		$rules = $wp_rewrite->wp_rewrite_rules();

		foreach ( self::$endpoints as $endpoint ) {
			/**
			 * エンドポイントが既に DBのオプションテーブルに書き込まれていれば continue
			 *
			 * @see https://core.trac.wordpress.org/browser/tags/3.9.2/src/wp-includes/rewrite.php#L1274
			 */
			$search = $endpoint . '(/(.*))?/?$';
			if ( isset( $rules[$search] ) ) {
				continue;
			}
			add_rewrite_endpoint( $endpoint, EP_ROOT );
		}
	}

	/**
	 *
	 */
	public function add_query_vars( $vars ) {
		if ( ! empty( self::$endpoints ) ) {
			foreach ( self::$endpoints as $endpoint ) {
				$vars[] = $endpoint;
			}
		}
		return $vars;
	}

	/**
	 *
	 */
	public static function is_endpoint( $endpoint ) {
		global $wp_query;
		return isset( $wp_query->query[$endpoint] );
	}

}

