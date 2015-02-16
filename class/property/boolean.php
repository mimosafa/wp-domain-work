<?php

namespace WP_Domain_Work\Property;

/**
 *
 */
class boolean extends simple {

	protected $_true_value  = 1;
	protected $_false_value = null;

	public function __construct( $var, Array $arg ) {
		if ( ! parent::__construct( $var, $arg ) ) {
			return false;
		}
		if ( array_key_exists( 'true_value',  $arg ) ) {
			$this->_true_value  = $arg['true_value'];
		}
		if ( array_key_exists( 'false_value', $arg ) ) {
			$this->_false_value = $arg['false_value'];
		}
	}

	public function filter( $value ) {
		return $value ? $this->_true_value : $this->_false_value;
	}

}
