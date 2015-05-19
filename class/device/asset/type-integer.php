<?php
namespace WPDW\Device\Asset;

class type_integer extends asset_simple {
	use asset_vars;

	/**
	 * @var int|null
	 */
	protected $min = null;
	protected $max = null;

	/**
	 * @var int
	 */
	protected $step; // @todo

	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( is_int( $this->min ) && is_int( $this->max) && $this->min > $this->max )
			$this->min = $this->max = null;
	}

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( in_array( $key, [ 'min', 'max' ], true ) ) :
			$arg = self::validate_integer( $arg );
		elseif ( $key === 'step' ) :
			// @todo
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	protected function filter_value( $value, $post = null ) {
		$options = [ 'default' => null ];
		if ( $this->min !== null )
			$options['min_range'] = $this->min;
		if ( $this->max !== null )
			$options['max_range'] = $this->max;
		return filter_var( $value, \FILTER_VALIDATE_INT, [ 'options' => $options ] );
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
