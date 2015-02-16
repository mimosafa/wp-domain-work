<?php

namespace WP_Domain_Work\Property;

class string extends simple {

	/**
	 * @var  bool
	 * @todo 
	 */
	protected $_multi_byte = false;

	/**
	 * @var integer
	 * @todo
	 */
	protected $_max_length;

	public function __construct( $var, Array $arg ) {
		if ( !parent::__construct( $var, $arg ) ) {
			return false;
		}
	}

	public function filter( $value ) {
		return $value;
	}

	public function getValue() {
		return $this->value;
	}

}
