<?php
namespace WPDW\WP;

/**
 * Create & Verify nonce in admin
 */
class nonce {

	/**
	 * String formats
	 */
	const NONCE_FORMAT  = '_wpdw_wp_nonce_%s_%s';
	const ACTION_FORMAT = 'wpdw-wp-nonce-%s-%s';

	/**
	 * @var string e.g. post's post_type
	 */
	private $context;

	/**
	 * WPDW\WP\nonce instances
	 * @var array
	 */
	private static $instances = [];

	/**
	 * Singleton pattern
	 *
	 * @access public
	 *
	 * @param  string $context
	 * @return WPDW\WP\nonce
	 */
	public static function getInstance( $context ) {
		if ( ! $context = filter_var( $context ) )
			return;
		if ( ! isset( self::$instances[$context] ) )
			self::$instances[$context] = new self( $context );
		return self::$instances[$context];
	}

	/**
	 * @access private
	 *
	 * @param  string $context
	 * @return void
	 */
	private function __construct( $context ) {
		if ( ! is_string( $context ) || ! $context )
			return false;
		$this->context = $context;
	}

	/**
	 * @access public
	 *
	 * @param  string $field
	 * @return string wp_nonce_field
	 */
	public function nonce_field( $field ) {
		if ( ! $field = filter_var( $field ) )
			return;
		return wp_nonce_field( $this->get_action( $field ), $this->get_action( $field ), true, false );
	}

	public function create_nonce( $field ) {
		if ( ! $field = filter_var( $field ) )
			return;
		return wp_create_nonce( $this->get_action( $field ) );
	}

	/**
	 * @access public
	 *
	 * @param  string $field
	 * @return bool
	 */
	public function check_admin_referer( $field ) {
		if ( ! $field = filter_var( $field ) )
			return;
		return check_admin_referer( $this->get_action( $field ), $this->get_nonce( $field ) );
	}

	public function get_nonce( $field ) {
		if ( ! $field = filter_var( $field ) )
			return;
		return esc_attr( sprintf( self::NONCE_FORMAT, $this->context, $field ) );
	}

	public function get_action( $field ) {
		if ( ! $field = filter_var( $field ) )
			return;
		return esc_attr( sprintf( self::ACTION_FORMAT, $this->context, $field ) );
	}

}
