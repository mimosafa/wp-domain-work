<?php
namespace WPDW\Device\Asset;

abstract class asset_abstract implements asset {

	/**
	 * Constructor
	 *
	 * @param  array $args
	 * @return (void)
	 */
	public function __construct( Array $args ) {
		foreach ( $args as $key => $val ) {
			if ( property_exists( $this, $key ) && isset( $key ) )
				$this->$key = $val;
		}
		if ( ! $this->multiple )
			unset( $this->delimiter );
	}

	/**
	 * @access protected
	 *
	 * @see    WPDW\Device\asset_vars::prepare_arguments()
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'name' ) :
			$arg = $asset;
		elseif ( in_array( $key, [ 'label', 'description', 'delimiter' ], true ) ) :
			$arg = self::sanitize_string( $arg );
		elseif ( in_array( $key, [ 'multiple', 'required', 'readonly' ], true ) ) :
			$arg = self::validate_boolean( $arg, false );
		elseif ( in_array( $key, [ 'deps' ], true ) ) :
			$arg = filter_var( $arg, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY );
		elseif ( $key === 'model' ) :
			$method = 'get_' . $arg;
			$arg = method_exists( __NAMESPACE__ . '\\asset_models', $method ) ? $arg : null;
		elseif ( ! in_array( $key, [ 'domain', 'type', ], true ) ) :
			$arg = null;
		endif;
	}

	/**
	 * Sanitize string
	 *
	 * @access protected
	 *
	 * @param  string $string
	 * @return string
	 */
	protected static function sanitize_string( $string ) {
		return (string) filter_var( $string, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	}

	/**
	 * Validate integer
	 *
	 * @access protected
	 *
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
		return filter_var( $int, \FILTER_VALIDATE_INT, $options );
	}

	/**
	 * Validate boolean
	 *
	 * @access protected
	 *
	 * @param  boolean $bool
	 * @param  boolean $default
	 * @return boolean
	 */
	protected static function validate_boolean( $bool, $default = false ) {
		return filter_var( $bool, \FILTER_VALIDATE_BOOLEAN, [ 'options' => [ 'default' => $default ] ] );
	}

}
