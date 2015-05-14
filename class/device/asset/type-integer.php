<?php
namespace WPDW\Device\Asset;

class type_integer implements asset_interface {
	use asset_methods, asset_vars, asset_models;

	/**
	 * @var string
	 */
	protected $model = 'post_meta';

	/**
	 * @var int|null
	 */
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
		if ( $key === 'min' && isset( $arg ) ) :
			$arg = self::validate_integer( $arg );
		elseif ( $key === 'max' && isset( $arg ) ) :
			$arg = self::validate_integer( $arg );
		else :
			// Common
			self::common_arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * @see WPDW\Device\property::prepare_assets()
	 *
	 * @param  mixed  &$arg
	 * @return (void)
	 */
	public static function arguments_filter( &$args ) {
		if ( $args['min'] > $args['max'] ) :
			$args['min'] = $args['max'] = null;
		else :
			self::common_arguments_filter( $args );
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

	/**
	 * Print value in list table column - Hooked on '_wpdw_{$name}_column'
	 *
	 * @access public
	 *
	 * @see    WPDW\Device\Admin\posts_column::column_callback()
	 *
	 * @param  mixed $value
	 * @param  int   $post_id
	 * @return string
	 */
	public function print_column( $value, $post_id ) {
		return esc_html( $value );
	}

}
