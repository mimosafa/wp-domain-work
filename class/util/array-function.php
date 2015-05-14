<?php
namespace WPDW\Util;

trait Array_Function {

	/**
	 * Merging a multi-dimensional array
	 *
	 * @param  array $a
	 * @param  array $b
	 * @return array
	 */
	public static function md_merge( Array $a, Array $b ) {
		foreach ( $a as $key => $val ) {
			if ( ! isset( $b[$key] ) ) {
				$b[$key] = $val;
			} else if ( is_array( $val ) ) {
				$b[$key] = self::md_merge( $val, $b[$key] );
			}
		}
		return $b;
	}

	/**
	 * Recursively flatten array
	 *
	 * @todo ...How this work ;)
	 *
	 * @see  http://blog.code-life.net/blog/2014/09/23/php-array-flatten/
	 *
	 * @uses  RecursiveIteratorIterator
	 * @uses  RecursiveArrayIterator
	 *
	 * @param  array $array
	 * @param  boolean $filter_null
	 * @return array
	 */
	public static function flatten( Array $array, $filter_null = false ) {
		$return = iterator_to_array( new \RecursiveIteratorIterator ( new \RecursiveArrayIterator( $array ) ), false );
		return $filter_null ? array_filter( $return, function( $var ) { return $var !== null; } ) : $return;
	}

}
