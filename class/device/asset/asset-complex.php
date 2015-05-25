<?php
namespace WPDW\Device\Asset;

abstract class asset_complex extends asset_abstract {

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
		// ~
	}

	public function get( $post ) {}
	public function update( $post, $value ) {}

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
		if ( isset( $recipe['assets'] ) ) {
			array_walk( $recipe['assets'], function( &$asset, $i, $post ) {
				$asset = \WPDW\_property( $this->domain )->$asset->get_recipe( $post );
			}, $post );
		}
		return $recipe;
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
			/**
			 * @var string $glue
			 */
			$arg = filter_var( $arg, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		elseif ( $key === 'assets' ) :
			/**
			 * @var array $assets
			 */
			$arg = filter_var( $arg, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY );
		elseif ( $key === 'admin_form_style' ) :
			/**
			 * @var string $admin_form_style [inline|block|hide] Default: block
			 */
			$arg = in_array( $arg, [ 'inline', 'block', 'hide' ], true ) ? $arg : 'block';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	public function filter_input( Array $array ) {
		$property = \WPDW\_property( $this->domain );
		$return = [];
		foreach ( $this->assets as $asset ) {
			$val = isset( $array[$asset] ) ? $array[$asset] : '';
			$assetObj = $property->$asset;
			$return[$asset] = $assetObj->filter_input( $val );
		}
		return $return;
	}

	protected static function is_met_requirements( Array $args ) {
		return true; // @todo
	}

}
