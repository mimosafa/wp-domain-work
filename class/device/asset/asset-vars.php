<?php
namespace WPDW\Device\Asset;

trait asset_vars {

	/**
	 * @var string
	 */
	protected $domain;
	protected $type;
	protected $name;
	protected $model;
	protected $label;
	protected $description;

	/**
	 * @var boolean
	 */
	protected $multiple = false;
	protected $required = false;
	protected $readonly = false;

	/**
	 * @var array
	 */
	protected $deps;

	/**
	 * @var string
	 */
	protected $glue = ', ';

	/**
	 * WP_Domain\{$domain}\property::$assets arguments provisioner
	 *
	 * @see  WPDW\Device\property::prepare_assets()
	 *
	 * @access public
	 *
	 * @param  array  &$args
	 * @param  string $asset
	 */
	public static function prepare_arguments( Array &$args, $asset ) {
		static $defaults;
		if ( ! $defaults ) {
			$defaults = get_class_vars( __CLASS__ );
			// Unset static vars
			unset( $defaults['_properties'] );
		}
		$args = array_merge( $defaults, $args );
		array_walk( $args, __CLASS__ . '::arguments_walker', $asset );

		if ( ! $args['label'] )
			$args['label'] = ucwords( str_replace( '_', ' ', $args['name'] ) );
	}

}
