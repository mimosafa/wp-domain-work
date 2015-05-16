<?php
namespace WPDW\Device\Asset;

abstract class asset_abstract {

	abstract protected function output_filter( $value );
	abstract protected function input_filter( $value, \WP_Post $post );

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
			unset( $this->glue );
	}

	/**
	 * Get value
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\type_{$type}::output_filter()
	 *
	 * @param  int|WP_Post $post
	 * @return mixed
	 */
	public function get( $post ) {
		if ( ! $this->model || ! $post = get_post( $post ) )
			return;
		$get = 'get_' . $this->model;
		return $this->output_filter( $this->$get( $post ) );
	}

	/**
	 * Update value
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\type_{$type}::input_filter()
	 *
	 * @param  int|WP_Post $post
	 * @param  mixed $value
	 */
	public function update( $post, $value ) {
		if ( ! $this->model || ! $post = get_post( $post ) )
			return;
		$update = 'update_' . $this->model;
		return $this->$update( $post, $this->input_filter( $value, $post ) );
	}

	/**
	 * Return recipe of the asset as array
	 *
	 * @access public
	 *
	 * @param  int|WP_Post $post
	 * @return array
	 */
	public function get_recipe( $post ) {
		return array_merge( get_object_vars( $this ), [ 'value' => $this->get( $post ) ] );
	}

	/**
	 * Array_walk callback function used in WPDW\Device\type_{$type}::prepare_arguments()
	 *
	 * @access protected
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'name' ) :
			$arg = $asset;
		elseif ( in_array( $key, [ 'label', 'description', 'glue' ], true ) ) :
			$arg = self::sanitize_string( $arg );
		elseif ( in_array( $key, [ 'multiple', 'required', 'readonly' ], true ) ) :
			$arg = self::validate_boolean( $arg, false );
		elseif ( in_array( $key, [ 'deps' ], true ) ) :
			$arg = filter_var( $arg, \FILTER_VALIDATE_BOOLEAN, \FILTER_REQUIRE_ARRAY );
		elseif ( $key === 'model' ) :
			$method = 'get_' . $arg;
			$arg = method_exists( __NAMESPACE__ . '\\asset_models', $method ) ? $arg : null;
		elseif ( $key !== 'type' ) :
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
