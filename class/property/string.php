<?php

namespace property;

/**
 *
 */
class string extends single {

	private $_multi_byte = false;

	protected function construct( $arg ) {

		if ( array_key_exists( 'multibyte', $arg ) && true === $arg['multibyte'] ) {
			$this -> _multi_byte = true;
		}

		return true;

	}

	/**
	 *
	 */
	public function filter( $value ) {

		return $value;

		// throw error
	}

}
