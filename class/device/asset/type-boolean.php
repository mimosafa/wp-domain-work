<?php
namespace WPDW\Device\Asset;

class type_boolean extends asset_simple {
	use asset_vars, Model\meta_post_meta;

	/**
	 * @var string
	 */
	protected $display;

	public function __construct( Array $args ) {
		if ( $this->multiple )
			$this->multiple = false;
		parent::__construct( $args );
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
	public static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'display' ) :
			/**
			 * @var string $display
			 */
			$arg = filter_var( $arg, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * @access public
	 *
	 * @param  mixed $value
	 * @return boolean|null
	 */
	public function filter( $value ) {
		return filter_var( $value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
	}

	protected function filter_value( $value, $post = null ) {
		return filter_var( $value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
	}

	public function print_column( $value, $post_id ) {
		$output = $this->display ?: $this->label;
		return $value ? $output : '';
	}

}
