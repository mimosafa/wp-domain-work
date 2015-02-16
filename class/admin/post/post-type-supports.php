<?php

namespace WP_Domain_Work\Admin\post;

class post_type_supports {
	use \singleton;

	private static $post_type;

	/**
	 * Custom post type supports
	 * @var boolean
	 */
	private $post_type_supports = [
		/**
		 * Core supports
		 */
		'title'           => false,
		'editor'          => false,
		'author'          => false,
		'thumbnail'       => false,
		'excerpt'         => false,
		'trackbacks'      => false,
		'custom-fields'   => false,
		'comments'        => false,
		'revisions'       => false,
		'page-attributes' => false,
		'post-formats'    => false,

		/**
		 * Additional supports
		 */
		'slug' => false,
	];

	protected function __construct() {
		//
	}

	public static function init( $post_type ) {
		if ( ! $post_type || ! is_string( $post_type ) ) {
			return;
		}
		self::$post_type = $post_type;
		$_PTS = self::getInstance();
		$_PTS->init_supports();
	}

	private function init_supports() {
		if ( ! post_type_exists( self::$post_type ) ) {
			return;
		}
		$this->set_default_supports();

		$supports = \WP_Domain_Work\Plugin::get_post_type_supports();
		$domain = get_post_type_object( self::$post_type )->rewrite['slug'];
		if ( array_key_exists( $domain, $supports ) ) {
			foreach ( $supports[$domain] as $feature => $string ) {
				$this->post_type_supports[$feature] = $string;
			}
		}
		if ( $this->post_type_supports = array_filter( $this->post_type_supports ) ) {
			$this->add_post_type_supports();
		}
	}

	private function set_default_supports() {
		//
	}

	/**
	 * Add post type support, if necessary show as readonly form
	 */
	private function add_post_type_supports() {
		foreach ( $this->post_type_supports as $feature => $string ) {
			if ( 'support' === $string ) {
				add_post_type_support( self::$post_type, $feature );
			} else if ( 'readonly' === $string ) {
				$cl = sprintf( '\\WP_Domain_Work\\Admin\\post\\readonly_%s', $feature );
				if ( class_exists( $cl ) ) {
					new $cl( self::$post_type );
				}
			}
		}
	}
	
}
