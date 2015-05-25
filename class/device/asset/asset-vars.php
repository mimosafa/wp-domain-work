<?php
namespace WPDW\Device\Asset;

/**
 * Common vars of asset, and common static method
 */
trait asset_vars {

	/**
	 * @var string
	 */
	protected $domain;
	protected $type;
	protected $name;
	protected $label;
	protected $description;

	/**
	 * @var boolean
	 */
	protected $multiple = false;
	protected $required = false;
	protected $readonly = false;

	/**
	 * Arguments (WP_Domain\{$domain}\property::$assets) provisioner
	 *
	 * @access public
	 *
	 * @see    WPDW\Device\property::prepare_assets()
	 *
	 * @uses   WPDW\Device\Asset\type_{$type}::is_met_requirements()
	 * @see    WPDW\Device\Asset\asset_{simple|complex}::is_met_requirements()
	 *
	 * @param  array  &$args
	 * @param  string $asset
	 */
	public static function prepare_arguments( Array &$args, $asset ) {
		static $defaults;
		if ( ! $defaults ) {
			$defaults = get_class_vars( __CLASS__ );
			unset( $defaults['_properties'] ); // unset static vars
		}
		$args = array_merge( $defaults, $args );
		array_walk( $args, __CLASS__ . '::arguments_walker', $asset );

		if ( ! self::is_met_requirements( $args ) ) {
			$args = null;
			return;
		}
		
		if ( ! $args['label'] )
			$args['label'] = ucwords( str_replace( '_', ' ', $args['name'] ) );
	}

}
