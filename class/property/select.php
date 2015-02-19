<?php

namespace WP_Domain_Work\Property;

class select extends simple {

	/**
	 * @var array
	 */
	public $options = [];

	public function __construct( $var, Array $arg ) {
		if ( ! parent::__construct( $var, $arg ) ) {
			return false;
		}
		if ( ! array_key_exists( 'options', $arg ) || !is_array( $arg['options'] ) ) {
			return false;
		}
		$this->options = $arg['options'];
	}

	public function filter( $value ) {
		return $value !== false && array_key_exists( $value, $this->options ) ? $value : null;
	}

	public function getValue() {
		$return = '';
		if ( $value  = $this->value ) {
			$return = $this->options[$this->value];
		}
		$tag = sprintf( 'wpdw_get_%s_%s_value', $this->domain, $this->name );
		return apply_filters( $tag, $return, $value );
	}

}
