<?php

namespace wordpress\model;

/**
 *
 */
class posts {

	/**
	 * @var $array
	 */
	protected $_query_arg;

	/**
	 *
	 */
	public function __construct() {
		//
	}

	/**
	 *
	 */
	public function get( $args ) {
		return get_posts( $args );
	}

	/**
	 *
	 */
	public function update( $value ) {
		//
	}

}
