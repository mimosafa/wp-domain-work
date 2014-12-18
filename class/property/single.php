<?php

namespace property;

/**
 *
 */
abstract class single extends base {

	protected $_model;

	protected $_required = false;

	protected $_multiple = false;

	protected $_readonly = false;

	protected $_unique   = false;

	/**
	 * @param  array $arg
	 * @return bool
	 */
	abstract protected function construct( $arg );

	/**
	 * @param  mixed
	 * @return mixed
	 */
	abstract public function filter( $value );

	/**
	 * @param string $var
	 * @param array $arg
	 */
	public function __construct( $var, Array $arg ) {
		
		/**
		 * Require 'model' argument
		 */
		if ( !array_key_exists( 'model', $arg ) ) {
			return false; // throw error
		}

		/**
		 * Object constructer
		 */
		if ( !$this -> construct( $arg ) ) {
			return false;
		}

		/**
		 * Constract \property\base class
		 */
		parent::__construct( $var, $arg );

		/**
		 * model
		 */
		if ( array_key_exists( 'model', $arg ) ) {
			$this -> _model = $arg['model'];
		}

		if ( array_key_exists( 'required', $arg ) && true === $arg['required'] ) {
			$this -> _required = true;
		}

		if ( array_key_exists( 'multiple', $arg ) && true === $arg['multiple'] ) {
			$this -> _multiple = true;
		}

		if ( array_key_exists( 'readonly', $arg ) && true === $arg['readonly'] ) {
			$this -> _readonly = true;
		}

		if ( array_key_exists( 'unique', $arg ) && true === $arg['unique'] ) {
			$this -> _unique = true;
		}

	}

	/**
	 *
	 */
	public function val( $value ) {
		$this -> value = $this -> filter( $value );
	}

	/**
	 *
	 */
	public function getModel() {
		return $this -> _model;
	}

	public function getArray() {
		return get_object_vars( $this );
	}

}
