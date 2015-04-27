<?php
namespace WPDW\Device\Module;

trait Initializer {

	/**
	 * 
	 * @access public
	 * @param  array $args
	 * @return (void)
	 */
	public static function init( Array $args ) {
		static $self = null;
		if ( ! $self ) {
			$class = __CLASS__;
			$self = new $class( $args );
		}
	}

}
