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
	protected $admin_form_style = 'block';

	/**
	 * @var boolean
	 */
	protected $inline;

	/**
	 * @access public
	 *
	 * @uses   WPDW\_property()
	 *
	 * @param  int|WP_Post $post
	 * @param  array $value
	 * @return (void)
	 */
	public function update( $post, $value ) {
		$value = $this->filter_input( $value );
		$property = \WPDW\_property( $this->domain );
		foreach ( $value as $asset => $val ) {
			$property->$asset->update( $post, $val );
		}
	}

	public function print_column( $value, $post_id ) {
		//
	}

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'assets' ) :
			$arg = self::flatten( filter_var( $arg, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY ), true );
		elseif ( in_array( $key, [ 'glue' ], true ) ) :
			$arg = filter_var( $arg, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		elseif ( $key ==='inline' ) :
			$arg = filter_var( $arg, \FILTER_VALIDATE_BOOLEAN );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

}
