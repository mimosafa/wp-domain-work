<?php

namespace WP_Domain_Work\Post;

/**
 * @uses WP_Domain\(domain)\properties
 * @uses WP_Domain_Work\WP\admin\nonce
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
	 * @var object WP_Domain\(domain)\properties
	 */
	private static $properties;

	/**
	 * @var object WP_Domain_Work\WP\admin\nonce
	 */
	private static $nonceInstance;

	/**
	 * @param string $post_type
	 */
	public function __construct( $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			return;
		}
		$this->post_type = $post_type;
		$this->domain = get_post_type_object( $post_type )->rewrite['slug'];

		self::$nonceInstance = new \WP_Domain_Work\WP\admin\nonce( $post_type );

		$this->init();
	}

	/**
	 *
	 */
	private function init() {
		$hook = 'save_post_' . $this->post_type;
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
		 * @uses WP_Domain\(domain)\properties
		 */
		$propClass = sprintf( 'WP_Domain\\%s\\properties', $this->domain );
		if ( !class_exists( $propClass ) ) {
			return $post_id;
		}
		self::$properties = new $propClass( $post_id );

		if ( ! $propSettings = self::$properties->get_property_setting() ) {
			return $post_id;
		}

		foreach ( $propSettings as $key => $arg ) {

			$nonce = self::$nonceInstance->get_nonce( $key );
			if ( ! array_key_exists( $nonce, $_POST ) ) {
				continue;
			}
			if ( ! self::$nonceInstance->check_admin_referer( $key ) ) {
				continue;
			}

			$val = array_key_exists( $key, $_POST ) ? $_POST[$key] : '';

			/**
			 * Save action
			 */
			self::$properties->$key = $val;

		}
	}

}
