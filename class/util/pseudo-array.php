<?php
namespace WPDW\Util;

/**
 * For array behavior object (Readonly - If necessary, overwrite methods)
 */
abstract class Pseudo_Array implements \ArrayAccess, \IteratorAggregate {

	/**
	 * @var array
	 */
	protected $arguments;

	/**
	 * Constructor
	 */
	public function __construct( Array $args ) {
		$this->arguments = $args;
	}

	/**
	 * ArrayAccess methods
	 */
	public function offsetSet( $offset, $value ) {
		// Do nothing
	}
	public function offsetExists( $offset ) {
		return isset( $this->arguments[$offset] );
	}
	public function offsetUnset( $offset ) {
		// Do nothing
	}
	public function offsetGet($offset) {
		return isset( $this->arguments[$offset] ) ? $this->arguments[$offset] : null;
	}

	/**
	 * IteratorAggregate methods
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->arguments );
	}

}
