<?php
namespace WPDW\Device\Asset;

class type_datetime implements asset_interface {
	use asset_methods, asset_vars, asset_models;

	protected $model = 'post_meta';

	protected $input_type = 'datetime_local';

	protected $input_format  = 'Y-m-d H:i:s';
	protected $output_format = 'Y-m-d H:i:s';

	protected $min = null;
	protected $max = null;

	/**
	 * @see WPDW\Device\property::prepare_assets()
	 *
	 * @param  mixed  $arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	public static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'input_type' && isset( $arg ) ) :
			static $typeLists = [ 'datetime_local', 'date', 'time' ];
			$arg = in_array( $arg, $typeLists, true ) ? $arg : $typeLists[0];
		elseif ( in_array( $key, [ 'input_format', 'output_format' ], true ) ) :
			// yet
		elseif ( in_array( $key, [ 'min', 'max'], true ) ) :
			// yet

		else :
			self::common_arguments_walker( $arg, $key, $asset );
		endif;
	}

	public static function arguments_filter( &$args ) {
		// yet
		self::common_arguments_filter( $args );
	}

	public function filter( $var ) {
		// yet
		return filter_var( $var /**/ );
	}

	public function print_column( $value, $post_id ) {
		// yet
		return esc_html( $value );
	}

}
