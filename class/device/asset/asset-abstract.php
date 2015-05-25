<?php
namespace WPDW\Device\Asset;

abstract class asset_abstract implements asset {

	/**
	 * @var array
	 */
	protected $deps;

	/**
	 * @var string
	 */
	protected $delimiter = ', ';

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
	 * @param  WP_Post $post
	 * @return boolean
	 */
	protected function check_dependency( \WP_Post $post ) {
		if ( ! $this->deps )
			return true;
		$property = \WPDW\_property( $this->domain );
		foreach ( $this->deps as $asset => $arg ) {
			if ( ! is_array( $arg ) ) {
				if ( filter_var( $arg, \FILTER_VALIDATE_BOOLEAN ) && ! $property->$asset->get( $post ) ) {
					return false;
					break;
				}
			} else {
				//
			}
		}
		return true;
	}

	/**
	 * @access protected
	 *
	 * @see    WPDW\Device\asset_vars::prepare_arguments()
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'name' ) :
			/**
			 * Asset name
			 * @var string $name
			 */
			$arg = $asset;
		elseif ( in_array( $key, [ 'label', 'description', 'delimiter' ], true ) ) :
			/**
			 * @var string $label|$description|$delimiter
			 */
			$arg = filter_var( $arg, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		elseif ( in_array( $key, [ 'multiple', 'required', 'readonly' ], true ) ) :
			/**
			 * @var boolean $multiple|$required|$readonly
			 */
			$arg = filter_var( $arg, \FILTER_VALIDATE_BOOLEAN );
		elseif ( in_array( $key, [ 'deps' ], true ) ) :
			/**
			 * @var array $deps
			 */
			$arg = filter_var( $arg, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY );
		elseif ( in_array( $key, [ 'domain', 'type', ], true ) ) :
			/**
			 * @var string $domain|$type
			 */
			$arg = filter_var( $arg );
		else :
			$arg = null;
		endif;
	}

}
