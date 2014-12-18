<?php

namespace wordpress\admin;

/**
 * @uses \(domain)\properties
 * @uses \admin\nonce (*important)
 */
class save_post {

	/**
	 * @var string
	 */
	private $post_type;

	/**
	 * @var string
	 */
	private $domain;

	/**
	 * @var object \(domain)\properties
	 */
	private static $properties;

	/**
	 * @var object \admin\nonce
	 */
	private static $nonceInstance;

	/**
	 * @param string $post_type
	 */
	public function __construct( $post_type ) {
		if ( !post_type_exists( $post_type ) ) {
			return false; // throw error
		}
		$this -> post_type = $post_type;
		$this -> domain = get_post_type_object( $post_type ) -> rewrite['slug'];

		self::$nonceInstance = new nonce( $post_type );

		$this -> init();
	}

	/**
	 *
	 */
	private function init() {
		$hook = 'save_post_' . $this -> post_type;
		add_action( $hook, [ $this, 'save_post' ] );
	}

	/**
	 *
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		/**
		 * @uses \(domain)\properties
		 */
		$propClass = '\\' . $this -> domain . '\\properties';
		if ( !class_exists( $propClass ) ) {
			return $post_id;
		}
		self::$properties = new $propClass( $post_id );

		if ( !$propSettings = self::$properties -> get_property_setting() ) {
			return $post_id;
		}

		foreach ( $propSettings as $key => $arg ) {

			$nonce = self::$nonceInstance -> get_nonce( $key );
			if ( !array_key_exists( $nonce, $_POST ) ) {
				continue;
			}
			if ( !self::$nonceInstance -> check_admin_referer( $key ) ) {
				continue;
			}

			$val = array_key_exists( $key, $_POST ) ? $_POST[$key] : '';

			/**
			 * Save action
			 */
			self::$properties -> $key = $val;

		}
	}

}
