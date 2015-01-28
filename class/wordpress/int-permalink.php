<?php

namespace wordpress;

/**
 * Rewrite slug to post_id for custom post types.
 */
class int_permalink {
	use \singleton;

	/**
	 * Post types
	 *
	 * @var Array
	 */
	private static $post_types = [];

	/**
	 */
	protected function __construct() {
		$this -> init();
	}

	/**
	 * init function.
	 */
	private function init() {
		add_action( 'init', [ $this, 'set_rewrite' ], 11 );
		add_filter( 'post_type_link', [ $this, 'set_permalink' ], 10, 2 );
	}

	/**
	 * Set post types.
	 *
	 * @param string $post_type variable-length
	 */
	public function set( $post_type ) {
		if ( is_string( $post_type ) ) {
			self::$post_types[] = $post_type;
		}
	}

	/**
	 * @see http://www.torounit.com/blog/2011/04/17/683/
	 * @see http://blog.ext.ne.jp/?p=1416
	 */

	/**
	 * action_hook 'init'
	 */
	public function set_rewrite() {
		if ( empty( self::$post_types ) ) {
			return;
		}
		global $wp_rewrite;
		foreach ( self::$post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				$slug = get_post_type_object( $post_type ) -> rewrite['slug'];
				$wp_rewrite -> add_rewrite_tag( "%{$post_type}_id%", '([^/]+)', "post_type={$post_type}&p=" );
				$wp_rewrite -> add_permastruct( $post_type, "/{$slug}/%{$post_type}_id%", false );
			}
		}
	}

	/**
	 * filter_hook 'post_type_link'
	 */
	public function set_permalink( $url, $post ) {
		if ( empty( self::$post_types ) ) {
			return $url;
		}
		global $wp_rewrite;
		$post = get_post( $post );
		$post_type = $post -> post_type;
		if ( in_array( $post_type, self::$post_types ) ) {
			$_url = str_replace(
				"%{$post_type}_id%",
				$post -> ID,
				$wp_rewrite -> get_extra_permastruct( $post_type )
			);
			$url = home_url( user_trailingslashit( $_url ) );
		}
		return $url;
	}

}
