<?php
namespace WPDW\Device\Admin;

/**
 * Class for printing admin form.
 * - Used @ WPDW\Device\Admin\meta_boxes class
 *
 * @uses mimosafa\Decoder
 */
class template {
	use \mimosafa\Decoder;

	/**
	 * @var string
	 */
	private $form_id_prefix;

	/**
	 * @var WPDW\WP\nonce
	 */
	private $nonce;

	/**
	 * Constructor
	 *
	 * @uses   WPDW\Device\Admin\post::FORM_ID_PREFIX
	 * @uses   WPDW\WP\nonce
	 *
	 * @param  string $context
	 * @return (void)
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;

		$this->form_id_prefix = post::FORM_ID_PREFIX . $domain . '-';

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
		/*
		echo '<pre>'; var_dump( $args ); echo '<pre>';
		return;
		*/
		if ( $dom_array = $this->generate_dom_array( $args ) )
			echo html_entity_decode( $this->getArrayToHtmlString( $dom_array ) );
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
		static $fieldset = [];
		#static $table = [];
		static $non_separate_nonce_for_block = false;
		static $non_separate_nonce_for_inline = false;
		static $name = '';

		if ( isset( $args['assets'] ) ) {
			if ( isset( $args['admin_form_style'] ) )
				$form_style = filter_var( $args['admin_form_style'] );
			else
				$form_style = 'block';
		}

		/**
		 * Generate DOM array
		 */
		if ( ! isset( $form_style ) ) { // Single form (Has non assets)

			if ( $dom = $this->single_form_dom_array( $name, $args ) ) {
				if ( empty( $fieldset ) )
					$dom = [ [ 'element' => 'fieldset', 'children' => $dom ] ];
				$return = $dom;
				if ( ! $non_separate_nonce_for_block && ! $non_separate_nonce_for_inline )
					$this->nonce_dom_array( $return, $args['name'] );
			}

		} else if ( $form_style === 'inline' ) { // Inline forms (Has assets)

			$fieldset = [
				'element' => 'fieldset',
				'children' => [],
			];
			$dom =& $fieldset['children'];

			// Cache vars
			$name_cache = $name;

			$non_separate_nonce_for_inline = true;

			$this->form_inline_dom_array( $dom, $name, $args );
			if ( ! empty( $dom ) ) {
				if ( ! $non_separate_nonce_for_block )
					$this->nonce_dom_array( $dom, $args['name'] );
				$return[] = $fieldset;
			}

			// Reset vars
			$name = $name_cache;
			$non_separate_nonce_for_inline = false;
			$fieldset = [];

		} else { // Block forms (Has assets)

			$table = /*$table ?:*/ [
				'element' => 'table',
				'attribute' => [ 'class' => 'form-table' ],
				'children' => [
					[ 'element' => 'tbody', 'children' => [] ]
				]
			];
			$dom =& $table['children'][0]['children'];

			// Cache vars
			$name_cache = $name;

			$non_separate_nonce_for_block = $args['type'] !== '_plural_assets';

			$this->form_table_dom_array( $dom, $name, $args );
			if ( ! empty( $dom ) ) {
				$return[] = $table;
				if ( isset( $args['name'] ) )
					$this->nonce_dom_array( $return, $args['name'] );
			}

			// Reset vars
			$name = $name_cache;
			if ( $non_separate_nonce_for_block )
				$non_separate_nonce_for_block = ! $non_separate_nonce_for_block;

		}

