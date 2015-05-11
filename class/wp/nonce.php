<?php
namespace WPDW\WP;

/**
 * Create & Verify nonce in admin
 */
class nonce {

	const NONCE_FORMAT  = '_wp_domain_work_nonce_%s_%s';
	const ACTION_FORMAT = 'wp-domain-work-%s-%s';

	/**
	 * @var string e.g. post's post_type
	 */
	private $context;

	/**
	 * @param  string $context
	 * @return void
	 */
	public function __construct( $context ) {
		if ( ! is_string( $context ) || ! $context )
			return false;
		$this->context = $context;
	}

	/**
	 * @param  string $field
	 * @return string wp_nonce_field
	 */
	public function nonce_field( $field ) {
		$nonce = $this->get_nonce( $field );
		$action = $this->get_action( $field );
		return wp_nonce_field( $action, $nonce, true, false );
	}

	public function create_nonce( $field ) {
		$action = $this->get_action( $field );
		return wp_create_nonce( $action );
	}

	/**
	 * @param  string $field
	 * @return bool
	 */
	public function check_admin_referer( $field ) {
		$nonce = $this->get_nonce( $field );
		$action = $this->get_action( $field );
		return check_admin_referer( $action, $nonce );
	}

	public function get_nonce( $field ) {
		if ( ! is_string( $field ) )
			return false; // throw error
		return esc_attr( sprintf( self::NONCE_FORMAT, $this->context, $field ) );
	}

	public function get_action( $field ) {
		if ( ! is_string( $field ) )
			return false; // throw error
		return esc_attr( sprintf( self::ACTION_FORMAT, $this->context, $field ) );
	}

}
