<?php

namespace property;

class post_parent {

	public $name;

	public $label;

	protected $_type = 'post_parent';

	public function __construct( $var, Array $arg ) {
		
		if ( !is_string( $var ) ) {
			return null;
		}

		/**
		 * property name
		 */
		$this -> name = $var;

		/**
		 * Property label
		 */
		$this -> label = array_key_exists( 'label', $arg ) && is_string( $arg['label'] )
			? $arg['label']
			: ucwords( str_replace( [ '_', '-' ], ' ', trim( $var ) ) );
		;

		/**
		 * Description
		 */
		if ( array_key_exists( 'description', $arg ) ) {
			$this -> description = $arg['description'];
		}

	}

	public function getArray() {
		return get_object_vars( $this );
	}

}
