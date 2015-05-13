<?php
namespace WPDW\Device\Admin;

use WPDW\Options as OPT;

class save_post {

	/**
	 * @var WP_Domain\{$domain}\property
	 */
	private $property;

	/**
	 * @var WPDW\WP\nonce
	 */
	private $nonce;

	/**
	 * @var array
	 */
	private static $default_forms = [
		'post_title', 'post_name', 'menu_order', // ...and more
	];

	/**
	 * Constructor
	 *
	 * @access protected
	 *
	 * @uses   WPDW\_property_object()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		if ( ! $this->property = \WPDW\_property_object( $domain ) )
			return;
		$this->nonce = new \WPDW\WP\nonce( $domain );
		$this->init();
	}

	public function init() {
		add_action( 'save_post', [ &$this, 'save_post' ] );
	}

	/**
	 * @access public
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && \DOING_AUTOSAVE )
			return $post_id;
		if ( ! $_POST )
			return $post_id;
		if ( ! $settings = $this->property->get_setting() )
			return $post_id;

		foreach ( array_keys( $settings ) as $key ) {
			if ( in_array( $key, self::$default_forms, true ) )
				continue;
			$nonce = $this->nonce->get_nonce( $key );
			if ( ! array_key_exists( $nonce, $_POST ) )
				continue;
			if ( ! $this->nonce->check_admin_referer( $key ) )
				continue;
			if ( ! array_key_exists( $key, $_POST ) )
				continue;
			if ( ! $assetInstance = $this->property->$key )
				continue;
			if ( is_array( $_POST[$key] ) )
				$value = filter_input( \INPUT_POST, $key, \FILTER_DEFAULT, \FILTER_FORCE_ARRAY );
			else
				$value = filter_input( \INPUT_POST, $key );
			$assetInstance->update( $post_id, $value );
		}

	}

}
