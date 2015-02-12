<?php

namespace property;

/**
 * Abstract class for basic property class
 */
abstract class basic {

	/**
	 * \module\properties ですべてのプロパティインスタンスに追加されている
	 */
	public $domain;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $label;

	/**
	 * @var string
	 */
	protected $_type;

	abstract public function getArray();

	public function __construct( $var, Array $arg ) {
		if ( ! $var || ! is_string( $var )  ) {
			return false;
		}
		$this->_type  = \utility\getEndOfClassname( $this );
		$this->name   = $var;
		$this->label  = array_key_exists( 'label', $arg ) && is_string( $arg['label'] )
			? $arg['label']
			: ucwords( str_replace( [ '_', '-' ], ' ', trim( $var ) ) );
		;
		if ( array_key_exists( 'description', $arg ) ) {
			$this->description = $arg['description'];
		}
		return true;
	}

}
