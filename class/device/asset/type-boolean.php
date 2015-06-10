<?php
namespace WPDW\Device\Asset;

class type_boolean extends asset_simple {
	use asset_trait, Model\meta_post_meta;

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
	public function filter_singular( $value ) {
		return filter_var( $value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
	}

	public function print_column( $value, $post_id ) {
		$output = $this->display ?: $this->label;
		return $value ? $output : '';
	}

	/**
	 * Get DOM array to render form html element
	 *
	 * @access public
	 *
	 * @see    mimosafa\Decoder
	 *
	 * @todo   multiple value
	 *
	 * @param  mixed  $value
	 * @param  string $namespace
	 * @return array
	 */
	public function admin_form_element_dom_array( $value, $namespace = '' ) {
		$name = $namespace ? sprintf( '%s[%s]', $namespace, $this->name ) : $this->name;
		$label_text = $this->display ?: $this->description ?: $this->label;
		$input_attr = [
			'type' => 'checkbox',
			'name' => esc_attr( $name ),
			'value' => 1
		];
		if ( filter_var( $value, \FILTER_VALIDATE_BOOLEAN ) )
			$input_attr['checked'] = 'checked';

		$domArray = [
			'element' => 'label',
			'children' => [
				[ 'element' => 'input', 'attribute' => $input_attr ],
				[ 'element' => 'span', 'text' => esc_attr( $label_text ) ]
			],
			'attribute' => [ 'class' => 'wpdw-checkbox' ]
		];

		return $domArray;
	}

}
