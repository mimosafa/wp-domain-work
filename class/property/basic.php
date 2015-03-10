<?php

namespace WP_Domain_Work\Property;

use \WP_Domain_Work\Utility as Util;

/**
 * Abstract class for basic property class
 */
abstract class basic {

	/**
	 * WP_Domain_Work\Module\properties ですべてのプロパティインスタンスに追加されている
	 * @var string
	 */
	public $domain;

	/**
	 * @var string
	 */
	public $name, $label;

	/**
	 * @var string
	 */
	protected $_type;

	abstract public function getArray();

	public function __construct( $var, Array $arg ) {
		if ( ! $var || ! is_string( $var )  ) {
			return false;
		}
		/**
		 * Define type name by class name string
		 * @uses \WP_Domain_Work\Utility\classname::getClassName
		 */
		$this->_type  = Util\String_Function::getClassName( $this );
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
