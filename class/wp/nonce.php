<?php

namespace WP_Domain_Work\WP;

/**
 * Create & Verify nonce in admin
 *
 * - used @ WP_Domain_Work\Admin\templates\meta_box_inner
 * 	      @ WP_Domain_Work\Post\save_post
 */
class nonce {

	/**
	 * @var string e.g. post's post_type
	 */
	private $context;

	private static $nonce_format  = '_nonce_%s_%s';
	private static $action_format = '%s-%s';

	/**
	 * @param  string $context
	 * @return void
	 */
	public function __construct( $context ) {
		if ( !is_string( $context ) )
			return false;
		$this -> context = $context;
	}

	/**
	 * @param  string $field
	 * @return string wp_nonce_field
	 */
	public function nonce_field( $field ) {
		$nonce = $this -> get_nonce( $field );
		$action = $this -> get_action( $field );
		return wp_nonce_field( $action, $nonce, true, false );
	}

	/**
	 * @param  string $field
	 * @return bool
	 */
	public function check_admin_referer( $field ) {
		$nonce = $this -> get_nonce( $field );
		$action = $this -> get_action( $field );
		return check_admin_referer( $action, $nonce );
	}

	public function get_nonce( $field ) {
		if ( !is_string( $field ) )
			return false; // throw error
		return esc_attr( sprintf( self::$nonce_format, $this -> context, $field ) );
	}

	public function get_action( $field ) {
		if ( !is_string( $field ) )
			return false; // throw error
		return esc_attr( sprintf( self::$action_format, $this -> context, $field ) );
	}

}
