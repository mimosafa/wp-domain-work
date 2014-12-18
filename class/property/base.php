<?php

namespace property;

/**
 *
 */
class base {

	public $name;

	public $label;

	protected $_type;

	/**
	 * @param string $var
	 * @param array $arg
	 */
	public function __construct( $var, Array $arg ) {

		if ( !is_string( $var ) ) {
			return null; // throw error
		}

		/**
		 * type
		 */
		$this -> _type = \utility\getEndOfClassname( $this );

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

}
