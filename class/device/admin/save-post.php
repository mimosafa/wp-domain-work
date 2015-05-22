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
	 * @uses   WPDW\_property()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		if ( ! $this->property = \WPDW\_property( $domain ) )
			return;

		/**
		 * Nonce gen
		 * - $domain must be the same as when rendering forms
		 * @see WPDW\Device\Admin\template::__construct()
		 */
		$this->nonce = \WPDW\WP\nonce::getInstance( $domain );

		add_action( 'save_post', [ &$this, 'save_post' ] );
	}

	/**
	 * @access public
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && \DOING_AUTOSAVE )
			return;
		if ( ! $_POST )
			return;
		if ( ! $settings = $this->property->get_setting() )
			return;

		foreach ( $settings as $key => $setting ) {
			
			if ( in_array( $key, self::$default_forms, true ) )
				continue;

			$nonce = $this->nonce->get_nonce( $key );
			if ( ! array_key_exists( $nonce, $_POST ) )
				continue;

			$this->nonce->check_admin_referer( $key );

			if ( ! array_key_exists( $key, $_POST ) ) {
				if ( $setting['type'] !== 'boolean' )
					continue;
				else
					/**
					 * For unchecked boolean
					 */
					$_POST[$key] = '';
			}

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
