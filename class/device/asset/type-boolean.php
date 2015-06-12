<?php
namespace WPDW\Device\Asset;

class type_boolean extends asset_unit implements asset, writable {
	use asset_trait;

	/**
	 * @var string
	 */
	protected $display;

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
		if ( $this->multiple )
			$this->multiple = false;
	}

	/**
	 * Overwrite WPDW\Device\Admin\asset_unit::filter()
	 *
	 * @access public
	 *
	 * @param  mixed $value
	 * @return boolean|null
	 */
	public function filter( $value ) {
		return filter_var( $value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
	}

	/**
	 * Overwrite WPDW\Device\Admin\asset_unit::admin_form_element_dom_array()
	 *
	 * @access public
	 *
	 * @see    mimosafa\Decoder
	 *
	 * @param  mixed  $value
	 * @param  string $namespace
	 * @return array
	 */
	public function admin_form_element_dom_array( $value, $namespace = '' ) {
		$name = $namespace ? sprintf( '%s[%s]', $namespace, $this->name ) : $this->name;
		$input_attr = [
			'type' => 'checkbox',
			'name' => esc_attr( $name ),
			'value' => 1
		];
		if ( filter_var( $value, \FILTER_VALIDATE_BOOLEAN ) )
			$input_attr['checked'] = 'checked';

		$label_text = $this->display ?: $this->description ?: $this->label;
		$domArray = [
			'element' => 'label',
			'children' => [
				[ 'element' => 'input', 'attribute' => $input_attr ],
				[ 'element' => 'span', 'text' => esc_html( $label_text ) ]
			],
			'attribute' => [ 'class' => 'wpdw-checkbox' ]
		];

		return [ $domArray ];
	}

	public function print_column( $value, $post_id ) {
		$output = $this->display ?: $this->label;
		return $value ? $output : '';
	}

}
