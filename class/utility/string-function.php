<?php

namespace WP_Domain_Work\Utility;

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

}
