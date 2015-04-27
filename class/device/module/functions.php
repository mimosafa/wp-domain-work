<?php
namespace WPDW\Device\Module;

trait Functions {

	/**
	 * @access private
	 *
	 * @param  string $args
	 * @return boolean
	 */
	private function isDefined( $args ) {
		if ( ! property_exists( __CLASS__, $args ) )
			return false;
		if ( ! is_array( $this->$args ) || ! $this->$args ) {
			$message = '[ Definition Error: <strong>%s::$%s</strong> ] The variable must be assigned by non-empty array. <strong>%s</strong> is given.';
			$supplied = strtolower( gettype( $this->$args ) ) === 'array' ? 'Empty array' : ucwords( gettype( $this->$args ) ); 
			\WPDW\WP\admin_notices::error( sprintf( __( $message ), __CLASS__, $args, $supplied ) );
			return false;
		}
		return true;
	}

}