		return $return;
	}

	/**
	 * Single form DOM array
	 *
	 * @access public
	 *
	 * @param  string $nameAttr
	 * @param  array  $args
	 * @return array
	 */
	public function single_form_dom_array( $nameAttr, Array $args ) {
		$method = $args['type'] . '_dom_array';
		if ( method_exists( __CLASS__, $method ) )
			return $this->$method( $nameAttr, $args );
	}

	/**
	 * @access private
	 *
	 * @param  array  &$dom
	 * @param  string &$nameAttr
	 * @param  array  $args
	 * @return (void)
	 */
	private function form_inline_dom_array( Array &$dom, &$nameAttr, Array $args ) {
		extract( $args );
		$nameAttr_cache = $nameAttr;
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;
		if ( $multiple )
			$nameAttr .= '[]';

		foreach ( $assets as $asset_args ) {
			if ( $asset_dom = $this->generate_dom_array( $asset_args ) )
				$dom[] = $asset_dom[0];
		}
	}

	/**
	 * @access private
	 *
	 * @param  array  &$dom
	 * @param  string &$nameAttr
	 * @param  array  $args
	 * @return (void)
	 */
	private function form_table_dom_array( Array &$dom, &$nameAttr, Array $args ) {
		extract( $args );
		if ( ! isset( $assets ) )
			return;
		if ( isset( $name ) )
			$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;

		//

		foreach ( $assets as $asset_args ) {
			if ( $asset_dom = $this->generate_dom_array( $asset_args ) ) {
				$id = $this->form_id_prefix;
				if ( in_array( $asset_args['type'], [ 'set', 'group' ], true ) )
					$id .= $asset_args['assets'][0]['name'];
				else
					$id .= $asset_args['name'];

				$dom[] = [
					'element'  => 'tr',
					'children' => [
						[
							'element'  => 'th',
							'children' => [
								[
									'element' => 'label',
									'attribute' => [ 'for' => esc_attr( $id ) ],
									'text' => esc_html( $asset_args['label'] )
								]
							]
						], [
							'element'  => 'td',
							'children' => $asset_dom
						]
					]
				];
			}
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
	private function string_dom_array( $nameAttr, Array $args ) {
		if ( $args['paragraph'] )
			return $this->textarea_dom_array( $nameAttr, $args );

		extract( $args );
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;
		if ( $multiple )
			$nameAttr .= '[]';
		$value = $value ?: [ null ];

		$dom = [];
		$n = 0;
		foreach ( (array) $value as $val ) {
			$id = $this->form_id_prefix;
			$id .= ! $n ? $name : $name . '-' . $n;
			$attr = [
				'type' => 'text',
				'name' => esc_attr( $nameAttr ),
				'value' => esc_attr( $val ),
				'id' => $id,
				'class' => 'regular-text'
			];
			if ( $max )
				$attr['maxlength'] = esc_attr( $args['max'] );
			if ( $args['readonly'] )
				$attr['readonly'] = 'readonly';

			$dom[] = [ 'element' => 'input', 'attribute' => $attr ];
			$n++;
		}
		return $dom;
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
	private function integer_dom_array( $nameAttr, Array $args ) {
		extract( $args );
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;
		if ( $multiple )
			$nameAttr .= '[]';
		$value = $value ?: [ null ];

		$dom = [];
		$n = 0;
		foreach ( (array) $value as $val ) {
			$id = $this->form_id_prefix;
			$id .= ! $n ? $name : $name . '-' . $n;
			$attr = [
				'type' => 'number',
				'name' => esc_attr( $nameAttr ),
				'value' => esc_attr( $val ),
				'id' => $id
			];
			if ( $min )
				$attr['min'] = esc_attr( $args['min'] );
			if ( $max )
				$attr['max'] = esc_attr( $args['max'] );
			if ( $step )
				$attr['step'] = esc_attr( $args['step'] );
			if ( $args['readonly'] )
				$attr['readonly'] = 'readonly';

			$dom[] = [ 'element' => 'input', 'attribute' => $attr ];
			$n++;
		}
		return $dom;
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
	private function datetime_dom_array( $nameAttr, Array $args ) {
		extract( $args );
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;
		if ( $multiple )
			$nameAttr .= '[]';
		$value = $value ?: [ null ];

		$dom = [];
		$n = 0;
		foreach ( (array) $value as $val ) {
			$id = $this->form_id_prefix;
			$id .= ! $n ? $name : $name . '-' . $n;
			$attr = [
				'type' => esc_attr( $unit ),
				'name' => esc_attr( $nameAttr ),
				'value' => esc_attr( $val ),
				'id' => $id
			];
			if ( $step )
				$attr['step'] = esc_attr( $args['step'] );
			if ( $args['readonly'] )
				$attr['readonly'] = 'readonly';

			$dom[] = [ 'element' => 'input', 'attribute' => $attr ];
			$n++;
		}
		return $dom;
	}

	private function boolean_dom_array( $nameAttr, Array $args ) {
		extract( $args );
		$nameAttr = $nameAttr ? $nameAttr . sprintf( '[%s]', $name ) : $name;
		$id = $this->form_id_prefix . $name;

		$attr = [
			'type' => 'checkbox',
			'name' => esc_attr( $nameAttr ),
			'value' => 1,
			'id' => $id
		];
		if ( $value )
			$attr['checked'] = 'checked';

		return [
			[
				'element' => 'label',
				'children' => [
					[ 'element' => 'input', 'attribute' => $attr ],
					[ 'element' => 'span', 'text' => esc_attr( $display ?: $description ?: $label ) ]
				],
				'attribute' => [ 'class' => 'wpdw-checkbox']
			]
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
