<?php
namespace WPDW\Device\Asset;

class type_string implements asset_interface {
	use asset_methods, asset_vars, asset_models;

	private $model = 'post_meta';
	private $multibyte = true;
	private $min_len = 0;
	private $max_len = 0;
	private $regexp = '';

	/**
	 * @see WPDW\Device\property::prepare_assets()
	 *
	 * @param  mixed  $arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	public static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'multibyte' ) :
			$arg = self::validate_boolean( $arg, true );
		elseif ( $key === 'min_len' ) :
			$arg = self::validate_integer( $arg, 0, 0 );
		elseif ( $key === 'max_len' ) :
			$arg = self::validate_integer( $arg, 0, 1 );
		elseif ( $key === 'regexp' && $arg ) :
			$arg = @preg_match( $pattern, '' ) !== false ? $arg : '';
		else :
			// Common
			self::common_arguments( $arg, $key, $asset );
		endif;
	}

	public function filter( $var ) {
		if ( $this->regexp ) {
			if ( ! preg_match( $this->regexp, $var ) )
				return null;
		}
		if ( $this->min_len < $this->max_len ) {
			$strlen = $this->multibyte ? 'mb_strlen' : 'strlen';
			$len = $strlen( $var );
			if ( $this->min_len && $len < $this->min_len )
				return null;
			if ( $this->max_len && $len > $this->max_len )
				return null;
		}
		return $var;
	}

}
