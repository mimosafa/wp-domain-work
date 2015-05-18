<?php
namespace WPDW\Device\Asset;

abstract class asset_complex extends asset_abstract {

	/**
	 * @var array {
	 *     @type WP_Domain\{$domain}\property $domain
	 * }
	 */
	protected static $_properties = [];

	/**
	 * Constructor
	 *
	 * @uses   WPDW\Device\Asset\asset_abstract::__construct()
	 *
	 * @param  array $args
	 * @return (void)
	 */
	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( ! isset( self::$_properties[$this->domain] ) )
			self::$_properties[$this->domain] = \WPDW\_property_object( $this->domain );
	}

	public function get( $post ) {}
	public function update( $post, $value ) {}

	/**
	 * Return recipe of the asset as array
	 *
	 * @access public
	 *
	 * @param  int|WP_Post $post
	 * @return array
	 */
	public function get_recipe( $post ) {
		//
	}

	/**
	 * Array_walk callback function
	 *
	 * @see    WPDW\Device\asset_vars::prepare_arguments()
	 *
	 * @access protected
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( in_array( $key, [ 'glue' ], true ) ) :
			$arg = self::sanitize_string( $arg );
		elseif ( $key === 'assets' ) :
			$arg = filter_var( $arg, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY );
		elseif ( $key === 'model' ) :
			$method = 'get_' . $arg;
			$arg = method_exists( __CLASS__, $method ) ? $arg : null;
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	protected static function is_met_requirements( Array $args ) {
		return true; // @todo
	}

	/**
	 * @access protected
	 */
	protected function get_assets( \WP_Post $post ) {
		if ( ! array_key_exists( $this->domain, self::$_properties ) ) {
			self::$_properties[$this->domain] = \WPDW\_property_object( $this->domain );
		}
		$property =& $this->_get_property();
		/*
		$return = [];
		foreach ( $this->assets as $asset ) {
			$return[] = (object) $property->$asset->get_recipe( $post );
		}
		return $return;
		*/
	}

}
