<?php
namespace WPDW\Device\Asset;

class type_string extends asset_simple {
	use asset_vars;

	/**
	 * @var boolean
	 */
	protected $multibyte;
	protected $paragraph = false; // @todo

	/**
	 * @var boolean|array
	 */
	protected $trim = true; // @todo

	/**
	 * @var int
	 */
	protected $min;
	protected $max;

	/**
	 * @var string Regexp
	 */
	protected $regexp = '';

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::__construct()
	 *
	 * @param  array $args
	 * @return (void)
	 */
	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( $this->min > $this->max )
			$this->min = $this->max = 0;
	}

	/**
	 * @access protected
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::arguments_walker()
	 *
	 * @param  mixed &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( in_array( $key, [ 'multibyte', 'paragraph' ], true ) ) :
			/**
			 * @var boolean $multibyte|$paragraph
			 */
			$arg = filter_var( $arg, \FILTER_VALIDATE_BOOLEAN );
		elseif ( in_array( $key, [ 'min', 'max' ], true ) ) :
			/**
			 * @var int $min|$max
			 */
			$options = [
				'options' => [
					'default' => 0,
					'min_range' => 1
				]
			];
			$arg = filter_var( $arg, \FILTER_VALIDATE_INT, $options );
		elseif ( $key === 'regexp' && $arg ) :
			/**
			 * @var string $regexp Regexp
			 */
			$arg = @preg_match( $pattern, '' ) !== false ? $arg : '';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * @access public
	 *
	 * @param  mixed $value
	 * @return string|array|null
	 */
	public function filter( $value ) {
		static $_filter_multiple = false;
		if ( $this->multiple && is_array( $value ) ) {

			if ( $_filter_multiple )
				return null;
			$filter_multiple = true;
			$filtered = [];
			foreach ( $value as $val )
				$filtered[] = $this->filter( $val );
			return array_filter( $filtered );

		} else {

			/**
			 * Regexp
			 */
			if ( $this->regexp ) {
				if ( ! preg_match( $this->regexp, $value ) )
					return null;
			}
			/**
			 * Multi-Byte
			 */
			if ( ! $this->multibyte && strlen( $value ) !== mb_strlen( $value ) )
				return null;
			/**
			 * String length
			 */
			if ( $this->min || $this->max ) {
				$strlen = $this->multibyte ? 'mb_strlen' : 'strlen';
				$len = $strlen( $value );
				if ( $this->min && $len < $this->min )
					return null;
				if ( $this->max && $len > $this->max )
					return null;
			}
			/**
			 * Return validated value
			 */
			return $value;

		}
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
