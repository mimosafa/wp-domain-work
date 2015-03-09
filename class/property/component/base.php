<?php

namespace WP_Domain_Work\Property\component;

abstract class base {

	public $name, $label, $description, $value;
	protected $_type;

	public function __construct( $var, Array $args ) {
		if ( ! is_string( $var ) || ! $var ) {
			return false;
		}
		$this->name = $var;
		$this->label = array_key_exists( 'label', $args ) && is_string( $args['label'] ) && $args['label']
			? $arg['label']
			: ucwords( str_replace( [ '_', '-' ], ' ', trim( $var ) ) )
		;
		if ( array_key_exists( 'description', $arg ) && is_string( $args['description'] ) && $args['description'] ) {
			$this->description = $arg['description'];
		}
		return true;
	}

	abstract function getValue();
	abstract function getArray();

}
