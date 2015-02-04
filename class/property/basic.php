<?php

namespace property;

/**
 * Abstract class for basic property class
 */
abstract class basic {

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $label;

	/**
	 * @var mixed
	 */
	public $value;

	/**
	 * @var string
	 */
	protected $_type;

	/**
	 * @var string
	 */
	protected $_model;

	/**
	 * @var bool
	 */
	protected $_required = false;

	/**
	 * @var bool
	 */
	protected $_readonly = false;

	/**
	 * @var bool
	 */
	protected $_multiple = false;

	/**
	 * @var bool
	 */
	protected $_unique   = false;

	abstract public function filter( $value );

	public function __construct( $var, Array $arg ) {
		if ( !is_string( $var ) || !array_key_exists( 'model', $arg ) ) {
			return false;
		}
		$this->_type  = \utility\getEndOfClassname( $this );
		$this->_model = $arg['model'];
		$this->name   = $var;
		$this->label  = array_key_exists( 'label', $arg ) && is_string( $arg['label'] )
			? $arg['label']
			: ucwords( str_replace( [ '_', '-' ], ' ', trim( $var ) ) );
		;
		if ( array_key_exists( 'required', $arg ) && $arg['required'] === true ) {
			$this->_required = true;
		}
		if ( array_key_exists( 'multiple', $arg ) && $arg['multiple'] === true ) {
			$this->_multiple = true;
		}
		if ( array_key_exists( 'readonly', $arg ) && $arg['readonly'] === true ) {
			$this->_readonly = true;
		}
		if ( array_key_exists( 'unique',   $arg ) && $arg['unique']   === true ) {
			$this->_unique   = true;
		}
		if ( array_key_exists( 'description', $arg ) ) {
			$this->description = $arg['description'];
		}
		return true;
	}

	public function val( $value ) {
		$this->value = $this->filter( $value );
	}

	public function getModel() {
		return $this->_model;
	}

	public function getArray() {
		return get_object_vars( $this );
	}

}
