<?php

namespace WP_Domain_Work\Utility;

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
			if ( !isset( $b[$key] ) ) {
				$b[$key] = $val;
			} elseif ( is_array( $val ) ) {
				$b[$key] = self::md_merge( $val, $b[$key] );
			}
		}
		return $b;
	}

}
