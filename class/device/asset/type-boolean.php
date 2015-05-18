<?php
namespace WPDW\Device\Asset;

class type_boolean extends asset_simple {
	use asset_vars;

	/**
	 * @var string
	 */
	protected $display;

	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( $this->multiple )
			$this->multiple = false;
	}

	public static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'display' ) :
			$arg = self::sanitize_string( $arg );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	protected function filter_callback( $value, $post = null ) {
		return filter_var( $value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
	}

	public function print_column( $value, $post_id ) {
		$output = $this->display ?: $this->label;
		return $value ? $output : '';
	}

}
