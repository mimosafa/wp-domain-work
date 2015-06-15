<?php
namespace WPDW\Device\Asset;

trait asset_trait {

	/**
	 * Arguments (WP_Domain\{$domain}\property::$assets) provisioner
	 *
	 * @access public
	 *
	 * @see    WPDW\Device\property::prepare_assets()
	 *
	 * @uses   WPDW\Device\Asset\type_{$type}::is_met_requirements()
	 * @see    WPDW\Device\Asset\asset_{simple|assets}::is_met_requirements()
	 *
	 * @param  array  &$args
	 * @param  string $asset
	 */
	public static function prepare_arguments( Array &$args, $asset ) {
		static $defaults;
		if ( ! $defaults )
			$defaults = get_class_vars( __CLASS__ );
		$args = array_merge( $defaults, $args );
		array_walk( $args, __CLASS__ . '::arguments_walker', $asset );

		if ( ! self::is_met_requirements( $args ) ) {
			$args = null;
			return;
		}
		
		// Label
		if ( ! $args['label'] )
			$args['label'] = ucwords( trim( str_replace( '_', ' ', $args['name'] ) ) );
	}

}
