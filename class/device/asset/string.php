<?php
namespace WPDW\Device\Asset;

class string implements asset_interface {
	use asset_vars, asset_model;

	private $model = 'post_meta';
	private $multibyte = true;
	private $min_len = 0;
	private $max_len = 0;
	private $regexp = '';

	/**
	 * Constructor
	 *
	 * @param  array $args
	 */
	public function __construct( Array $args ) {
		foreach ( $args as $key => $val ) {
			if ( property_exists( __CLASS__, $key ) )
				$this->$key = $val;
		}
	}

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
			$arg = self::validate_integer( $arg, null, 0 );
		elseif ( $key === 'max_len' ) :
			$arg = self::validate_integer( $arg, null, 1 );
		elseif ( $key === 'regexp' && $arg ) :
			$arg = @preg_match( $pattern, '' ) !== false ? $arg : '';
		else :
			// Common
			self::common_arguments( $arg, $key, $asset );
		endif;
	}

	public function filter( $var ) {
		$func_prefix = $this->multibyte ? 'mb_' : '';
		if ( $this->regexp ) {
			$preg_match = $func_prefix . 'preg_match';
			if ( ! $preg_match( $this->regexp, $var ) )
				return null;
		}
		if ( $this->min_len && $this->max_len && $this->min_len > $this->max_len ) {
			$this->min_len = 0;
			$this->max_len = 0;
		}
		$strlen = $func_prefix . 'strlen';
		$len = $strlen( $var );
		if ( $this->min_len && $len < $this->min_len )
			return null;
		if ( $this->max_len && $len > $this->max_len )
			return null;
		return $var;
	}

}
