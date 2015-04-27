<?php
namespace WPDW\Util;

/**
 * Singleton trait
 *
 * @see http://php.net/manual/ja/language.oop5.traits.php#108293
 */
trait Singleton {

	protected function __constract() {}

	public static function getInstance() {
		static $instance = null;
		$class = __CLASS__;
		return $instance ?: $instance = new $class();
	}

	public function __clone() {
		trigger_error( 'Cloning ' . __CLASS__ . ' is not allowed.', E_USER_ERROR );
	}

	public function __wakeup() {
		trigger_error( 'Unserializing ' . __CLASS__ . ' is not allowed.', E_USER_ERROR );
	}

}
