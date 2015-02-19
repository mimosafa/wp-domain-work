<?php

namespace WP_Domain_Work\Utility;

trait Classname {

	/**
	 * Get namespace of class object, uses in trait.
	 *
	 * @see https://gist.github.com/mimosafa/07d25da4523acb105358
	 * @see http://stackoverflow.com/questions/13932289/get-php-class-namespace-dynamically
	 *
	 * @param  object  $object
	 * @param  integer $n (optional)
	 * @return string
	 */
	public static function getNamespace( $object, $n = 0 ) {
		if ( ! is_object( $object ) || ! is_integer( $n ) ) {
			throw new \InvalidArgumentException( 'Invalid argument.' );
		}
		$class = get_class( $object );
		$namespace = substr( $class, 0, (integer) strrpos( $class, '\\' ) );
		if ( ! $n || ! strpos( $namespace, '\\' ) ) {
			return $namespace;
		}
		$strings = explode( '\\', $namespace );
		$return = '';
		for ( $i = 0, $n; $i < count( $strings ), $n; $i++, --$n ) {
			$return .= $strings[$i] . '\\';
		}
		return rtrim( $return, '\\' );
	}

	/**
	 * Get the class name without the namespace of objects
	 *
	 * @param  object  $object
	 * @param  integer $n (optional)
	 * @return string
	 */
	public static function getClassName( $object, $n = 1 ) {
		if ( ! is_object( $object ) || ! is_integer( $n ) ) {
			throw new \InvalidArgumentException( 'Invalid argument.' );
		}
		$class = get_class( $object );
		$classname = substr( $class, strpos( $class, '\\' ) + 1 );
		if ( ! $n || ! strpos( $classname, '\\' ) ) {
			return $classname;
		}
		$strings = array_reverse( explode( '\\', $classname ) );
		$return = '';
		for ( $i = 0, $n; $i < count( $strings ), $n; $i++, --$n ) {
			$return = '\\' . $strings[$i] . $return;
		}
		return ltrim( $return, '\\' );
	}

}
