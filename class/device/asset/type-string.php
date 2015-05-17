<?php
namespace WPDW\Device\Asset;

class type_string extends asset_abstract implements asset {
	use asset_vars, asset_models;

	/**
	 * @var boolean
	 */
	protected $multibyte = true;

	/**
	 * @var int
	 */
	protected $min = 0;
	protected $max = 0;

	/**
	 * @var string Regexp
	 */
	protected $regexp = '';

	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( $this->min > $this->max )
			$this->min = $this->max = 0;
	}

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'multibyte' ) :
			$arg = self::validate_boolean( $arg, true );
		elseif ( in_array( $key, [ 'min', 'max' ], true ) ) :
			$arg = self::validate_integer( $arg, 0, 1 );
		elseif ( $key === 'regexp' && $arg ) :
			$arg = @preg_match( $pattern, '' ) !== false ? $arg : '';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	protected function filter( $value, $post = null ) {
		if ( $this->regexp ) {
			if ( ! preg_match( $this->regexp, $value ) )
				return null;
		}
		if ( $this->min < $this->max ) {
			$strlen = $this->multibyte ? 'mb_strlen' : 'strlen';
			$len = $strlen( $value );
			if ( $this->min && $len < $this->min )
				return null;
			if ( $this->max && $len > $this->max )
				return null;
		}
		return $value;
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
