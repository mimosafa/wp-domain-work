<?php
namespace WPDW\Device\Asset;

class type_chain extends asset_abstract implements asset {
	use asset_trait;

	/**
	 * @var string WPDW\Device\Asset\type_{$type}::$name
	 */
	protected $ref;

	/**
	 * @var string
	 */
	protected $ref_domain;

	/**
	 * @var string
	 */
	protected $ref_asset;

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
		if ( in_array( $key, [ 'ref', 'ref_asset' ], true ) ) :
			$arg = provision::is_valid_asset_name_string( $arg ) ? $arg : null;
		elseif ( $key === 'ref_domain' ) :
			$arg = \WPDW\_is_domain( $arg ) ? $arg : null;
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
		return $args['ref'] && $args['ref_domain'] && $args['ref_asset'];
	}

	/**
	 * @todo  !!!
	 * 
	 * $refer validation
	 *
	 * @access public
	 */
	protected function _validate_after_constructed() {
		$maybeAsset = $this->ref;
		if ( ! $ref = \WPDW\_property( $this->domain )->$maybeAsset )
			return false;
		//
		return true;
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

	public function admin_form_element() {
		var_dump( $this );
	}
	
}
