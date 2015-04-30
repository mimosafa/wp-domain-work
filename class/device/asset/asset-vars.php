<?php
namespace WPDW\Device\Asset;

trait asset_vars {

	private $type;
	private $name;
	private $label;
	private $description;

	private $multiple = false;
	private $required = false;
	private $readonly = false;

	/**
	 * Get default arguments for class construction.
	 * @access public
	 * @return array Class vars
	 */
	public static function get_defaults() {
		return get_class_vars( __CLASS__ );
	}

	public function get_vars( $post ) {
		return array_merge( get_object_vars( $this ), [ 'value' => $this->get( $post ) ] );
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
	 * @access private
	 */
	private static function common_arguments( &$arg, $key, $asset ) {
		if ( $key === 'name' ) :
			$arg = $asset;
		elseif ( $key === 'label' ) :
			$arg = self::sanitize_string( $arg );
		elseif ( $key === 'description' ) :
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
	 * @access private
	 * @param  string $string
	 * @return string
	 */
	private static function sanitize_string( $string ) {
		return (string) filter_var( $string, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	}

	/**
	 * Validate integer
	 * @access private
	 * @param  integer $int
	 * @param  integer $default Optional
	 * @param  integer $min     Optional
	 * @param  integer $max     Optional
	 * @return integer
	 */
	private static function validate_integer( $int, $default = null, $min = null, $max = null ) {
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
	 * @access private
	 * @param  boolean $bool
	 * @param  boolean $default
	 * @return boolean
	 */
	private static function validate_boolean( $bool, $default = false ) {
		return filter_var( $bool, \FILTER_VALIDATE_BOOLEAN, [ 'options' => [ 'default' => $default ] ] );
	}

}
