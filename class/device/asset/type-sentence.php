<?php
namespace WPDW\Device\Asset;

class type_sentence extends asset_complex {
	use asset_vars, \WPDW\Util\Array_Function;

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
	protected $admin_form_display = 'block';

	/**
	 * @var boolean
	 */
	protected $inline;

	public function update( $post, $value ) {
		$def = array_map( function() {
			return \FILTER_DEFAULT;
		}, array_flip( $this->assets ) );
		$value = filter_var_array( $value, $def );
		foreach ( $value as $asset => $val ) {
			$this->_property()->$asset->update( $post, $val );
		}
	}

	public function print_column( $value, $post_id ) {
		//
	}

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'assets' ) :
			$arg = self::flatten( filter_var( $arg, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY ), true );
		elseif ( in_array( $key, [ 'glue' ], true ) ) :
			$arg = self::sanitize_string( $arg );
		elseif ( $key ==='inline' ) :
			$arg = filter_var( $arg, \FILTER_VALIDATE_BOOLEAN );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

}
