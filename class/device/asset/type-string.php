<?php
namespace WPDW\Device\Asset;

class type_string extends asset_unit implements asset, writable {
	use asset_trait;

	/**
	 * @var boolean
	 */
	protected $multibyte;
	protected $paragraph; // @todo
	protected $trim = true; // @todo

	/**
	 * @var int
	 */
	protected $min;
	protected $max;

	/**
	 * @var string Regexp
	 */
	protected $regexp;

	/**
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::arguments_walker()
	 *
	 * @param  mixed &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	public static function arguments_walker( &$arg, $key, $asset ) {
		if ( in_array( $key, [ 'multibyte', 'paragraph', 'trim' ], true ) ) :
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
			$arg = @preg_match( $pattern, '' ) !== false ? $arg : null;
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::__construct()
	 *
	 * @param  WPDW\Device\Asset\verified $args
	 * @return (void)
	 */
	public function __construct( verified $args ) {
		parent::__construct( $args );
		if ( $this->min > $this->max )
			$this->min = $this->max = 0;
	}

	/**
	 * @access public
	 *
	 * @param  mixed $value
	 * @return string|array|null
	 */
	public function filter_singular( $value ) {
		// Regexp
		if ( $this->regexp ) {
			if ( ! preg_match( $this->regexp, $value ) )
				return null;
		}

		// Multi-Byte
		if ( ! $this->multibyte && strlen( $value ) !== mb_strlen( $value ) )
			return null;

		// String length
		if ( $this->min || $this->max ) {
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
	 * @access public
	 *
	 * @param  string $name
	 * @param  string $value
	 */
	public function single_admin_form_element_dom_array( $name, $value ) {
		$name  = filter_var( $name );
		$value = filter_var( $value );

		if ( $this->paragraph ) {
			return [
				'element' => 'textarea',
				'attribute' => [
					'name' => esc_attr( $name ),
					'class' => 'large-text'
				],
				'text' => esc_html( $value ),
			];
		} else {
			return [
				'element' => 'input',
				'attribute' => [
					'type' => 'text',
					'name' => esc_attr( $name ),
					'value' => esc_attr( $value ),
					'class' => 'regular-text'
				]
			];
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
