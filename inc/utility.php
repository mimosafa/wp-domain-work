<?php

namespace utility;

/**
 * Get namespace of class object, uses in trait.
 *
 * @see https://gist.github.com/mimosafa/07d25da4523acb105358
 * @see http://stackoverflow.com/questions/13932289/get-php-class-namespace-dynamically
 *
 * @param  object $object
 * @return string
 */
function getObjectNamespace( $object ) {
	if ( !is_object( $object ) ) {
		return false; // throw error
	}
	$class = get_class( $object );
	return substr( $class, 0, strrpos( $class, '\\' ) );
}

/**
 * Get the class name without the namespace of objects
 *
 * @param  object
 * @return string
 */
function getEndOfClassName( $object ) {
	if ( !is_object( $object ) ) {
		return false; // throw error
	}
	$class = get_class( $object );
	return substr( $class, strrpos( $class, '\\' ) + 1 );
}

/**
 * Confirm that given array is array or associative array, return true if the array.
 *
 * @see http://qiita.com/Hiraku/items/721cc3a385cb2d7daebd
 *
 * @param  array $array
 * @return bool
 */
function is_vector( $array ) {
	if ( !is_array( $array ) ) {
		return false;
	}
	return array_values( $array ) === $array;
}

/**
 * Merging a multi-dimensional array
 *
 * @param  array $a
 * @param  array $b
 * @return array
 */
function md_array_merge( Array $a, Array $b ) {
	foreach ( $a as $key => $val ) {
		if ( !isset( $b[$key] ) ) {
			$b[$key] = $val;
		} elseif ( is_array( $val ) ) {
			$b[$key] = \utility\md_array_merge( $val, $b[$key] );
		}
	}
	return $b;
}
