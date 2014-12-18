<?php

namespace property;

/**
 *
 */
class boolean extends single {

	private $_true_value = 1;
	private $_false_value = null;

	protected function construct( $arg ) {
		return true;
	}

	/**
	 *
	 */
	public function filter( $value ) {
		return $value ? $this -> _true_value : $this -> _false_value;
	}

}
