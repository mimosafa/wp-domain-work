<?php
namespace WPDW\Device\Asset;

abstract class asset_assets extends asset_abstract {
	use \WPDW\Util\Array_Function;

	/**
	 * @var array
	 */
	protected $assets;

	/**
	 * @var string
	 */
	protected $glue = ' ';

	/**
	 * @var string
	 */
	protected $format;

	/**
	 * @var string
	 */
	protected $admin_form_style;

	/**
	 * @access public
	 *
	 * @param  mixed $array
	 * @return array|null
	 */
	public function filter_input( $array ) {
		if ( ! is_array( $array ) )
			return null;

		$property = \WPDW\_property( $this->domain );
		$return = [];
		foreach ( $this->assets as $asset ) {
			$val = isset( $array[$asset] ) ? $array[$asset] : '';
			$assetObj = $property->$asset;
			$return[$asset] = $assetObj->filter_input( $val );
		}
		return $return;
	}

	/**
	 * Get value
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_abstract::check_dependency()
	 *
	 * @param  int|WP_Post $post
	 * @return mixed
	 */
	public function get( $post ) {} // @todo

	/**
	 * Update value
	 *
	 * @access public
	 *
	 * @uses   WPDW\_property()
	 *
	 * @param  int|WP_Post $post
	 * @param  array $value
	 * @return (void)
	 */
	public function update( $post, $value ) {
		if ( ! is_array( $value ) )
			return;
		if ( ! $this->check_dependency( $post ) )
			return null;
		$value = $this->filter_input( $value );
		$property = \WPDW\_property( $this->domain );
		foreach ( $value as $asset => $val ) {
			$property->$asset->update( $post, $val );
		}
	}

	/**
	 * Return recipe of the asset as array
	 *
	 * @access public
	 *
	 * @uses   WPDW\_property()
	 *
	 * @param  int|WP_Post $post
	 * @return array
	 */
	public function get_recipe( $post ) {
		$recipe = get_object_vars( $this );
		array_walk( $recipe['assets'], function( &$asset, $i, $post ) {
			$asset = \WPDW\_property( $this->domain )->$asset->get_recipe( $post );
		}, $post );
		return $recipe;
	}

	public function print_column( $value, $post_id ) {
		//
	}

	/**
	 * Array_walk callback function
	 *
	 * @see    WPDW\Device\asset_vars::prepare_arguments()
	 *
	 * @access protected
	 *
	 * @uses   WPDW\Util\Array_Function::flatten()
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'assets' ) :
			/**
			 * @var array $assets
			 */
			$arg = self::flatten( filter_var( $arg, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY ), true );
		elseif ( in_array( $key, [ 'glue' ], true ) ) :
			/**
			 * @var string $glue
			 */
			$arg = filter_var( $arg, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * @access protected
	 *
	 * @param  array $args
	 * @return boolean
	 */
	protected static function is_met_requirements( Array $args ) {
		return empty( $args['assets'] ) ? false : true;
	}

}
