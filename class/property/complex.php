<?php

namespace property;

abstract class complex extends basic {

	public $properties = [];

	public function __construct( $var, $args ) {
		if ( ! parent::__construct( $var, $args ) ) {
			return false;
		}
		if ( ! array_key_exists( 'elements', $args ) || ! $args['elements'] ) {
			return false;
		}
	}

	/**
	 * Set elements' property.
	 * ~ This method will be used in class '\(domain)\properties'.
	 *
	 * @param string $name
	 * @param object $obj \property\(type)
	 */
	public function set_element( $name, $obj ) {
		if ( ! $obj || ! is_object( $obj ) ) {
			return;
		}
		$this->properties[$name] = $obj;
	}

	public function getArray() {
		if ( ! $this->properties ) {
			return []; // error
		}
		$name  = $this->name;
		$label = $this->label;
		if ( property_exists( $this, 'description' ) ) {
			$description = $this->description;
		}
		$_type = $this->_type;
		$_properties = [];
		foreach ( $this->properties as $obj ) {
			$_properties[] = $obj->getArray();
		}
		return compact( 'name', 'label', 'description', '_type', '_properties' );
	}

}
