<?php

namespace wordpress;

/**
 * @see http://firegoby.jp/archives/5309
 */
class create_endpoints {

	/**
	 *
	 */
	private $endpoints = [];

	/**
	 * @access public
	 *
	 * @param  string $endpoint (required)
	 */
	public function set( $endpoint ) {
		if ( !$endpoint || !is_string( $endpoint ) ) {
			return;
		}
		$this -> endpoints[] = $endpoint;
	}

	/**
	 * @access public
	 */
	public function init() {
		add_action( 'init', [ $this, 'add_rewrite_endpoints' ], 1 );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
	}

	/**
	 *
	 */
	public function add_rewrite_endpoints() {
		if ( empty( $this -> endpoints ) ) {
			return;
		}

		/**
		 * rewrite rulesを取得
		 */
		global $wp_rewrite;
		$rules = $wp_rewrite -> wp_rewrite_rules();

		foreach ( $this -> endpoints as $endpoint ) {
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
		if ( !empty( $this -> endpoints ) ) {
			foreach ( $this -> endpoints as $endpoint ) {
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
		return isset( $wp_query -> query[$endpoint] );
	}

}

/**
 * \wordpress\is_endpoint();
 *
 * @param  string Your custom endpoint.
 * @return bool
 */
if ( !function_exists( 'wordpress\is_endpoint' ) ) {
	function is_endpoint( $endpoint ) {
		return create_rewrite_endpoints::is_endpoint( $endpoint );
	}
}
