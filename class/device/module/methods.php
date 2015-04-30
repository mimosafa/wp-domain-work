<?php
namespace WPDW\Device\Module;

trait Methods {

	/**
	 * @access private
	 *
	 * @param  string $arg
	 * @return boolean
	 */
	private function is( $arg ) {
		return property_exists( __CLASS__, $arg ) && $arg;
	}

	/**
	 * @access private
	 *
	 * @param  string $arg
	 * @return boolean
	 */
	private function isDefined( $arg ) {
		if ( ! $this->is( $arg ) )
			return false;
		if ( ! is_array( $this->$arg ) ) {
			$message = '[ Definition Error: <strong>%s::$%s</strong> ] The variable must be assigned by non-empty array. <strong>%s</strong> is given.';
			$supplied = ucwords( gettype( $this->$arg ) );
			\WPDW\WP\admin_notices::error( sprintf( __( $message ), __CLASS__, $arg, $supplied ) );
			return false;
		}
		return true;
	}

}
