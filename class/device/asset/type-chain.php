<?php
namespace WPDW\Device\Asset;

class type_chain extends asset_abstract implements asset {
	use asset_trait;

	/**
	 * @var string WPDW\Device\Asset\type_{$type}::$name
	 */
	protected $refer;

	/**
	 * @var string
	 */
	protected $asset;

	/**
	 * Array_walk callback function
	 *
	 * @access protected
	 *
	 * @see    WPDW\Device\asset_trait::prepare_arguments()
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( in_array( $key, [ 'refer', 'asset' ], true ) ) :
			$arg = provision::is_valid_asset_name_string( $arg ) ? $arg : null;
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * @access protected
	 *
	 * @see    WPDW\Device\asset_trait::prepare_arguments()
	 *
	 * @param  array $args
	 * @return boolean
	 */
	protected static function is_met_requirements( Array $args ) {
		_var_dump( $args );
		return ! $args['refer'] || ! $args['asset'];
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
	public function get( $post ) {
		//
	}
	
}
