<?php
namespace WPDW\Util;

trait String_Function {

	/**
	 * Strings separated by delimiter & space convert strings array
	 *
	 * @param  string $strings
	 * @param  string $delimiter
	 * @return array
	 */
	public static function toArray( $strings, $delimiter = ',' ) {
		$array = explode( $delimiter, $strings );
		return array_map( function( $str ) {
			return trim( $str );
		}, $array );
	}

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
		if ( ! is_object( $object ) || ! is_integer( $n ) )
			throw new \InvalidArgumentException( 'Invalid argument.' );
		$class = get_class( $object );
		$namespace = substr( $class, 0, (int) strrpos( $class, '\\' ) );
		if ( ! $n || ! strpos( $namespace, '\\' ) )
			return $namespace;
		$strings = explode( '\\', $namespace );
		$return = '';
		for ( $i = 0, $n; $i < count( $strings ), $n; $i++, --$n ) {
			$return .= $strings[$i] . '\\';
		}
		return rtrim( $return, '\\' );
	}

}
