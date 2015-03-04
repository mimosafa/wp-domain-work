<?php

namespace WP_Domain_Work\Admin\templates;

/**
 * @uses WP_Domain_Work\WP\nonce
 * @uses mimosafa\Decoder
 * @uses WP_Domain_Work\Property\(property type)
 */
class form {

	/**
	 * @var string
	 */
	private $context;

	/**
	 * @var bool
	 */
	private $_post_new;

	/**
	 * @var object WP_Domain_Work\WP\nonce
	 */
	private static $nonceInstance;

	/**
	 * @var object mimosafa\Decoder
	 */
	private static $decoder;

	/**
	 * Constructor
	 */
	public function __construct( $context ) {
		if ( ! $context || ! is_string( $context ) ) {
			return;
		}
		$this->context   = $context;
		$this->_post_new = ( 'add' === get_current_screen()->action ) ? true : false;
		if ( ! self::$nonceInstance ) {
			self::$nonceInstance = new \WP_Domain_Work\WP\nonce( $context );
		}
		if ( ! self::$decoder ) {
			self::$decoder = new \mimosafa\Decoder();
		}
	}

}
