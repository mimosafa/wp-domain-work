<?php
namespace WPDW\Device\Asset;

class type_integer extends asset_abstract {
	use asset_vars, asset_models;

	/**
	 * @var int|null
	 */
	protected $min = null;
	protected $max = null;

	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( is_int( $this->min ) && is_int( $this->max) && $this->min > $this->max )
			$this->min = $this->max = null;
	}

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'min' && isset( $arg ) ) :
			$arg = self::validate_integer( $arg );
		elseif ( $key === 'max' && isset( $arg ) ) :
			$arg = self::validate_integer( $arg );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	protected function output_filter( $value ) {
		$options = [ 'default' => null ];
		if ( $this->min !== null )
			$options['min_range'] = $this->min;
		if ( $this->max !== null )
			$options['max_range'] = $this->max;
		if ( count( $options ) === 3 && $this->min > $this->max ) {
			unset( $options['min_range'] );
			unset( $options['max_range'] );
		}
		return filter_var( $value, \FILTER_VALIDATE_INT, [ 'options' => $options ] );
	}

	protected function input_filter( $value, \WP_Post $post ) {
		// @todo
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
