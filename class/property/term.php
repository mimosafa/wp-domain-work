<?php

namespace property;

class term extends simple {

	public function __construct( $var, Array $args ) {
		if ( !parent::__construct( $var, $args ) ) {
			return false;
		}
	}

	public function filter( $value ) {
		return $value;
	}

	public function getValue() {
		$name = $this->value->name;
		$tag = sprintf( 'wpdw_get_%s_%s_value', $this->domain, $this->name );
		return apply_filters( $tag, $name, $this->value );
	}

}
