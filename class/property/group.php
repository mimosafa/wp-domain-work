<?php

namespace property;

/**
 *
 */
class group extends base {

	public $properties = [];

	public function __construct( $var, $arg ) {

		parent::__construct( $var, $arg );

	}

	/**
	 * Set elements' property.
	 * ~ This method will be used in class '\(domain)\properties'.
	 *
	 * @param string $name
	 * @param object $value \property\(type)
	 */
	public function set_element( $name, $value ) {
		if ( !$value || !is_object( $value ) )
			return;
		$this -> properties[$name] = $value;
	}

	public function getJson() {
		return json_encode( $this -> getArray() );
	}

	public function getArray() {
		$name = $this -> name;
		$label = $this -> label;
		if ( property_exists( $this, 'description' ) ) {
			$description = $this -> description;
		}
		$_type = $this -> _type;
		$_properties = [];
		foreach ( $this -> properties as $obj ) {
			$_properties[] = $obj -> getArray();
		}
		return compact( 'name', 'label', 'description', '_type', '_properties' );
	}

}
