<?php
namespace WPDW\Device\Admin;

/**
 * Class for printing admin form.
 * - Used @ WPDW\Device\Admin\meta_boxes class
 *
 * @uses mimosafa\Decoder
 * @uses WPDW\WP\nonce
 */
class template {
	use \mimosafa\Decoder;

	/**
	 * @var WPDW\WP\nonce
	 */
	private $nonce;

	/**
	 * Constructor
	 *
	 * @param  string $context
	 * @return (void)
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;

		/**
		 * Nonce gen
		 * - $domain must be the same as when saving
		 * @see WPDW\Device\Admin\save_post::__construct()
		 */
		$this->nonce = \WPDW\WP\nonce::getInstance( $domain );
	}

	/**
	 * @access public
	 *
	 * @uses   mimosafa\Decoder::getArrayToHtmlString()
	 *
	 * @param  array $args
	 * @return (void)
	 */
	public function output( Array $args ) {
		if ( $dom_array = $this->generate_dom_array( $args ) ) {
			echo html_entity_decode( $this->getArrayToHtmlString( $dom_array ) );
			//echo '<pre>'; var_dump( $dom_array ); echo '</pre>';
		}
	}

	/**
	 * Generate DOM array method
	 *
	 * @access private
	 *
	 * @param  array $args {
	 *     //
	 * }
	 * @return array
	 */
	private function generate_dom_array( Array $args ) {
		// Return array
		$return = [];

		// Static vars
		static $table = [];
		static $fieldset = [];
		static $non_separate_nonce = false;
		static $name = '';

		// Asset type
		if ( ! $type = $args['type'] )
			return;

		// DOM children
		$children = isset( $args['assets'] ) ? $args['assets'] : null;

		// Form style
		if ( ! $children )
			$style = 'inline';
		else if ( isset( $args['admin_form_style'] ) )
			$style = filter_var( $args['admin_form_style'] ); // inline|hide|block
		else
			$style = 'block';

		if ( ! in_array( $style, [ 'inline', 'block', 'hide' ], true ) )
			return;

		/**
		 * Generate DOM array
		 */
		if ( $style === 'inline' ) : // fieldset

			// Set DOM wrapper
			$fieldset = $fieldset ?: [ 'element' => 'fieldset', 'children' => [] ];
			$dom =& $fieldset['children'];

			if ( $children ) {

				//

			} else {

				$method = $type . '_dom_array';
				if ( ! method_exists( __CLASS__, $method ) )
					return [];

				$this->$method( $dom, $name, $args );
				if ( ! $non_separate_nonce )
					$this->nonce_dom_array( $dom, $args['name'] );

			}

			if ( $dom )
				$return[] = $fieldset;

			// Reset static vars
			$fieldset = [];
		
		else : // table

			$non_separate_nonce = $type === '_plural_assets' ? true : false;

			// Set DOM wrapper
			$table = $table ?: [
				'element' => 'table',
				'attribute' => [ 'class' => 'form-table' ],
				'children' => [ [ 'element' => 'tbody', 'children' => [] ] ]
			];
			$dom =& $table['children'][0]['children'];

			foreach ( $children as $child ) :

				if ( $childDom = $this->generate_dom_array( $child ) ) {
					$dom[] = [
						'element'  => 'tr',
						'children' => [
							[
								'element'  => 'th',
								'children' => [ [ 'element' => 'label', 'text' => esc_html( $child['label'] ) ] ]
							], [
								'element'  => 'td',
								'children' => $childDom
							]
						]
					];
				}

			endforeach;

			if ( $dom )
				$return[] = $table;

			// Reset static vars
			$table = [];
			$non_separate_nonce = false;

		endif;

		return $return;
	}

	private function inline_forms_dom_array( &$dom, $nameAttr, Array $args ) {
		extract( $args );
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;
		if ( $multiple )
			$nameAttr .= '[]';

		foreach ( $assets as $asset ) {
			if ( $childDom = $this->generate_dom_array( $asset ) )
				$dom[] = $childDom;
		}
	}

	/**
	 * Type: string - Generate DOM array
	 *
	 * @access private
	 *
	 * @param  array  &$dom
	 * @param  string $nameAttr
	 * @param  array  $args
	 * @return (void)
	 */
	private function string_dom_array( &$dom, $nameAttr, Array $args ) {
		if ( $args['paragraph'] ) {
			$this->textarea_dom_array( $dom, $nameAttr, $args );
			return;
		}

		extract( $args );
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;
		if ( $multiple )
			$nameAttr .= '[]';
		$value = $value ?: [ null ];

		foreach ( (array) $value as $val ) {
			$attr = [
				'type' => 'text',
				'name' => esc_attr( $nameAttr ),
				'value' => esc_attr( $val )
			];
			if ( $max )
				$attr['maxlength'] = esc_attr( $args['max'] );
			if ( $args['readonly'] )
				$attr['readonly'] = 'readonly';

			$dom[] = [ 'element' => 'input', 'attribute' => $attr ];
		}
	}

	private function textarea_dom_array( $nameAttr, Array $args ) {}

	/**
	 * Type: integer - Generate DOM array
	 *
	 * @access private
	 *
	 * @param  array  &$dom
	 * @param  string $nameAttr
	 * @param  array  $args
	 * @return (void)
	 */
	private function integer_dom_array( &$dom, $nameAttr, Array $args ) {
		extract( $args );
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;
		if ( $multiple )
			$nameAttr .= '[]';
		$value = $value ?: [ null ];

		foreach ( (array) $value as $val ) {
			$attr = [
				'type' => 'number',
				'name' => esc_attr( $nameAttr ),
				'value' => esc_attr( $val )
			];
			if ( $min )
				$attr['min'] = esc_attr( $args['min'] );
			if ( $max )
				$attr['max'] = esc_attr( $args['max'] );
			if ( $args['readonly'] )
				$attr['readonly'] = 'readonly';

			$dom[] = [ 'element' => 'input', 'attribute' => $attr ];
		}
	}

	/**
	 * Type: datetime - Generate DOM array
	 *
	 * @access private
	 *
	 * @param  array  &$dom
	 * @param  string $nameAttr
	 * @param  array  $args
	 * @return (void)
	 */
	private function datetime_dom_array( &$dom, $nameAttr, Array $args ) {
		extract( $args );
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;
		if ( $multiple )
			$nameAttr .= '[]';
		$value = $value ?: [ null ];

		foreach ( (array) $value as $val ) {
			$attr = [
				'type' => esc_attr( $input_type ),
				'name' => esc_attr( $nameAttr ),
				'value' => esc_attr( $val )
			];
			if ( $step )
				$attr['step'] = esc_attr( $args['step'] );
			if ( $args['readonly'] )
				$attr['readonly'] = 'readonly';

			$dom[] = [ 'element' => 'input', 'attribute' => $attr ];
		}
	}

	private function boolean_dom_array( &$dom, $nameAttr, Array $args ) {
		extract( $args );
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;

		$attr = [
			'type' => 'checkbox',
			'name' => esc_attr( $nameAttr ),
			'value' => 1
		];
		if ( $value )
			$attr['checked'] = 'checked';

		$dom[] = [
			'element' => 'label',
			'children' => [
				[ 'element' => 'input', 'attribute' => $attr ],
				[ 'element' => 'span', 'text' => esc_attr( $display ?: $description ?: $label ) ]
			],
		];
	}

	/**
	 * Generate nonce dom array
	 *
	 * @see  https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/functions.php#L1366
	 * @see  https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/functions.php#L1390
	 * 
	 * @param  array  &$dom
	 * @param  string $field
	 * @return (void)
	 */
	private function nonce_dom_array( &$dom, $field ) {
		$name = esc_attr( $this->nonce->get_nonce( $field ) );
		$nonce = [
			'element' => 'input',
			'attribute' => [
				'type' => 'hidden',
				'id' => $name,
				'name' => $name,
				'value' => esc_attr( $this->nonce->create_nonce( $field ) )
			]
		];
		$refer = [
			'element' => 'input',
			'attribute' => [
				'type' => 'hidden',
				'name' => '_wp_http_referer',
				'value' => esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			]
		];
		$dom[] = $nonce;
		$dom[] = $refer;
	}

}
