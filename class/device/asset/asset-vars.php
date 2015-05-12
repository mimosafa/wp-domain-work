<?php
namespace WPDW\Device\Asset;

trait asset_vars {

	/**
	 * @var string
	 */
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
	 * @var string
	 */
	protected $glue = ', ';

	/**
	 * Get default arguments for class construction.
	 * @access public
	 * @return array Class vars
	 */
	public static function get_defaults() {
		return get_class_vars( __CLASS__ );
	}

	/**
	 * Validate & Sanitize vars methods
	 * - Used as callback for array_walk
	 *
	 * @see WPDW\Device\Asset\{$type}::arguments_walker()
	 * @see WPDW\Device\property::__construct()
	 */

	/**
	 * Commmon in some types
	 * @access protected
	 */
	protected static function common_arguments( &$arg, $key, $asset ) {
		if ( $key === 'name' ) :
			$arg = $asset;
		elseif ( in_array( $key, [ 'label', 'description', 'glue' ], true ) ) :
			$arg = self::sanitize_string( $arg );
		elseif ( in_array( $key, [ 'multiple', 'required', 'readonly' ], true ) ) :
			$arg = self::validate_boolean( $arg, false );
		elseif ( $key === 'model' ) :
			$method = 'get_' . $arg;
			$arg = method_exists( __CLASS__, $method ) ? $arg : null;
		elseif ( $key !== 'type' ) :
			$arg = null;
		endif;
	}

	/**
	 * Sanitize string
	 * @access protected
	 * @param  string $string
	 * @return string
	 */
	protected static function sanitize_string( $string ) {
		return (string) filter_var( $string, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	}

	/**
	 * Validate integer
	 * @access protected
	 * @param  integer $int
	 * @param  integer $default Optional
	 * @param  integer $min     Optional
	 * @param  integer $max     Optional
	 * @return integer
	 */
	protected static function validate_integer( $int, $default = null, $min = null, $max = null ) {
		$options = [ 'options' => [ 'default' => null ] ];
		if ( isset( $default ) && is_int( $default ) )
			$options['options']['default'] = (int) $default;
		if ( isset( $min ) )
			$options['options']['min_range'] = (int) $min;
		if ( isset( $max ) && (int) $max > $options['options']['min_range'] )
			$options['options']['max_range'] = (int) $max;
		return (int) filter_var( $int, \FILTER_VALIDATE_INT, $options );
	}

	/**
	 * Validate boolean
	 * @access protected
	 * @param  boolean $bool
	 * @param  boolean $default
	 * @return boolean
	 */
	protected static function validate_boolean( $bool, $default = false ) {
		return filter_var( $bool, \FILTER_VALIDATE_BOOLEAN, [ 'options' => [ 'default' => $default ] ] );
	}

}
