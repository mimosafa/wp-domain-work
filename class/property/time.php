<?php

namespace WP_Domain_Work\Property;

class time extends simple {

	public function __construct( $var, Array $arg ) {
		if ( !parent::__construct( $var, $arg ) ) {
			return false;
		}
	}

	public function filter( $value ) {
		if ( ! $value = strtotime( $value ) ) {
			return null;
		}
		return date( 'H:i', $value );
	}

}
