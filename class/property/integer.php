<?php

namespace property;

/**
 *
 */
class integer extends basic {

	/**
	 * 0値を許可するか否か
	 *
	 * @var bool
	 */
	protected $_allow_0 = false;

	public function __construct( $var, Array $arg ) {
		if ( !parent::__construct( $var, $arg ) ) {
			return false;
		}
		if ( array_key_exists( 'allow_0', $arg )  && $arg['allow_0'] === true ) {
			$this->_allow_0 = true;
		}
	}

	public function filter( $value ) {
		if ( $value === false && $this->_model === 'metadata' ) {
			return null;
		}
		if ( strval( $value ) === '0' ) {
			return $this->_allow_0 ? 0 : null;
		}
		return preg_match( '/\A[1-9][0-9]*\z/', $value ) ? intval( $value ) : null;
	}

	/*
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
	*/

}
