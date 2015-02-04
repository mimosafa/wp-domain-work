<?php

namespace property;

class post extends basic {

	protected $_post_type = [];

	public function __construct( $var, Array $arg ) {
		$arg['model'] = 'post';
		if ( ! parent::__construct( $var, $arg ) ) {
			return false;
		}
		if ( ! array_key_exists( 'post_type', $arg ) ) {
			return false;
		}
		$this->_post_type = $this->_post_type + (array) $arg['post_type'];
	}

	public function filter( $value ) {
		return $value;
	}

}
