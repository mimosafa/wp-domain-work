<?php
namespace WPDW\Device\Asset;

class type_boolean implements asset_interface {
	use asset_methods, asset_vars, asset_models;

	protected $model = 'post_meta';

	// yet

	/**
	 * @see WPDW\Device\property::prepare_assets()
	 *
	 * @param  mixed  $arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	public static function arguments_walker( &$arg, $key, $asset ) {
		// yet
		self::common_arguments_walker( $arg, $key, $asset );
	}

	public static function arguments_filter( &$args ) {
		// yet
		self::common_arguments_filter( $args );
	}

	public function filter( $var ) {
		return filter_var( $var, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
	}

	public function print_column( $value, $post_id ) {
		$output = $this->label ?: (string) $value;
		return $value ? $output : '';
	}

}
