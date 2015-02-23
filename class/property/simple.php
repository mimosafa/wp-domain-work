<?php

namespace WP_Domain_Work\Property;

abstract class simple extends basic {

	/**
	 * @var mixed
	 */
	public $value;

	public $prefix;
	public $safix;

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
	protected $_unique = false;

	abstract public function filter( $value );

	#abstract public function getValue();

	public function __construct( $var, Array $arg ) {
		if ( ! parent::__construct( $var, $arg ) ) {
			return false;
		}
		if ( ! array_key_exists( 'model', $arg ) ) {
			return false;
		}
		$this->_model = $arg['model'];
		if ( array_key_exists( 'required', $arg ) && $arg['required'] === true ) {
			$this->_required = true;
		}
		if ( array_key_exists( 'readonly', $arg ) && $arg['readonly'] === true ) {
			$this->_readonly = true;
		}
		if ( array_key_exists( 'multiple', $arg ) && $arg['multiple'] === true ) {
			$this->_multiple = true;
		}
		if ( array_key_exists( 'unique',   $arg ) && $arg['unique']   === true ) {
			$this->_unique   = true;
		}
		if ( array_key_exists( 'prefix', $arg ) && is_string( $arg['prefix'] ) && $arg['prefix'] ) {
			$this->prefix = $arg['prefix'];
		}
		if ( array_key_exists( 'safix', $arg ) && is_string( $arg['safix'] ) && $arg['safix'] ) {
			$this->safix = $arg['safix'];
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
