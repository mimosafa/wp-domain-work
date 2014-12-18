<?php

namespace property;

/**
 *
 */
class integer extends single {

	/**
	 * 0値を許可するか否か
	 *
	 * @var bool
	 */
	private $_allow_0 = false;

	/**
	 *
	 */
	protected function construct( $arg ) {

		if ( isset( $arg['allow_0'] ) && true === $arg['allow_0'] ) {
			$this -> _allow_0 = true;
		}

		return true;
	}

	/**
	 *
	 */
	public function filter( $value ) {
		// multiple
		if ( is_array( $value ) ) {
			if ( true === $this -> _multiple ) {
				static $n = 0;
				if ( 0 === $n ) {
					$n++;
					$return = [];
					foreach ( $value as $val ) {
						$return[] = $this -> filter( $val );
					}
					$n = 0;
				} else {
					// throw error 'Error: invalied value suplied.'
				}
			} else {
				// throw error 'Error: not allowed multiple.'
			}
			return $return;
		}

		if ( false === $value && 'metadata' === $this -> _model ) {
			return null;
		}

		if ( '0' === (string) $value ) {
			if ( true === $this -> _allow_0 ) {
				return 0;
			} else {
				// throw error
			}
		}

		if ( preg_match( '/\A[1-9][0-9]*\z/', $value ) ) {
			return (int) $value;
		}

		// throw error
	}

}
