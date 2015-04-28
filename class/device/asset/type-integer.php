<?php
namespace WPDW\Device\Asset;

class type_integer implements asset_interface {
	use asset_method, asset_vars, asset_model;

	private $model = 'post_meta';
	private $min = null;
	private $max = null;

	/**
	 * @see WPDW\Device\property::prepare_assets()
	 *
	 * @param  mixed  $arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	public static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'min' && isset( $arg ) ) :
			$arg = self::validate_integer( $arg );
		elseif ( $key === 'max' && isset( $arg ) ) :
			$arg = self::validate_integer( $arg );
		else :
			// Common
			self::common_arguments( $arg, $key, $asset );
		endif;
	}

	public function filter( $var ) {
		$options = [ 'default' => null ];
		if ( $this->min !== null )
			$options['min_range'] = $this->min;
		if ( $this->max !== null )
			$options['max_range'] = $this->max;
		if ( count( $options ) === 3 && $this->min > $this->max ) {
			unset( $options['min_range'] );
			unset( $options['max_range'] );
		}
		return filter_var( $var, \FILTER_VALIDATE_INT, [ 'options' => $options ] );
	}

}
